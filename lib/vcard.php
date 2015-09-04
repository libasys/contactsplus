<?php
/**
 * ownCloud - Addressbook
 *
 * @author Jakob Sack
 * @copyright 2011 Jakob Sack mail@jakobsack.de
 * @copyright 2012 Thomas Tanghus <thomas@tanghus.net>
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
/*
 *
 * The following SQL statement is just a help for developers and will not be
 * executed!
 *
 * CREATE TABLE contacts_cards (
 * id INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
 * addressbookid INT(11) UNSIGNED NOT NULL,
 * fullname VARCHAR(255),
 * carddata TEXT,
 * uri VARCHAR(100),
 * lastmodified INT(11) UNSIGNED
 * );
 */

namespace OCA\ContactsPlus;

use Sabre\VObject;

/**
 * This class manages our vCards
 */
class VCard {
	/**
	 * @brief Returns all cards of an address book
	 * @param integer $id
	 * @param integer $offset
	 * @param integer $limit
	 * @param array $fields An array of the fields to return. Defaults to all.
	 * @return array|false
	 *
	 * The cards are associative arrays. You'll find the original vCard in
	 * ['carddata']
	 */
	public static function all($id, $offset=null, $limit=null, $fields = array(),$bOnlyVCard=false) {
		$result = null;

		$qfields = count($fields) > 0
			? '`' . implode('`,`', $fields) . '`'
			: '*';
		
		$addWhere='';
		if($bOnlyVCard){
			$addWhere="AND `component` = 'VCARD' ";
		}
			
		if(is_array($id) && count($id)) {
			$id_sql = join(',', array_fill(0, count($id), '?'));
			$sql = "SELECT * FROM `".App::ContactsTable."` WHERE `addressbookid` IN (".$id_sql.") ".$addWhere." ORDER BY LOWER(`fullname`) ";
			try {
				$stmt = \OCP\DB::prepare($sql, $limit, $offset);
				$result = $stmt->execute($id);
				if (\OCP\DB::isError($result)) {
					\OCP\Util::writeLog(App::$appname, __METHOD__. 'DB error: ' . \OCP\DB::getErrorMessage($result), \OCP\Util::ERROR);
					return false;
				}
			} catch(\Exception $e) {
				\OCP\Util::writeLog(App::$appname, __METHOD__.', exception: ' . $e->getMessage(), \OCP\Util::ERROR);
				return false;
			}
		} elseif(is_int($id) || is_string($id)) {
			try {
				$sql = "SELECT * FROM `".App::ContactsTable."` WHERE `addressbookid` = ? ".$addWhere."  ORDER BY LOWER(`fullname`) ";
				$stmt = \OCP\DB::prepare($sql, $limit, $offset);
				$result = $stmt->execute(array($id));
				if (\OCP\DB::isError($result)) {
					\OCP\Util::writeLog(App::$appname, __METHOD__. 'DB error: ' . \OCP\DB::getErrorMessage($result), \OCP\Util::ERROR);
					return false;
				}
			} catch(\Exception $e) {
				\OCP\Util::writeLog(App::$appname, __METHOD__.', exception: '.$e->getMessage(), \OCP\Util::ERROR);
				\OCP\Util::writeLog(App::$appname, __METHOD__.', ids: '. $id, \OCP\Util::DEBUG);
				return false;
			}
		} else {
			\OCP\Util::writeLog(App::$appname, __METHOD__. '. Addressbook id(s) argument is empty: '.print_r($id, true), \OCP\Util::DEBUG);
			return false;
		}
		$cards = array();
		if(!is_null($result)) {
			while( $row = $result->fetchRow()) {
				
				if($row['bcompany']){
					$row['sortFullname'] = mb_substr($row['fullname'],0,3,"UTF-8");
				}else{
				  	if($row['lastname'] !== ''){	
				  		$row['sortFullname'] = mb_substr($row['lastname'],0,3,"UTF-8");
					}else{
						$row['sortFullname'] = mb_substr($row['surename'],0,3,"UTF-8");
					}
				}
				
				$cards[] = $row;
				
			}
		}
		usort($cards, array('\OCA\ContactsPlus\Vcard', 'compareContactsFullname'));
		return $cards;
	}
	
	public static function compareContactsLastname($a, $b) {
			return \OCP\Util::naturalSortCompare($a['sortLastname'], $b['sortLastname']);
	}
	
	public static function compareContactsFullname($a, $b) {
			return \OCP\Util::naturalSortCompare($a['sortFullname'], $b['sortFullname']);
	}

  public static function allByFavourite(){
  	   $favorites = \OC::$server -> getTagManager() -> load(App::$appname)->getFavorites();	
  	  
		if(count($favorites)>0){
			$id_sql='';
			foreach($favorites as $fav){
				if($id_sql==''){
					$id_sql=$fav;
				}else{
					$id_sql.=','.$fav;
				}
			}
			
			
			$sql = "SELECT * FROM `".App::ContactsTable."` WHERE `id` IN (".$id_sql.") AND `component` = 'VCARD' ORDER BY LOWER(`fullname`) ASC";
			$stmt = \OCP\DB::prepare($sql);
			$result = $stmt->execute();
			$cards = array();
			if(!is_null($result)) {
			while( $row = $result->fetchRow()) {
				
				$cards[] = $row;
				
			}
		}

		return $cards;
			
		}
  }
  
  public static function getCardsByGroups($id, $grpid,$offset=null, $limit=null,$bOnlyVCard=false) {
  	  
	  if($grpid==='none'){
	  	$SQLStatement="WHERE  `c`.`bcategory`='0' ";
	  }	else{
	  	 $SQLStatement="
	  	 LEFT JOIN `".App::ContactsProbTable."` cp ON  `c`.`id`=`cp`.`contactid`
	  	 WHERE `c`.`bcategory`='1' AND `cp`.`name`='CATEGORIES' AND `cp`.`value` LIKE '%".$grpid."%'
	  	 
	  	 ";
	  }
		$addWhere='';
		if($bOnlyVCard){
			$addWhere="AND `c`.`component` = 'VCARD' ";
		}
		
  	  if(is_array($id) && count($id)) {
			$id_sql = join(',', array_fill(0, count($id), '?'));
		  //SELECT * FROM `oc_contacts_cards` c LEFT JOIN `oc_contacts_cards_properties` cp ON `c`.`id` = `cp`.`contactid` WHERE `c`.`addressbookid`='1' AND `cp`.`name`='CATEGORIES' AND `cp`.`value` LIKE '%Kunden%' ORDER BY `c`.`fullname`
			$sql = "SELECT `c`.`id`,`c`.`fullname`,`c`.`surename`,`c`.`lastname`,`c`.`carddata`,`c`.`addressbookid`,`c`.`uri`,`c`.`lastmodified`,`c`.`component`  FROM `".App::ContactsTable."` c  
			            ".$SQLStatement." AND `c`.`addressbookid` IN (".$id_sql.") ".$addWhere."
			           ORDER BY LOWER(`c`.`fullname`) ASC";
			 $stmt = \OCP\DB::prepare($sql, $limit, $offset);
			 $result = $stmt->execute($id);
		} elseif(is_int($id) || is_string($id)) {
			$sql = "SELECT `c`.`id`,`c`.`fullname`,`c`.`surename`,`c`.`lastname`,`c`.`carddata`,`c`.`addressbookid`,`c`.`uri`,`c`.`lastmodified`,`c`.`component`  FROM `".App::ContactsTable."` c  
			            ".$SQLStatement." AND `c`.`addressbookid`= ? ".$addWhere."
			           ORDER BY LOWER(`c`.`fullname`) ASC";
			 $stmt = \OCP\DB::prepare($sql, $limit, $offset);
			 $result = $stmt->execute(array($id));
		}	
			 $cards = array();
				if(!is_null($result)) {
					while( $row = $result->fetchRow()) {
						
						$cards[] = $row;
					}
				}

		return $cards;	   
			//\OCP\Util::writeLog(App::$appname,', SQL: '. $sql, \OCP\Util::DEBUG);		   
	  
  }

