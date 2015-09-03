<?php
/**
 * Copyright (c) 2012 Georg Ehrke <ownclouddev@georgswebsite.de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
/*
 * This class does import and converts all times to the users current timezone
 */
 namespace OCA\ContactsPlus;
 

class Import{
	/*
	 * @brief counts the absolute number of parsed elements
	 */
	private $abscount;

	/*
	 * @brief var saves if the percentage should be saved with \OC::$server->getCache()
	 */
	private $cacheprogress;

	/*
	 * @brief Sabre\VObject\Component\VCalendar object - for documentation see http://code.google.com/p/sabredav/wiki/Sabre_VObject_Component_VCalendar
	 */
	private $vcardobject;

	/*
	 * @brief var counts the number of imported elements
	 */
	private $count;

	/*
	 * @brief var to check if errors happend while initialization
	 */
	private $error;
	
	private $errorCount = 0;

	/*
	 * @brief var saves the ical string that was submitted with the __construct function
	 */
	private $ivcard;

	/*
	 * @brief calendar id for import
	 */
	private $id;

	/*
	 * @brief overwrite flag
	 */
	private $overwrite;

	/*
	 * @brief var saves the percentage of the import's progress
	 */
	private $progress;

	/*
	 * @brief var saves the key for the percentage of the import's progress
	 */
	private $progresskey;

	

	/*
	 * @brief var saves the userid
	 */
	private $userid;

	/*
	 * public methods
	 */

	/*
	 * @brief does general initialization for import object
	 * @param string $calendar content of ical file
	 * @param string $tz timezone of the user
	 * @return boolean
	 */
	public function __construct($vcard) {
		$this->error = null;
		$this->ivcard = $vcard;
		$this->abscount = 0;
		$this->count = 0;
		
		try{
			$this->prepareFile();
			//$this->vcardobject = \Sabre\VObject\Reader::read($this->ivcard, \Sabre\VObject\Reader::OPTION_IGNORE_INVALID_LINES);
		}catch(Exception $e) {
			//MISSING: write some log
			$this->error = true;
			return false;
		}
		return true;
	}

	private function prepareFile(){
		$nl = "\n";
			
		$this->ivcard = str_replace(array("\r","\n\n"), array("\n","\n"), $this->ivcard);
		$lines = explode($nl, $this->ivcard);
		
		$inelement = false;
		$parts = array();
		$card = array();
		foreach($lines as $line) {
			if(strtoupper(trim($line)) == 'BEGIN:VCARD') {
				$inelement = true;
			} elseif (strtoupper(trim($line)) == 'END:VCARD') {
				$card[] = $line;
				$parts[] = implode($nl, $card);
				$card = array();
				$inelement = false;
			}
			if ($inelement === true && trim($line) != '') {
				$card[] = $line;
			}
		}
		//$this->vcardobject = \Sabre\VObject\Reader::read($card, \Sabre\VObject\Reader::OPTION_IGNORE_INVALID_LINES);
		$this->vcardobject = $parts;
		
	}

	/*
	 * @brief imports a vcard
	 * @return boolean
	 */
	public function import() {
		if (is_null($this->userid)) {
			throw new \Exception('No user id set');
		}
		if(!$this->isValid()) {
			return false;
		}
		$numofcomponents = count($this->vcardobject);
		
		
		if($this->overwrite) {
			foreach(VCard::all($this->id) as $obj) {
				VCard::delete($obj['id']);
			}
		}
		
		foreach($this->vcardobject as $object) {
			if(!($object instanceof \Sabre\VObject\Component\VCard)) {
				
				//continue;
			}
			$vcard = \Sabre\VObject\Reader::read($object, \Sabre\VObject\Reader::OPTION_IGNORE_INVALID_LINES);
			
			$importVCard = VCard::structureContact($vcard);
			if($importVCard!==false){
				$newVcard = $this->parseVCard($importVCard,$vcard);
				
				$insertid = VCard::add($this->id, $newVcard);
				$this->abscount++;
			}else{
				$this->errorCount++;	
			}
			
			if($this->isDuplicate($insertid)) {
				VCard::delete($insertid);
				$this->abscount--;
			}
			$this->updateProgress(intval(($this->abscount / $numofcomponents)*100));
		}
		\OC::$server->getCache()->remove($this->progresskey);
		return true;
	}

	
	/*
	 * @brief sets the overwrite flag
	 * @return boolean
	 */
	public function setOverwrite($overwrite) {
		$this->overwrite = (bool) $overwrite;
		return true;
	}

	/*
	 * @brief sets the progresskey
	 * @return boolean
	 */
	public function setProgresskey($progresskey) {
		$this->progresskey = $progresskey;
		return true;
	}

	/*
	 * @brief checks if something went wrong while initialization
	 * @return boolean
	 */
	public function isValid() {
		if(is_null($this->error)) {
			return true;
		}
		return false;
	}

	/*
	 * @brief returns the percentage of progress
	 * @return integer
	 */
	public function getProgress() {
		return $this->progress;
	}

	/*
	 * @brief enables the cache for the percentage of progress
	 * @return boolean
	 */
	public function enableProgressCache() {
		$this->cacheprogress = true;
		return true;
	}

