<?php
/**
 * ownCloud - Addressbook
 *
 * @author Jakob Sack
 * @copyright 2011 Jakob Sack mail@jakobsack.de
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * This CardDAV backend uses PDO to store addressbooks
 */
 namespace OCA\ContactsPlus\Connector\Sabre\Carddav;
 
use \OCA\ContactsPlus\Addressbook as AddrBook;
use \OCA\ContactsPlus\VCard;
 
class Backend extends \Sabre\CardDAV\Backend\AbstractBackend {
	/**
	 * Returns the list of addressbooks for a specific user.
	 *
	 * @param string $principaluri
	 * @return array
	 */
	public function getAddressBooksForUser($principaluri) {
		$data = AddrBook::allWherePrincipalURIIs($principaluri);
		$addressbooks = array();

		foreach($data as $i) {
			if($i['userid'] !== \OCP\USER::getUser()) {
				$i['uri'] = $i['uri'] . '_shared_by_' . $i['userid'];
			}
			$addressbooks[] = array(
				'id'  => $i['id'],
				'uri' => $i['uri'],
				'principaluri' => 'principals/'.$i['userid'],
				'{DAV:}displayname' => $i['displayname'],
				'{' . \Sabre\CardDAV\Plugin::NS_CARDDAV . '}addressbook-description' => $i['description'],
				'{http://calendarserver.org/ns/}getctag' => $i['ctag'],
				'{http://sabredav.org/ns}sync-token' => $i['ctag']?$i['ctag']:'0',
			);
			
			//\OCP\Util::writeLog('kontakte','CARDDAV->:'.$i['displayname'], \OCP\Util::DEBUG);
		}
		
		
		return $addressbooks;
	}


	/**
	 * Updates an addressbook's properties
	 *
	 * See Sabre_DAV_IProperties for a description of the mutations array, as
	 * well as the return value.
	 *
	 * @param mixed $addressbookid
	 * @param array $mutations
	 * @see Sabre_DAV_IProperties::updateProperties
	 * @return bool|array
	 */
	public function updateAddressBook($addressbookid, \Sabre\DAV\PropPatch $mutations) {
			
		$supportedProperties = [
            '{DAV:}displayname',
            '{' . \Sabre\CardDAV\Plugin::NS_CARDDAV . '}addressbook-description',
        ];
		
		$propPatch->handle($supportedProperties, function($mutations) use ($addressbookid) {

            $updates = [];
            foreach($mutations as $property=>$newValue) {

                switch($property) {
                    case '{DAV:}displayname' :
                        $updates['displayname'] = $newValue;
                        break;
                    case '{' . \Sabre\CardDAV\Plugin::NS_CARDDAV . '}addressbook-description' :
                        $updates['description'] = $newValue;
                        break;
                }
            }
		
			AddrBook::edit($addressbookid, $updates['displayname'], $updates['description']);
			return true;
		 });
	
	}

	/**
	 * Creates a new address book
	 *
	 * @param string $principaluri
	 * @param string $url Just the 'basename' of the url.
	 * @param array $properties
	 * @return void
	 */
	public function createAddressBook($principaluri, $url, array $properties) {

		$displayname = null;
		$description = null;

		foreach($properties as $property => $newvalue) {

			switch($property) {
				case '{DAV:}displayname' :
					$name = $newvalue;
					break;
				case '{' . \Sabre\CardDAV\Plugin::NS_CARDDAV
						. '}addressbook-description' :
					$description = $newvalue;
					break;
				default :
					throw new \Sabre\DAV\Exception\BadRequest('Unknown property: '
						. $property);
			}

		}

		AddrBook::addFromDAVData(
					$principaluri,
					$url,
					$name,
					$description
		);
	}

	/**
	 * Deletes an entire addressbook and all its contents
	 *
	 * @param int $addressbookid
	 * @return void
	 */
	public function deleteAddressBook($addressbookid) {
		AddrBook::delete($addressbookid);
	}

	/**
	 * Returns all cards for a specific addressbook id.
	 *
	 * @param mixed $addressbookid
	 * @return array
	 */
	public function getCards($addressbookid) {
		$data = VCard::all($addressbookid);
		$cards = array();
		foreach($data as $i) {
			//OCP\Util::writeLog('contacts', __METHOD__.', uri: ' . $i['uri'], OCP\Util::DEBUG);
			$cards[] = array(
				'id' => $i['id'],
				//'carddata' => $i['carddata'],
				'size' => strlen($i['carddata']),
				'etag' => '"' . md5($i['carddata']) . '"',
				'uri' => $i['uri'],
				'lastmodified' => $i['lastmodified'] );
		}

		return $cards;
	}

	/**
	 * Returns a specfic card
	 *
	 * @param mixed $addressbookid
	 * @param string $carduri
	 * @return array
	 */
	public function getCard($addressbookid, $carduri) {
		return VCard::findWhereDAVDataIs($addressbookid, $carduri);

	}

	/**
	 * Creates a new card
	 *
	 * @param mixed $addressbookid
	 * @param string $carduri
	 * @param string $carddata
	 * @return bool
	 */
	public function createCard($addressbookid, $carduri, $carddata) {
		VCard::addFromDAVData($addressbookid, $carduri, $carddata);
	}

	/**
	 * Updates a card
	 *
	 * @param mixed $addressbookid
	 * @param string $carduri
	 * @param string $carddata
	 * @return bool
	 */
	public function updateCard($addressbookid, $carduri, $carddata) {
		return VCard::editFromDAVData($addressbookid, $carduri, $carddata);
	}

	/**
	 * Deletes a card
	 *
	 * @param mixed $addressbookid
	 * @param string $carduri
	 * @return bool
	 */
	public function deleteCard($addressbookid, $carduri) {
		return VCard::deleteFromDAVData($addressbookid, $carduri);
	}
	
	
	/**
	 * @brief gets the userid from a principal path
	 * @param string $principaluri
	 * @return string
	 */
	public function userIDByPrincipal($principaluri) {
		list(, $userid) = \Sabre\DAV\URLUtil::splitPath($principaluri);
		return $userid;
	}
	
	
	
}