	/**
	 * @brief Returns a card
	 * @param integer $id
	 * @param array $fields An array of the fields to return. Defaults to all.
	 * @return associative array or false.
	 */
	public static function find($id, $fields = array() ) {
		if(count($fields) > 0 && !in_array('addressbookid', $fields)) {
			$fields[] = 'addressbookid';
		}
		try {
			$qfields = count($fields) > 0
				? '`' . implode('`,`', $fields) . '`'
				: '*';
			$stmt = \OCP\DB::prepare( 'SELECT ' . $qfields . ' FROM `'.App::ContactsTable.'` WHERE `id` = ?' );
			$result = $stmt->execute(array($id));
			if (\OCP\DB::isError($result)) {
				\OCP\Util::writeLog(App::$appname, __METHOD__. 'DB error: ' . \OCP\DB::getErrorMessage($result), \OCP\Util::ERROR);
				return false;
			}
		} catch(\Exception $e) {
			\OCP\Util::writeLog(App::$appname, __METHOD__.', exception: '.$e->getMessage(), \OCP\Util::ERROR);
			\OCP\Util::writeLog(App::$appname, __METHOD__.', id: '. $id, \OCP\Util::DEBUG);
			return false;
		}

		$row = $result->fetchRow();
		if($row) {
			try {
				$addressbook = Addressbook::find($row['addressbookid']);
			} catch(\Exception $e) {
				\OCP\Util::writeLog(App::$appname, __METHOD__.', exception: '.$e->getMessage(), \OCP\Util::ERROR);
				\OCP\Util::writeLog(App::$appname, __METHOD__.', id: '. $id, \OCP\Util::DEBUG);
				throw $e;
			}
		}
		return $row;
	}

	/**
	 * @brief finds a card by its DAV Data
	 * @param integer $aid Addressbook id
	 * @param string $uri the uri ('filename')
	 * @return associative array or false.
	 */
	public static function findWhereDAVDataIs($aid, $uri) {
		try {
			$stmt = \OCP\DB::prepare( 'SELECT * FROM `'.App::ContactsTable.'` WHERE `addressbookid` = ? AND `uri` = ?' );
			$result = $stmt->execute(array($aid,$uri));
			if (\OCP\DB::isError($result)) {
				\OCP\Util::writeLog(App::$appname, __METHOD__. 'DB error: ' . \OCP\DB::getErrorMessage($result), \OCP\Util::ERROR);
				return false;
			}
		} catch(\Exception $e) {
			\OCP\Util::writeLog(App::$appname, __METHOD__.', exception: '.$e->getMessage(), \OCP\Util::ERROR);
			\OCP\Util::writeLog(App::$appname, __METHOD__.', aid: '.$aid.' uri'.$uri, \OCP\Util::DEBUG);
			return false;
		}

		return $result->fetchRow();
	}

	/**
	* VCards with version 2.1, 3.0 and 4.0 are found.
	*
	* If the VCARD doesn't know its version, 3.0 is assumed and if
	* option UPGRADE is given it will be upgraded to version 3.0.
	*/
	const DEFAULT_VERSION = '3.0';

	/**
	* The vCard 2.1 specification allows parameter values without a name.
	* The parameter name is then determined from the unique parameter value.
	* In version 2.1 e.g. a phone can be formatted like: TEL;HOME;CELL:123456789
	* This has to be changed to either TEL;TYPE=HOME,CELL:123456789 or TEL;TYPE=HOME;TYPE=CELL:123456789 - both are valid.
	*
	* From: https://github.com/barnabywalters/vcard/blob/master/barnabywalters/VCard/VCard.php
	*
	* @param string value
	* @return string
	*/
	public static function paramName($value) {
		static $types = array (
				'DOM', 'INTL', 'POSTAL', 'PARCEL','HOME', 'WORK',
				'PREF', 'VOICE', 'FAX', 'MSG', 'CELL', 'PAGER',
				'BBS', 'MODEM', 'CAR', 'ISDN', 'VIDEO',
				'AOL', 'APPLELINK', 'ATTMAIL', 'CIS', 'EWORLD',
				'INTERNET', 'IBMMAIL', 'MCIMAIL',
				'POWERSHARE', 'PRODIGY', 'TLX', 'X400',
				'GIF', 'CGM', 'WMF', 'BMP', 'MET', 'PMB', 'DIB',
				'PICT', 'TIFF', 'PDF', 'PS', 'JPEG', 'QTIME',
				'MPEG', 'MPEG2', 'AVI',
				'WAVE', 'AIFF', 'PCM',
				'X509', 'PGP');
		static $values = array (
				'INLINE', 'URL', 'CID');
		static $encodings = array (
				'7BIT', 'QUOTED-PRINTABLE', 'BASE64');
		$name = 'UNKNOWN';
		if (in_array($value, $types)) {
			$name = 'TYPE';
		} elseif (in_array($value, $values)) {
			$name = 'VALUE';
		} elseif (in_array($value, $encodings)) {
			$name = 'ENCODING';
		}
		return $name;
	}

	/**
	* @brief Decode properties for upgrading from v. 2.1
	* @param $property Reference to a Sabre_VObject_Property.
	* The only encoding allowed in version 3.0 is 'b' for binary. All encoded strings
	* must therefor be decoded and the parameters removed.
	*/
	public static function decodeProperty(&$property) {
		// Check out for encoded string and decode them :-[
		foreach($property->parameters as $key=>&$parameter) {
			if(trim($parameter->getValue()) === '') {
				$parameter->setValue($parameter->name);
				$parameter->name = self::paramName($parameter->name);
			}
			if(strtoupper($parameter->name) == 'ENCODING') {
				if(strtoupper($parameter->getValue()) == 'QUOTED-PRINTABLE') { // what kind of other encodings could be used?
					// Decode quoted-printable and strip any control chars
					// except \n and \r
					$property->setValue(preg_replace(
								'/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/',
								'',
								quoted_printable_decode($property->getValue())
					));
					unset($property->parameters[$key]);
				}
			} elseif(strtoupper($parameter->name) == 'CHARSET') {
					unset($property->parameters[$key]);
			}
		}
	}

	/**
	* Work around issue in older VObject sersions
	* https://github.com/fruux/sabre-vobject/issues/24
	* @param $vcard Reference to a Sabre_VObject_Property.
	*/
	public static function fixPropertyParameters(&$vcard) {
		// Work around issue in older VObject sersions
		// https://github.com/fruux/sabre-vobject/issues/24
		foreach($vcard->children as $property) {
			foreach($property->parameters as $key=>$parameter) {
				$delim = '';
				if(strpos($parameter->getValue(), ',') !== false) {
					$delim = ',';
				} elseif(strpos($parameter->getValue(), '\\,') !== false) {
					$delim = '\\,';
				} else {
					continue;
				}
				$values = explode($delim, $parameter->getValue());
				$parameter->setValue(array_shift($values));
				foreach($values as $value) {
					$property->add($parameter->name, $value);
				}
			}
		}
	}