	/*
	 * @brief disables the cache for the percentage of progress
	 * @return boolean
	 */
	public function disableProgressCache() {
		$this->cacheprogress = false;
		return false;
	}

	

	/*
	 * @brief sets the id for the calendar
	 * @param integer $id of the calendar
	 * @return boolean
	 */
	public function setAddressbookId($id) {
		$this->id = $id;
		return true;
	}

	/*
	 * @brief sets the userid to import the calendar
	 * @param string $id of the user
	 * @return boolean
	 */
	public function setUserID($userid) {
		$this->userid = $userid;
		return true;
	}

	/*
	 * @brief returns the private
	 * @param string $id of the user
	 * @return boolean
	 */
	public function getCount() {
		return $this->abscount;
	}

	public function getErrorCount(){
		return $this->errorCount;
	}
	/*
	 * @brief generates a new calendar name
	 * @return string
	 */
	public function createAddressbookName() {
		/*	
		Addressbook::add();	
		$addressbooks = Addressbook::all($this->userid);
		
		$addressbookname = App::$l10n->t('New Addressbook');
		$i = 1;
		while(!Calendar::isCalendarNameavailable($addressbookname, $this->userid)) {
			$calendarname = $guessedcalendarname . ' (' . $i . ')';
			$i++;
		}
		return $addressbookname;*/
	}

	/*
	 * private methods
	 */

	/*
	 * @brief generates an unique ID
	 * @return string
	 */
	//private function createUID() {
	//	return substr(md5(rand().time()),0,10);
	//}

	/*
	 * @brief checks is the UID is already in use for another event
	 * @param string $uid uid to check
	 * @return boolean
	 */
	//private function isUIDAvailable($uid) {
	//
	//}

	
	private function parseVCard(array $importInfo, $vcard){
		
		
		
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
			 
			 if($aOrg[0] === $vcard->FN){
			 	$vcard->{'X-ABSHOWAS'} = 'COMPANY';
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
			 //$addressDefArray=array('0'=>'','1'=>'','2'=>'street','3'=>'city','4'=>'state','5'=>'postalcode','6'=>'country');
			 $saveAdr = $importInfo['ADR'][0]['value'];	
			 
			 $prepareAddrStr = $saveAdr[2].','.$saveAdr[5].','.$saveAdr[3].','.$saveAdr[6];
			 
			 $getLonLat = $this->getLonLatFromAddress($prepareAddrStr);
			 
			 if($getLonLat!==false){
			 	$vcard->GEO = $getLonLat['lat'].';'.$getLonLat['lon'];
			 }
			 
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
	/**
	 * @NoAdminRequired
	 * 
	 * @param $location string with addressdata street postal city country
	 */
	private function getLonLatFromAddress($location){
		 
		 $name = urlencode($location);

		$renderUrl='http://nominatim.openstreetmap.org/search?format=json&q='.$name.'&limit=1&addressdetails=0&polygon=0';
		
		$locationInfo=$this->getLocationInfo($renderUrl,false);
		
		    if($locationInfo){
		    	$GeoInfo =json_decode($locationInfo);
				$lat = (isset($GeoInfo[0]->lat) ? $GeoInfo[0]->lat : '');
				$lon =  (isset($GeoInfo[0]->lat) ? $GeoInfo[0]->lon : '');
				
				$result = array('lon'=>$lon,'lat'=>$lat);
				
				
				return $result;
			}else{
				return false;
			}
	}
	
	private function getLocationInfo($url, $userAgent = true) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, 0);
    	curl_setopt($ch, CURLOPT_TIMEOUT, 900); 
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		if ($userAgent) {
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.1.2) Gecko/20090729 Firefox/3.5.2 GTB5');
		}
		curl_setopt($ch, CURLOPT_URL, $url);
		$tmp = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//\OCP\Util::writeLog('pinit','HTTPCODE:'.$httpCode,\OCP\Util::DEBUG);
		if ($httpCode == 404) {
			return false;
		} else {
			if ($tmp != false) {
				return $tmp;
			}
		}

	}
	
	/*
	 * @brief checks if an event already exists in the user's calendars
	 * @param integer $insertid id of the new object
	 * @return boolean
	 */
	private function isDuplicate($insertid) {
		$newobject = VCard::find($insertid);
	
		$stmt = \OCP\DB::prepare('SELECT COUNT(*) AS `COUNTING` FROM `'.App::ContactsTable.'` `CC`
								 INNER JOIN `'.App::AddrBookTable.'` `CA` ON `CC`.`addressbookid`=`CA`.`id`
								 WHERE  `CC`.`fullname`= ? AND `CC`.`carddata`=? AND `CC`.`component`=? AND `CA`.`id` = ? AND `CA`.`userid` = ?');
		$result = $stmt->execute(array($newobject['fullname'],$newobject['carddata'], 'VCARD', $this->id, $this->userid));
		$result = $result->fetchRow();
		
		
		if($result['COUNTING'] >= 2) {
			return true;
		}
		return false;
	}

	/*
	 * @brief updates the progress var
	 * @param integer $percentage
	 * @return boolean
	 */
	private function updateProgress($percentage) {
		$this->progress = $percentage;
		if($this->cacheprogress) {
			\OC::$server->getCache()->set($this->progresskey, $this->progress, 300);
		}
		return true;
	}

	

	
}
