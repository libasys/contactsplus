<?php
namespace OCA\ContactsPlus;


class AddressbookProvider implements \OCP\IAddressBook {
	/**
	 * Addressbook info array
	 * @var AddressBook
	 */
	public $addressBook;

	/**
	 * Constructor
	 * @param AddressBook $addressBook
	 */
	public function __construct($addressBook) {
		$this->addressBook = $addressBook;
	
	}
	
	public function getAddressbook() {
		return $this->addressBook;
	}
	
	/**
	* @return string defining the technical unique key
	*/
	public function getKey() {
		return $this->addressBook['id'];
	}

	/**
	* In comparison to getKey() this function returns a human readable (maybe translated) name
	* @return string
	*/
	public function getDisplayName() {
		return $this->addressBook['displayname'];
	}
	
	/**
	* @return integer
	*/
	public function getPermissions() {
		return $this->addressBook['permissions'];
	}
	
	/**
	* @param string $pattern
	* @param string[] $searchProperties
	* @param $options
	* @return array|false
	*/
	public function search($pattern, $searchProperties, $options) {
		$propTable = '*PREFIX*conplus_cards_properties';
		$contTable = '*PREFIX*conplus_cards';
		$addrTable = '*PREFIX*conplus_addressbooks';
		$results = array();

		/**
		 * This query will fetch all contacts which match the $searchProperties
		 * It will look up the addressbookid of the contact and the user id of the owner of the contact app
		 */
		$query = <<<SQL
			SELECT
				DISTINCT
				`$propTable`.`contactid`,
				`$contTable`.`addressbookid`,
				`$addrTable`.`userid`

			FROM
				`$propTable`
			INNER JOIN
				`$contTable`
			ON `$contTable`.`id` = `$propTable`.`contactid`
  				INNER JOIN `$addrTable`
			ON `$addrTable`.id = `$contTable`.addressbookid
			WHERE
				(`$contTable`.addressbookid = ?) AND
				(
SQL;

		$params = array();
		$params[] = $this->getKey();		
		
		foreach ($searchProperties as $property) {
			$params[] = $property;
			$params[] = '%' . $pattern . '%';
			$query .= '(`name` = ? AND `value` ILIKE ?) OR ';
		}
		
		$query = substr($query, 0, strlen($query) - 4);
		$query .= ')';

		$stmt = \OCP\DB::prepare($query);
		$result = $stmt->execute($params);
		
		if (\OCP\DB::isError($result)) {
			\OCP\Util::writeLog('contactsplus', __METHOD__ . 'DB error: ' . \OC_DB::getErrorMessage($result),
				\OCP\Util::ERROR);
			return false;
		}
		
		$j = [];
		
		while ($row = $result->fetchRow()) {
			$id = $row['contactid'];
			//$addressbookKey = $row['addressbookid'];
			$vcard = App::getContactVCard($id);
			
			$contact = VCard::structureContact($vcard);
			
			$j['data'] = $contact;
			$j['data']['id'] = $id;
			$j['data']['metadata'] = $row;
			$j['data']['photo'] = false;
			if(isset($vcard->BDAY)){
				$j['data']['birthday'] = $vcard->BDAY;
			} 
			if(isset($vcard->PHOTO) || isset($vcard->LOGO)) {
				$j['data']['photo'] = true;
				$url = \OC::$server->getURLGenerator()->linkToRoute('contactsplus.contacts.getContactPhoto',array('id' => $id));
				$url = \OC::$server->getURLGenerator()->getAbsoluteURL($url);
				$j['data']['PHOTO'] = "uri:$url";
			}
			
			$results[] = $this->convertToSearchResult($j);
			
		}
		return $results;
		
	}
	
	/**
	* @param $properties
	* @return Contact|null
	*/
	public function createOrUpdate($properties) {
		
	}
	
	/**
	* @param $id
	* @return mixed
	*/
	public function delete($id) {
		
		VCard::delete($id);
	}
	
	
	
	/**
	 * @param $j
	 * @return array
	 */
	private function convertToSearchResult($j) {
		$data = $j['data'];
		$result = array();
		foreach( $data as $key => $d) {
			$d = $data[$key];
			if (in_array($key, App::$multi_properties)) {
				$result[$key] = array_map(function($v){
					return $v['value'];
				}, $d);
			} else {
				if (is_array($d)) {
					$result[$key] = $d[0]['value'];
				} else {
					$result[$key] = $d;
				}
			}
		}

		return $result;
	}
}