   public static function updateCardByUid($uid,$mode,$category) {
		$stmt = \OCP\DB::prepare( 'SELECT * FROM `'.App::ContactsTable.'` WHERE  `uri` = ?' );
		$uri = $uid.'.vcf';
		$result = $stmt->execute(array($uri));
		if(!is_null($result)) {
			  $row = $result->fetchRow(); 
		//	\OCP\Util::writeLog(App::$appname,'IOS CARD ' . $row['carddata'].$uri, \OCP\Util::DEBUG);
			
			
			 try {
					 $vcard = VObject\Reader::read($row['carddata']);
				} catch(\Exception $e) {
					\OCP\Util::writeLog(App::$appname, __METHOD__.
						', Unable to parse VCARD, : ' . $e->getMessage(), \OCP\Util::ERROR);
					return false;
				}
				
			 $property = $vcard->select('CATEGORIES');
			 if($mode=='add'){
				 if(count($property) === 0) {
					//Neu
					$vcard->add('CATEGORIES',$category);
					$iNumber=1;
				} else {
					$property = array_shift($property);
					$oldValue=stripslashes($property->getValue());
					
					if(!stristr($oldValue,$category)){
						$newValue=(string) $oldValue.','.$category;
						$property->setValue((string) $newValue);	
						$iNumber=1;
					}else{
						$iNumber=0;
					}
				} 
			 }
			
			if($mode=='delete'){
				 	
				 	$temp=explode(',',stripslashes($vcard->CATEGORIES));
					if($temp==1){
						unset($vcard->CATEGORIES);
					}else{
						$newVal='';	
						foreach($temp as $val){
							if($newVal=='' && $val!=$category) $newVal=$val;
							else{
								if($val!=$category) $newVal.=','.$val;
							}
						}
						$vcard->CATEGORIES=$newVal;
					}
					
				//\OCP\Util::writeLog(App::$appname,'IOS CARD CAT ' . $newVal, \OCP\Util::DEBUG);
					 
			}
				
			self::edit($row['id'], $vcard);
			
			 
			  
		   }else{
		   	return false;
		   }
		}

	/**
	* @brief Checks if a contact with the same UID already exist in the address book.
	* @param $aid Address book ID.
	* @param $uid UID (passed by reference).
	* @returns true if the UID has been changed.
	*/
	protected static function trueUID($aid, &$uid) {
		$stmt = \OCP\DB::prepare( 'SELECT * FROM `'.App::ContactsTable.'` WHERE `addressbookid` = ? AND `uri` = ?' );
		$uri = $uid.'.vcf';
		try {
			$result = $stmt->execute(array($aid,$uri));
			if (\OCP\DB::isError($result)) {
				\OCP\Util::writeLog(App::$appname, __METHOD__. 'DB error: ' . \OCP\DB::getErrorMessage($result), \OCP\Util::ERROR);
				return false;
			}
		} catch(\Exception $e) {
			\OCP\Util::writeLog(App::$appname, __METHOD__.', exception: '.$e->getMessage(), \OCP\Util::ERROR);
			\OCP\Util::writeLog(App::$appname, __METHOD__.', aid: '.$aid.' uid'.$uid, \OCP\Util::DEBUG);
			return false;
		}
		if($result->numRows() > 0) {
			while(true) {
				$tmpuid = substr(md5(rand().time()), 0, 10);
				$uri = $tmpuid.'.vcf';
				$result = $stmt->execute(array($aid, $uri));
				if($result->numRows() > 0) {
					continue;
				} else {
					$uid = $tmpuid;
					return true;
				}
			}
		} else {
			return false;
		}
	}

	/**
	* @brief Tries to update imported VCards to adhere to rfc2426 (VERSION: 3.0) and add mandatory fields if missing.
	* @param aid Address book id.
	* @param vcard A Sabre\VObject\Component of type VCARD (passed by reference).
	*/
	protected static function updateValuesFromAdd($aid, &$vcard) { // any suggestions for a better method name? ;-)
		$stringprops = array('N', 'FN', 'ORG', 'NICK', 'ADR', 'NOTE', 'TEL', 'EMAIL', 'URL');
		$upgrade = false;
		$fn = $n = $uid = $email = $org = null;
		$version = isset($vcard->VERSION) ? $vcard->VERSION : null;
		// Add version if needed
		if($version && $version < '3.0') {
			$upgrade = true;
			\OCP\Util::writeLog(App::$appname, 'OCA\Contacts\VCard::updateValuesFromAdd. Updating from version: '.$version, \OCP\Util::DEBUG);
		}
		foreach($vcard->children as &$property) {
			// Decode string properties and remove obsolete properties.
			if($upgrade) {
				self::decodeProperty($property);
				self::fixPropertyParameters($vcard);
			}
			if(function_exists('iconv')) {
				$property->setValue(str_replace("\r\n", "\n", iconv(mb_detect_encoding($property->getValue(), 'UTF-8, ISO-8859-1'), 'utf-8', $property->getValue())));
			} else {
				$property->setValue(str_replace("\r\n", "\n", mb_convert_encoding($property->getValue(), 'UTF-8', mb_detect_encoding($property->getValue(), 'UTF-8, ISO-8859-1'), $property->getValue())));
			}
			if(in_array($property->name, $stringprops)) {
				$property->setValue(strip_tags($property->getValue()));
			}

			if($property->name == 'FN') {
				$fn = $property->getValue();
			}
			else if($property->name == 'N') {
				$n = $property->getValue();
			}
			else if($property->name == 'UID') {
				$uid = $property->getValue();
			}
			else if($property->name == 'ORG') {
				$org = $property->getValue();
			}
			else if($property->name == 'EMAIL' && is_null($email)) { // only use the first email as substitute for missing N or FN.
				$email = $property->getValue();
			}
		}
		// Check for missing 'N', 'FN' and 'UID' properties
		if(!$fn) {
			if($n && $n != ';;;;') {
				$fn = join(' ', array_reverse(array_slice(explode(';', $n), 0, 2)));
			} elseif($email) {
				$fn = $email;
			} elseif($org) {
				$fn = $org;
			} else {
				$fn = 'Unknown Name';
			}
			$vcard->FN = $fn;
			//OCP\Util::writeLog('contacts', 'OCA\Contacts\VCard::updateValuesFromAdd. Added missing \'FN\' field: '.$fn, OCP\Util::DEBUG);
		}
		if(!$n || $n == ';;;;') { // Fix missing 'N' field. Ugly hack ahead ;-)
			$slice = array_reverse(array_slice(explode(' ', $fn), 0, 2)); // Take 2 first name parts of 'FN' and reverse.
			if(count($slice) < 2) { // If not enought, add one more...
				$slice[] = "";
			}
			$n = implode(';', $slice).';;;';
			$vcard->N = $n;
			//OCP\Util::writeLog('contacts', 'OCA\Contacts\VCard::updateValuesFromAdd. Added missing \'N\' field: '.$n, OCP\Util::DEBUG);
		}
		if(!$uid) {
			$uid = substr(md5(rand().time()), 0, 10);
			$vcard->add('UID', $uid);
			//OCP\Util::writeLog('contacts', 'OCA\Contacts\VCard::updateValuesFromAdd. Added missing \'UID\' field: '.$uid, OCP\Util::DEBUG);
		}
		if(self::trueUID($aid, $uid)) {
			$vcard->{'UID'} = $uid;
		}
		$now = new \DateTime;
		$vcard->{'REV'} = $now->format(\DateTime::W3C);
	}

	/**
	 * @brief Adds a card
	 * @param $aid integer Addressbook id
	 * @param $card Sabre\VObject\Component  vCard file
	 * @param $uri string the uri of the card, default based on the UID
	 * @param $isChecked boolean If the vCard should be checked for validity and version.
	 * @return insertid on success or false.
	 */
	public static function add($aid, VObject\Component $card, $uri=null, $isChecked=false) {
		if(is_null($card)) {
			\OCP\Util::writeLog(App::$appname, __METHOD__ . ', No vCard supplied', \OCP\Util::ERROR);
			return null;
		};
		$addressbook = Addressbook::find($aid);
		if ($addressbook['userid'] != \OCP\User::getUser()) {
			$sharedAddressbook = \OCP\Share::getItemSharedWithBySource(App::SHAREADDRESSBOOK, App::SHAREADDRESSBOOKPREFIX.$aid);
			if (!$sharedAddressbook || !($sharedAddressbook['permissions'] & \OCP\PERMISSION_CREATE)) {
				throw new \Exception(
					App::$l10n->t(
						'You do not have the permissions to add contacts to this addressbook.'
					)
				);
			}
		}
		if(!$isChecked) {
			//self::updateValuesFromAdd($aid, $card);
		}
		$card->{'VERSION'} = '3.0';
		// Add product ID is missing.
		//$prodid = trim($card->getAsString('PRODID'));
		//if(!$prodid) {
		if(!isset($card->PRODID)) {
			$appinfo = \OCP\App::getAppInfo(App::$appname);
			$appversion = \OCP\App::getAppVersion(App::$appname);
			$prodid = '-//ownCloud//NONSGML '.$appinfo['name'].' '.$appversion.'//EN';
			$card->add('PRODID', $prodid);
		}
		$sComponent='VCARD';
		
		$fn = '';
		$lastname = '';
		$surename = '';	
				
			if(isset($card->N)){
				$temp=explode(';',$card->N);
				if(!empty($temp[0])){	
					$lastname = $temp[0];
					$surename = $temp[1];	
				}
			}
		
		$organization = '';
		if(isset($card->ORG)){
			$temp=explode(';',$card->ORG);	
			$organization = 	$temp[0];
		}
		
		$bCompany = isset($card->{'X-ABSHOWAS'}) ? 1 : 0;
		if($bCompany && $organization !== ''){
			$card->FN = $organization;
			$fn = $organization;
		}else{
			if($lastname !== ''){	
				$card->FN = $surename.' '.$lastname;
				$fn = $surename.' '.$lastname;
			}
		}
		
		$bGroup = isset($card->CATEGORIES) ? 1 : 0;
		if($bGroup){
			$card->CATEGORIES = stripslashes($card->CATEGORIES);	
		}
		
		$uid = $card->UID;
		if(!isset($card->UID)){
			$uid = substr(md5(rand().time()), 0, 10);
			$card->UID = $uid;
		}
		
		$uri = isset($uri) ? $uri : $uid . '.vcf';

		$data = $card->serialize();
		
		//\OCP\Util::writeLog(App::$appname,'XXXX: '.$fn, \OCP\Util::DEBUG);
		
       if(isset($card->{'X-ADDRESSBOOKSERVER-KIND'})){
       	   $sComponent='GROUP';
			
       }
		
		$stmt = \OCP\DB::prepare( 'INSERT INTO `'.App::ContactsTable.'` (`addressbookid`,`fullname`,`surename`,`lastname`,`carddata`,`uri`,`lastmodified`,`component`, `bcategory`,`organization`,`bcompany`) VALUES(?,?,?,?,?,?,?,?,?,?,?)' );
		try {
			$result = $stmt->execute(array($aid, $fn,$surename,$lastname, $data, $uri, time(),$sComponent,$bGroup,$organization,$bCompany));
			if (\OCP\DB::isError($result)) {
				\OCP\Util::writeLog(App::$appname, __METHOD__. 'DB error: ' . \OCP\DB::getErrorMessage($result), \OCP\Util::ERROR);
				return false;
			}
		} catch(\Exception $e) {
			\OCP\Util::writeLog(App::$appname, __METHOD__.', exception: '.$e->getMessage(), \OCP\Util::ERROR);
			\OCP\Util::writeLog(App::$appname, __METHOD__.', aid: '.$aid.' uri'.$uri, \OCP\Util::DEBUG);
			return false;
		}
		$newid = \OCP\DB::insertid(App::ContactsTable);
		App::loadCategoriesFromVCard($newid, $card);
		App::updateDBProperties($newid, $card);
		App::cacheThumbnail($newid);

		Addressbook::touch($aid);
		
		//Libasys
		if($sComponent=='GROUP'){
			//$newCardInfo=self::find($newid,array('fullname'));	
			//$stmt = \OCP\DB::prepare('INSERT INTO  `*PREFIX*vcategory` (`uid`,`type`,`category`,`color`)  VALUES(?,?,?,?) ');
			//$stmt->execute(array(\OCP\User::getUser(),'contact',$newCardInfo['fullname'],'#cccccc'));
		}
		//\OC_Hook::emit('\OCA\Kontakts\VCard', 'post_createVCard', $newid);
		return $newid;
	}

	/**
	 * @brief Adds a card with the data provided by sabredav
	 * @param integer $id Addressbook id
	 * @param string $uri   the uri the card will have
	 * @param string $data  vCard file
	 * @returns integer|false insertid or false on error
	 */
	public static function addFromDAVData($id, $uri, $data) {
		try {
			$vcard = VObject\Reader::read($data);
			
			return self::add($id, $vcard, $uri);
		} catch(\Exception $e) {
			\OCP\Util::writeLog(App::$appname, __METHOD__.', exception: '.$e->getMessage(), \OCP\Util::ERROR);
			return false;
		}
	}

	/**
	 * @brief Mass updates an array of cards
	 * @param array $objects  An array of [id, carddata].
	 */
	public static function updateDataByID($objects) {
		$stmt = \OCP\DB::prepare( 'UPDATE `'.App::ContactsTable.'` SET `carddata` = ?, `lastmodified` = ? WHERE `id` = ?' );
		$now = new \DateTime;
		foreach($objects as $object) {
			$vcard = null;
			try {
				$vcard = VObject\Reader::read($contact['carddata']);
			} catch(\Exception $e) {
				\OCP\Util::writeLog(App::$appname, __METHOD__. $e->getMessage(), \OCP\Util::ERROR);
			}
			if(!is_null($vcard)) {
				$oldcard = self::find($object[0]);
				if (!$oldcard) {
					return false;
				}

				$addressbook = Addressbook::find($oldcard['addressbookid']);
				if ($addressbook['userid'] != \OCP\User::getUser()) {
					$sharedContact = \OCP\Share::getItemSharedWithBySource(App::SHARECONTACT,App::SHARECONTACTPREFIX.$object[0], \OCP\Share::FORMAT_NONE, null, true);
					if (!$sharedContact || !($sharedContact['permissions'] & \OCP\PERMISSION_UPDATE)) {
						return false;
					}
				}
				$vcard->{'REV'} = $now->format(\DateTime::W3C);
				$data = $vcard->serialize();
				try {
					$result = $stmt->execute(array($data,time(),$object[0]));
					if (\OCP\DB::isError($result)) {
						\OCP\Util::writeLog(App::$appname, __METHOD__. 'DB error: ' . \OCP\DB::getErrorMessage($result), \OCP\Util::ERROR);
					}
					//OCP\Util::writeLog('contacts','OCA\Contacts\VCard::updateDataByID, id: '.$object[0].': '.$object[1],OCP\Util::DEBUG);
				} catch(\Exception $e) {
					\OCP\Util::writeLog(App::$appname, __METHOD__.', exception: '.$e->getMessage(), \OCP\Util::ERROR);
					\OCP\Util::writeLog(App::$appname, __METHOD__.', id: '.$object[0], \OCP\Util::DEBUG);
				}
				App::updateDBProperties($object[0], $vcard);
			}
		}
	}

	/**
	 * @brief edits a card
	 * @param integer $id id of card
	 * @param Sabre\VObject\Component $card  vCard file
	 * @return boolean true on success, otherwise an exception will be thrown
	 */
	public static function edit($id, VObject\Component $card) {
		$oldcard = self::find($id);
		if (!$oldcard) {
			\OCP\Util::writeLog(App::$appname, __METHOD__.', id: '
				. $id . ' not found.', \OCP\Util::DEBUG);
			throw new \Exception(
				App::$l10n->t(
					'Could not find the vCard with ID.' . $id
				)
			);
		}
		if(is_null($card)) {
			return false;
		}
		// NOTE: Owner checks are being made in the ajax files, which should be done
		// inside the lib files to prevent any redundancies with sharing checks
		$addressbook = Addressbook::find($oldcard['addressbookid']);
		if ($addressbook['userid'] != \OCP\User::getUser()) {
			$sharedAddressbook = \OCP\Share::getItemSharedWithBySource(
				App::SHAREADDRESSBOOK,
				App::SHAREADDRESSBOOKPREFIX.$oldcard['addressbookid'],
				\OCP\Share::FORMAT_NONE, null, true);
			$sharedContact = \OCP\Share::getItemSharedWithBySource(App::SHARECONTACT, App::SHAREADDRESSBOOKPREFIX.$id, \OCP\Share::FORMAT_NONE, null, true);
			$addressbook_permissions = 0;
			$contact_permissions = 0;
			if ($sharedAddressbook) {
				$addressbook_permissions = $sharedAddressbook['permissions'];
			}
			if ($sharedContact) {
				$contact_permissions = $sharedEvent['permissions'];
			}
			$permissions = max($addressbook_permissions, $contact_permissions);
			if (!($permissions & \OCP\PERMISSION_UPDATE)) {
				throw new \Exception(
					App::$l10n->t(
						'You do not have the permissions to edit this contact.'
					)
				);
			}
		}
		//App::loadCategoriesFromVCard($id, $card);
        $sComponent='VCARD';
		//\OCP\Util::writeLog(App::$appname,'XXXX: '.$card->{'X-ADDRESSBOOKSERVER-KIND'}->getValue(), \OCP\Util::DEBUG);
       if(isset($card->{'X-ADDRESSBOOKSERVER-KIND'})){
       	   $sComponent='GROUP';
       }
	   
	   $fn = '';
		$lastname = '';
		$surename = '';	
	
			
		if(isset($card->N)){
			$temp=explode(';',$card->N);
			if(!empty($temp[0])){	
				$lastname = $temp[0];
				$surename = $temp[1];
			}
		}

		$organization = '';
		if(isset($card->ORG)){
			$temp=explode(';',$card->ORG);	
			$organization = 	$temp[0];
		}
		
		$bCompany = isset($card->{'X-ABSHOWAS'}) ? 1 : 0;
		if($bCompany && $organization !== ''){
			$card->FN = $organization;
			$fn = $organization;
		}else{
			if($lastname !== ''){	
				$fn = $surename.' '.$lastname;
				$card->FN = $fn;
			}
		}
		
		$bGroup = isset($card->CATEGORIES) ? 1 : 0;
		$now = new \DateTime;
		$card->REV = $now->format(\DateTime::W3C);

		$data = $card->serialize();
		
		
		$stmt = \OCP\DB::prepare( 'UPDATE `'.App::ContactsTable.'` SET `fullname` = ?,`surename` = ?,`lastname` = ?,`carddata` = ?, `lastmodified` = ?, `component` = ? ,`bcategory` = ?,`organization` = ?,`bcompany` = ? WHERE `id` = ?' );
		try {
			$result = $stmt->execute(array($fn,$surename, $lastname, $data, time(), $sComponent, $bGroup,$organization,$bCompany, $id));
			if (\OCP\DB::isError($result)) {
				\OCP\Util::writeLog(App::$appname, __METHOD__. 'DB error: ' . \OCP\DB::getErrorMessage($result), \OCP\Util::ERROR);
				return false;
			}
		} catch(\Exception $e) {
			\OCP\Util::writeLog(App::$appname, __METHOD__.', exception: '. $e->getMessage(), \OCP\Util::ERROR);
			\OCP\Util::writeLog(App::$appname, __METHOD__.', id'.$id, \OCP\Util::DEBUG);
			return false;
		}

		App::cacheThumbnail($oldcard['id']);
		App::updateDBProperties($id, $card);
		Addressbook::touch($oldcard['addressbookid']);
		App::loadCategoriesFromVCard($id, $card);
		//\OC_Hook::emit('\OCA\Contacts\VCard', 'post_updateVCard', $id);
		return true;
	}

	/**
	 * @brief edits a card with the data provided by sabredav
	 * @param integer $id Addressbook id
	 * @param string $uri   the uri of the card
	 * @param string $data  vCard file
	 * @return boolean
	 */
	public static function editFromDAVData($aid, $uri, $data) {
		$oldcard = self::findWhereDAVDataIs($aid, $uri);
		// Force update of thumbnail when from CardDAV
		App::cacheThumbnail($oldcard['id'], null, false, true);
		try {
			$vcard = \Sabre\VObject\Reader::read($data);
		} catch(\Exception $e) {
			\OCP\Util::writeLog(App::$appname, __METHOD__.
				', Unable to parse VCARD, : ' . $e->getMessage(), \OCP\Util::ERROR);
			return false;
		}

		self::fixPropertyParameters($vcard);

		try {
			//Libasys
			$iosSupport=\OCP\Config::getUserValue(\OCP\USER::getUser(), App::$appname, 'iossupport');
			
			if($oldcard['component']=='GROUP' && $iosSupport){
				
				try {
					$oldvcard = \Sabre\VObject\Reader::read($oldcard['carddata']);
				} catch(\Exception $e) {
					\OCP\Util::writeLog(App::$appname, __METHOD__.
						', Unable to parse VCARD, : ' . $e->getMessage(), \OCP\Util::ERROR);
					return false;
				}
				
				$oldCardCount=count($oldvcard->{'X-ADDRESSBOOKSERVER-MEMBER'});
				$newCardCount=count($vcard->{'X-ADDRESSBOOKSERVER-MEMBER'});
				
				$oldCardMember=$oldvcard->{'X-ADDRESSBOOKSERVER-MEMBER'};
				$newCardMember=$vcard->{'X-ADDRESSBOOKSERVER-MEMBER'};
				$aOldCardMember=array();
				if($oldCardMember){
					foreach($oldCardMember as $key =>$param){
						 $aOldCardMember[$key]=$param;
					}
				}
				$aNewCardMember=array();
				if($newCardMember){
					foreach($newCardMember as $key =>$param){
						 $aNewCardMember[$key]=$param;
					}
				}
				
				if($oldCardCount != $newCardCount){
					if($oldCardCount>$newCardCount)	{
						//Some Member Deleted
						$resultMember = array_diff_assoc($aOldCardMember,$aNewCardMember);
						foreach($resultMember as $val){
							$uid=substr($val, 9);	
							self::updateCardByUid($uid,'delete',$oldcard['fullname']);
						}
					}
					if($oldCardCount<$newCardCount)	{
						//Some Member Added
						$resultMember = array_diff_assoc($aNewCardMember,$aOldCardMember);
						foreach($resultMember as $val){
							$uid=substr($val,9);
							self::updateCardByUid($uid,'add',$oldcard['fullname']);
						}
					}
					\OCP\Util::writeLog(App::$appname,'IOS GROUP Something happens'.$oldCardCount.':'.$newCardCount, \OCP\Util::DEBUG);
				}
			}
			//give vcard one format;	
			//$davInfo = self::structureContact($vcard);
			//$newVcard = self::prepareVCard($davInfo,$vcard);	
			self::edit($oldcard['id'], $vcard);
			
			
			return true;
		} catch(\Exception $e) {
			\OCP\Util::writeLog(App::$appname, __METHOD__.', exception: '
				. $e->getMessage() . ', '
				. \OCP\USER::getUser(), \OCP\Util::ERROR);
			\OCP\Util::writeLog(App::$appname, __METHOD__.', uri'
				. $uri, \OCP\Util::DEBUG);
			return false;
		}
	}

	/**
	 * @brief deletes a card
	 * @param integer $id id of card
	 * @return boolean true on success, otherwise an exception will be thrown
	 */
	public static function delete($id) {
		$contact = self::find($id);
		if (!$contact) {
			\OCP\Util::writeLog(App::$appname, __METHOD__.', id: '
				. $id . ' not found.', \OCP\Util::DEBUG);
			throw new \Exception(
				App::$l10n->t(
					'Could not find the vCard with ID: ' . $id, 404
				)
			);
		}
		$addressbook = Addressbook::find($contact['addressbookid']);
		if(!$addressbook) {
			throw new \Exception(
				App::$l10n->t(
					'Could not find the Addressbook with ID: '
					. $contact['addressbookid'], 404
				)
			);
		}

		if ($addressbook['userid'] != \OCP\User::getUser() && !\OC_Group::inGroup(\OCP\User::getUser(), 'admin')) {
			\OCP\Util::writeLog('contacts', __METHOD__.', '
				. $addressbook['userid'] . ' != ' . \OCP\User::getUser(), \OCP\Util::DEBUG);
			$sharedAddressbook = \OCP\Share::getItemSharedWithBySource(
				App::SHAREADDRESSBOOK,
				App::SHAREADDRESSBOOKPREFIX.$contact['addressbookid'],
				\OCP\Share::FORMAT_NONE, null, true);
			$sharedContact = \OCP\Share::getItemSharedWithBySource(
				App::SHARECONTACT,
				App::SHARECONTACTPREFIX.$id,
				\OCP\Share::FORMAT_NONE, null, true);
			$addressbook_permissions = 0;
			$contact_permissions = 0;
			if ($sharedAddressbook) {
				$addressbook_permissions = $sharedAddressbook['permissions'];
			}
			if ($sharedContact) {
				$contact_permissions = $sharedEvent['permissions'];
			}
			$permissions = max($addressbook_permissions, $contact_permissions);

			if (!($permissions & \OCP\PERMISSION_DELETE)) {
				throw new \Exception(
					App::$l10n->t(
						'You do not have the permissions to delete this contact.', 403
					)
				);
			}
		}
		$aid = $contact['addressbookid'];
	//	\OC_Hook::emit('\OCA\ContactsPlus\VCard', 'pre_deleteVCard',
	//		array('aid' => null, 'id' => $id, 'uri' => null)
	//	);
		$favorites = \OC::$server -> getTagManager() -> load(App::$appname)->getFavorites();	
		if(count($favorites)>0){
			$favorites = \OC::$server -> getTagManager() -> load(App::$appname)->removeFromFavorites($id);
		}
	
		$stmt = \OCP\DB::prepare('DELETE FROM `'.App::ContactsTable.'` WHERE `id` = ?');
		try {
			$stmt->execute(array($id));
		} catch(\Exception $e) {
			\OCP\Util::writeLog(App::$appname, __METHOD__.
				', exception: ' . $e->getMessage(), \OCP\Util::ERROR);
			\OCP\Util::writeLog(App::$appname, __METHOD__.', id: '
				. $id, \OCP\Util::DEBUG);
			throw new \Exception(
				App::$l10n->t(
					'There was an error deleting this contact.'
				)
			);
		}

		App::updateDBProperties($id);
		//App::getVCategories()->purgeObject($id);
		Addressbook::touch($addressbook['id']);

		\OCP\Share::unshareAll(App::SHARECONTACT, $id);
		return true;
	}

	/**
	 * @brief deletes a card with the data provided by sabredav
	 * @param integer $aid Addressbook id
	 * @param string $uri the uri of the card
	 * @return boolean
	 */
	public static function deleteFromDAVData($aid, $uri) {
		$contact = self::findWhereDAVDataIs($aid, $uri);
		if(!$contact) {
			\OCP\Util::writeLog(App::$appname, __METHOD__.', contact not found: '
				. $uri, \OCP\Util::DEBUG);
			throw new \Sabre_DAV_Exception_NotFound(
				App::$l10n->t(
					'Contact not found.'
				)
			);
		}
		$id = $contact['id'];
		try {
			//Libasys
			$iosSupport=\OCP\Config::getUserValue(\OCP\USER::getUser(), App::$appname, 'iossupport');
			if($contact['component']=='GROUP' && $iosSupport){
				$stmt = \OCP\DB::prepare('DELETE FROM `*PREFIX*vcategory` WHERE `category` = ? AND `uid` = ?');
			    $stmt->execute(array($contact['fullname'],\OCP\User::getUser()));
			}
			
			return self::delete($id);
		} catch (\Exception $e) {
			switch($e->getCode()) {
				case 403:
					\OCP\Util::writeLog(App::$appname, __METHOD__.', forbidden: '
						. $uri, \OCP\Util::DEBUG);
					throw new \Sabre_DAV_Exception_Forbidden(
						App::$l10n->t(
							$e->getMessage()
						)
					);
					break;
				case 404:
					\OCP\Util::writeLog(App::$appname, __METHOD__.', contact not found: '
						. $uri, \OCP\Util::DEBUG);
					throw new \Sabre_DAV_Exception_NotFound(
						App::$l10n->t(
							$e->getMessage()
						)
					);
					break;
				default:
					throw $e;
					break;
			}
		}
		return true;
	}

	/**
	 * @brief Data structure of vCard
	 * @param Sabre\VObject\Component $property
	 * @return associative array
	 *
	 * look at code ...
	 */
	public static function structureContact($vcard) {
		$details = array();
		if(is_array($vcard->children)){
		foreach($vcard->children as $property) {
			$pname = $property->name;
			$temp = self::structureProperty($property);
			if(!is_null($temp)) {
				// Get Apple X-ABLabels
				if(isset($vcard->{$property->group . '.X-ABLABEL'})) {
					$temp['label'] = $vcard->{$property->group . '.X-ABLABEL'}->getValue();
					if($temp['label'] == '_$!<Other>!$_') {
						$temp['label'] = App::$l10n->t('Other');
					}
					if($temp['label'] == '_$!<HomePage>!$_') {
						$temp['label'] = App::$l10n->t('HomePage');
					}
				}
				if(isset($vcard->{$property->group.'.X-ABSHOWAS'})) {
					$temp['SHOWAS'] = $vcard->{$property->group.'.X-ABSHOWAS'}->getValue();
				}
				if(array_key_exists($pname, $details)) {
					$details[$pname][] = $temp;
				}
				else{
					$details[$pname] = array($temp);
				}
			}
		}
			return $details;
		}else{
			return false;
		}
	}
	
	/**
	 * @brief returns the calendarid of an object
	 * @param integer $id
	 * @return integer
	 */
	public static function getAddressbookid($id) {
		$vcard = self::find($id,array('addressbookid'));
		return $vcard['addressbookid'];
	}

	/**
	 * @brief Data structure of properties
	 * @param object $property
	 * @return associative array
	 *
	 * returns an associative array with
	 * ['name'] name of property
	 * ['value'] htmlspecialchars escaped value of property
	 * ['parameters'] associative array name=>value
	 * ['checksum'] checksum of whole property
	 * NOTE: $value is not escaped anymore. It shouldn't make any difference
	 * but we should look out for any problems.
	 */
	public static function structureProperty(\Sabre\VObject\Property $property) {
		if(!in_array($property->name, App::$index_properties)) {
			return;
		}
		$value = $property->getValue();
		if($property->name == 'ADR' || $property->name == 'N' || $property->name == 'ORG' || $property->name == 'CATEGORIES') {
			$value = $property->getParts();
			if($property->name == 'CATEGORIES'){
				$value = str_replace(';', ',', $value);
			}
			if($property->name == 'N'){
				
				//$value = stripslashes($value);
				//	\OCP\Util::writeLog('contactsplus','NAME VAL: '.$value, \OCP\Util::DEBUG);	
				
			}
			$value = array_map('trim', $value);
			
		}elseif($property->name == 'BDAY') {
			if(strlen($value) >= 8
				&& is_int(substr($value, 0, 4))
				&& is_int(substr($value, 4, 2))
				&& is_int(substr($value, 6, 2))) {
				$value = substr($value, 0, 4).'-'.substr($value, 4, 2).'-'.substr($value, 6, 2);
			} else if($value[5] !== '-' || $value[7] !== '-') {
				try {
					// Skype exports as e.g. Jan 14, 1996
					$date = new \DateTime($value);
					$value = $date->format('Y-m-d');
				} catch(\Exception $e) {
					\OCP\Util::writeLog('contactsplus', __METHOD__.' Error parsing date: ' . $value, \OCP\Util::DEBUG);
					return;
				}
			}
		} elseif($property->name == 'PHOTO') {
			$value = true;
		}
		elseif($property->name == 'IMPP') {
			if(strpos($value, ':') !== false) {
				$value = explode(':', $value);
				$protocol = array_shift($value);
				if(!isset($property['X-SERVICE-TYPE'])) {
					$property['X-SERVICE-TYPE'] = strtoupper($protocol);
				}
				$value = implode('', $value);
			}
		}
		if(is_string($value)) {
			$value = strtr($value, array('\,' => ',', '\;' => ';'));
		}
		
		$temp = array(
			//'name' => $property->name,
			'value' => $value,
			'parameters' => array()
		);
      		
		// This cuts around a 3rd off of the json response size.
		if(in_array($property->name, App::$multi_properties)) {
			$temp['checksum'] = substr(md5($property->serialize()), 0, 8);
		}
		
		foreach($property->parameters as $parameter) {
			// Faulty entries by kaddressbook
			// Actually TYPE=PREF is correct according to RFC 2426
			// but this way is more handy in the UI. Tanghus.
			if($parameter->name == 'TYPE' && strtoupper($parameter->getValue()) == 'PREF') {
				$parameter->name = 'PREF';
				$parameter->setValue('1');
			}
			// NOTE: Apparently Sabre_VObject_Reader can't always deal with value list parameters
			// like TYPE=HOME,CELL,VOICE. Tanghus.
			// TODO: Check if parameter is has commas and split + merge if so.
			if ($parameter->name == 'TYPE') {
				$pvalue = $parameter->getValue();
				if(is_string($pvalue) && strpos($pvalue, ',') !== false) {
					$pvalue = array_map('trim', explode(',', $pvalue));
				}
				$pvalue = is_array($pvalue) ? $pvalue : array($pvalue);
				if (isset($temp['parameters'][$parameter->name])) {
					$temp['parameters'][$parameter->name][] = \OCP\Util::sanitizeHTML($pvalue);
				}
				else {
					
					$temp['parameters'][$parameter->name] = \OCP\Util::sanitizeHTML($pvalue);
				}
			}
			else{
				//$value = strtr($value, array('\,' => ',', '\;' => ';'));	
				$temp['parameters'][$parameter->name] = \OCP\Util::sanitizeHTML($parameter->getValue());
			}
		}
		return $temp;
	}


    public static function buildVCard($VDATA){
    	
		$rArray=array();
		
		
    }

	/**
	 * @brief Move card(s) to an address book
	 * @param integer $aid Address book id
	 * @param $id Array or integer of cards to be moved.
	 * @return boolean
	 *
	 */
	public static function moveToAddressBook($aid, $id, $isAddressbook = false) {
		
		$addressbook = Addressbook::find($aid);
		if ($addressbook['userid'] != \OCP\User::getUser()) {
			$sharedAddressbook = \OCP\Share::getItemSharedWithBySource(App::SHAREADDRESSBOOK, App::SHAREADDRESSBOOKPREFIX. $aid);
			if (!$sharedAddressbook || !($sharedAddressbook['permissions'] & \OCP\PERMISSION_CREATE)) {
				throw new \Exception(App::$l10n->t('You don\'t have permissions to move contacts into this address book'));
			}
		}
		if(is_array($id)) {
			// NOTE: This block is currently not used and need rewrite if used!
			foreach ($id as $index => $cardId) {
				$card = self::find($cardId);
				if (!$card) {
					unset($id[$index]);
				}
				$oldAddressbook = Addressbook::find($card['addressbookid']);
				if ($oldAddressbook['userid'] != \OCP\User::getUser()) {
					$sharedContact = \OCP\Share::getItemSharedWithBySource(App::SHARECONTACT,App::SHARECONTACTPREFIX.$cardId, \OCP\Share::FORMAT_NONE, null, true);
					if (!$sharedContact || !($sharedContact['permissions'] & \OCP\PERMISSION_DELETE)) {
						unset($id[$index]);
					}
				}
			}
			$id_sql = join(',', array_fill(0, count($id), '?'));
			$prep = 'UPDATE `'.App::ContactsTable.'` SET `addressbookid` = ? WHERE `id` IN ('.$id_sql.')';
			try {
				$stmt = \OCP\DB::prepare( $prep );
				//$aid = array($aid);
				$vals = array_merge((array)$aid, $id);
				$result = $stmt->execute($vals);
				if (\OCP\DB::isError($result)) {
					\OCP\Util::writeLog(App::$appname, __METHOD__. 'DB error: ' . \OCP\DB::getErrorMessage($result), \OCP\Util::ERROR);
					throw new \Exception(App::$l10n->t('Database error during move.'));
				}
			} catch(\Exception $e) {
				\OCP\Util::writeLog(App::$appname, __METHOD__.', exception: '.$e->getMessage(), \OCP\Util::ERROR);
				\OCP\Util::writeLog(App::$appname, __METHOD__.', ids: '.join(',', $vals), \OCP\Util::DEBUG);
				\OCP\Util::writeLog(App::$appname, __METHOD__.', SQL:'.$prep, \OCP\Util::DEBUG);
				throw new \Exception(App::$l10n->t('Database error during move.'));
			}
		} else {
			$stmt = null;
			if($isAddressbook) {
				$stmt = \OCP\DB::prepare( 'UPDATE `'.App::ContactsTable.'` SET `addressbookid` = ? WHERE `addressbookid` = ?' );
			} else {
				$card = self::find($id);
				if (!$card) {
					throw new \Exception(App::$l10n->t('Error finding card to move.'));
				}
				$oldAddressbook = Addressbook::find($card['addressbookid']);
				if ($oldAddressbook['userid'] != \OCP\User::getUser()) {
					$sharedAddressbook = \OCP\Share::getItemSharedWithBySource(App::SHAREADDRESSBOOK, App::SHAREADDRESSBOOKPREFIX.$oldAddressbook['id']);
					if (!$sharedAddressbook || !($sharedAddressbook['permissions'] & \OCP\PERMISSION_DELETE)) {
						throw new \Exception(App::$l10n->t('You don\'t have permissions to move contacts from this address book'));
					}
				}
				Addressbook::touch($oldAddressbook['id']);
				$stmt = \OCP\DB::prepare( 'UPDATE `'.App::ContactsTable.'` SET `addressbookid` = ? WHERE `id` = ?' );
			}
			try {
				$result = $stmt->execute(array($aid, $id));
				if (\OCP\DB::isError($result)) {
					\OCP\Util::writeLog(App::$appname, __METHOD__. 'DB error: ' . \OCP\DB::getErrorMessage($result), \OCP\Util::ERROR);
					throw new \Exception(App::$l10n->t('Database error during move.'));
				}
			} catch(\Exception $e) {
				\OCP\Util::writeLog(App::$appname, __METHOD__.', exception: '.$e->getMessage(), \OCP\Util::DEBUG);
				\OCP\Util::writeLog(App::$appname, __METHOD__.' id: '.$id, \OCP\Util::DEBUG);
				throw new \Exception(App::$l10n->t('Database error during move.'));
			}
		}
		//\OC_Hook::emit('\OCA\Contacts\VCard', 'post_moveToAddressbook', array('aid' => $aid, 'id' => $id));
		Addressbook::touch($aid);
		return true;
	}

     /*
	  * @param $importInfo structureContact
	  * @param $vcard  vcard object
	  */
	public static function prepareVCard(array $importInfo, $vcard){
		
		if(isset($importInfo['N'][0]['value']) && count($importInfo['N'][0]['value'])>0){
			$aName=array();	
			
			foreach($importInfo['N'][0]['value'] as $key => $val){
				
				$aName[$key] = $val;
			}	
			$vcard->N = $aName;
		}
		 if(isset($importInfo['ORG'][0]['value']) && count($importInfo['ORG'][0]['value'])>0){
			$aOrg=array();
			 foreach($importInfo['ORG'][0]['value'] as $key => $val){
			 	$aOrg[$key] = $val;
			 }	
			$vcard->ORG = $aOrg;
		 }
		 
		 if(isset($importInfo['NICKNAME'][0]['value']) && !empty($importInfo['NICKNAME'][0]['value'])){
		 	$vcard->NICKNAME = $importInfo['NICKNAME'][0]['value'];
		 }
		 
		 if(isset($importInfo['TITLE'][0]['value']) && !empty($importInfo['TITLE'][0]['value'])){
		 	$vcard->TITLE= $importInfo['TITLE'][0]['value'];
		 }
		 
		  if(isset($importInfo['BDAY'][0]['value']) && !empty($importInfo['BDAY'][0]['value'])){
				$date = New \DateTime($importInfo['BDAY'][0]['value']);
			    $value = $date->format('Ymd');
				$vcard->BDAY = $value;
				$vcard->BDAY->add('VALUE', 'DATE');
		  }

		 if(array_key_exists('CATEGORIES', $importInfo)){
		 	unset($vcard->CATEGORIES);
			 $sCat = '';
			 foreach($importInfo['CATEGORIES'] as $catInfo){
			 	foreach($catInfo['value'] as $key => $val){
			 		$sCat .=($sCat === '' )?$val:','.$val;
			 	}	
			 	
			 }
			 $vcard->CATEGORIES = $sCat;
		 }

		 if(array_key_exists('ADR', $importInfo)){
		 	unset($vcard->ADR);
			 	
		 	foreach($importInfo['ADR'] as  $addrInfo){
		 		$PREF = 0;	
		 		if(array_key_exists('PREF', $addrInfo['parameters'])){
					$PREF =1;
				}
				
				$VAL = array();
				foreach($addrInfo['value'] as $key => $val){	
						$VAL[$key] = $val;	
				}
				
				$sType ='';	
				if(array_key_exists('TYPE', $addrInfo['parameters'])){
					foreach($addrInfo['parameters']['TYPE'] as $typeInfo){
						$typeInfo = strtoupper($typeInfo);
						if($typeInfo != 'PREF' && $typeInfo !== ''){
							$sType .=($sType === '' )?$typeInfo:','.$typeInfo;
						}
						if($typeInfo === 'PREF'){
							$PREF =1;
						}
					}
				}
				
				if($sType == ''){
					$sType = 'HOME';
				}
				
				if($PREF ===1){
					$vcard->add('ADR',$VAL,array('type'=>$sType,'pref' =>'1'));
				}else{
					$vcard->add('ADR',$VAL,array('type'=>$sType));
				}
		 	}
		 }

		if(array_key_exists('TEL', $importInfo)){
		 	unset($vcard->TEL);
			 	
		 	foreach($importInfo['TEL'] as  $telInfo){
		 		$PREF = 0;	
		 		if(array_key_exists('PREF', $telInfo['parameters'])){
					$PREF =1;
				}
				
				$VAL = $telInfo['value'];
				
				$sType ='';	
				if(array_key_exists('TYPE', $telInfo['parameters'])){
					foreach($telInfo['parameters']['TYPE'] as $typeInfo){
						$typeInfo = strtoupper($typeInfo);
						if($typeInfo != 'PREF' && $typeInfo !== ''){
							$sType .=($sType === '' )?$typeInfo:','.$typeInfo;
						}
						if($typeInfo === 'PREF'){
							$PREF =1;
						}
					}
				}
				if($sType == ''){
					$sType = 'WORK,VOICE';
				}
				if($PREF === 1){
					$vcard->add('TEL',$VAL,array('type'=>$sType,'pref' =>'1'));
				}else{
					$vcard->add('TEL',$VAL,array('type'=>$sType));
				}
		 	}
		 }
		
		 if(array_key_exists('EMAIL', $importInfo)){
		 	unset($vcard->EMAIL);
			 	
		 	foreach($importInfo['EMAIL'] as  $emailInfo){
		 		$PREF = 0;	
		 		if(array_key_exists('PREF', $emailInfo['parameters'])){
					$PREF =1;
				}
				
				$VAL = $emailInfo['value'];
				
				$sType ='';	
				if(array_key_exists('TYPE', $emailInfo['parameters'])){
					foreach($emailInfo['parameters']['TYPE'] as $typeInfo){
						$typeInfo = strtoupper($typeInfo);
						if($typeInfo != 'PREF' && $typeInfo !== ''){
							$sType .=($sType === '' )?$typeInfo:','.$typeInfo;
						}
						if($typeInfo === 'PREF'){
							$PREF = 1;
						}
					}
				}
				
				if($sType == ''){
					$sType = 'OTHER';
				}
				
				if($PREF === 1){
					$vcard->add('EMAIL',$VAL,array('type'=>$sType,'pref' =>'1'));
				}else{
					$vcard->add('EMAIL',$VAL,array('type'=>$sType));
				}
		 	}
		 }
		 
		if(array_key_exists('URL', $importInfo)){
		 	unset($vcard->URL);
			 	
		 	foreach($importInfo['URL'] as  $urlInfo){
		 		$PREF = 0;	
		 		if(array_key_exists('PREF', $urlInfo['parameters'])){
					$PREF =1;
				}
				
				$VAL = $urlInfo['value'];
				
				$sType ='';	
				if(array_key_exists('TYPE', $urlInfo['parameters'])){
					foreach($urlInfo['parameters']['TYPE'] as $typeInfo){
						$typeInfo = strtoupper($typeInfo);
						if($typeInfo != 'PREF' && $typeInfo !== ''){
							$sType .=($sType === '' )?$typeInfo:','.$typeInfo;
						}
						if($typeInfo === 'PREF'){
							$PREF =1;
						}
					}
				}
				
				if($sType == ''){
					$sType = 'OTHER';
				}
				
				if($PREF === 1){
					$vcard->add('URL',$VAL,array('type'=>$sType,'pref' =>'1'));
				}else{
					$vcard->add('URL',$VAL,array('type'=>$sType));
				}
		 	}
		 }
		
		 return $vcard;	
		
	}
}
