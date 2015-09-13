<?php

namespace OCA\ContactsPlus;
use Sabre\VObject;

App::$appname = 'contactsplus';
App::$l10n = \OC::$server->getL10N('contactsplus');

class App{
	
	public static $l10n;
	
	public static $appname;
	
	const THUMBNAIL_PREFIX = 'kontakte-photo-';
	const THUMBNAIL_SIZE = 28;
	const ContactsTable='*PREFIX*conplus_cards';
	const AddrBookTable='*PREFIX*conplus_addressbooks';
	const ContactsProbTable='*PREFIX*conplus_cards_properties';
	const SHAREADDRESSBOOK = 'cpladdrbook';
	const SHAREADDRESSBOOKPREFIX = '';
	const SHARECONTACT = 'cplcontact';
	const SHARECONTACTPREFIX = '';
	
	/**
	 * @brief categories of the user
	 */
	protected static $categories = null;
		/**
	 * Properties there can be more than one of.
	 */
	public static $multi_properties = array('EMAIL', 'TEL', 'IMPP', 'ADR', 'URL','CLOUD');

	/**
	 * Properties to index.
	 */
	 
	 //lat:lon GEO:37.386013;-122.082932
	 
	public static $index_properties = array('BDAY', 'UID', 'N', 'FN', 'TITLE', 'ROLE', 'NOTE', 'NICKNAME', 'ORG', 'CATEGORIES', 'EMAIL', 'TEL', 'IMPP', 'ADR', 'URL', 'GEO', 'PHOTO', 'CLOUD');
	
	public static function searchProperties($searchquery){
		
		
		$ids = AddressBook::activeIds(\OCP\USER::getUser());
		
		$id_sql = join(',', array_fill(0, count($ids), '?'));
		
		$SQL="SELECT  `c`.`id`,`c`.`fullname`,`cp`.`name`,`cp`.`value` FROM `".self::ContactsTable."` c
		           LEFT JOIN `".self::ContactsProbTable."` cp ON  `c`.`id`=`cp`.`contactid`
		           WHERE  `c`.`addressbookid` IN (".$id_sql.")  AND `cp`.`value` LIKE '%".addslashes($searchquery)."%' AND `c`.`component`='VCARD' GROUP BY `c`.`id`";
				
		 $stmt = \OCP\DB::prepare($SQL);
		 //array_push($ids,$searchquery);
		$result = $stmt->execute($ids);
		$cards = array();
		if(!is_null($result)) {
			while( $row = $result->fetchRow()) {
				//if($row[''])
				$cards[] = $row;
			}
		}

		return $cards;
		
	}
	
	 public static function checkGroupRightsForPrincipal($uid){
	   	   $appConfig = \OC::$server->getAppConfig();
	   	   $isEnabled=$appConfig->getValue(self::$appname,'enabled');
		   $bEnabled=false;
		   if ($isEnabled === 'yes') {
		   	   $bEnabled=true;
		   } 
		   else if ($isEnabled === 'no') {
		   	  $bEnabled=false;
		   }
		   else if ($isEnabled !== 'no') {
		   	   $groups = json_decode($isEnabled);
			   if (is_array($groups)) {
					foreach ($groups as $group) {
						if (\OC_Group::inGroup($uid, $group)) {
							 $bEnabled=true;
							break;
						}
					}
			   }
		   }
		   if($bEnabled==false){
		   	 throw new \Sabre\DAV\Exception\Forbidden();		
		   	 return false;
		   }else return true;
	   
   }

	public static function getAddressbookSharees(){
    	
		$SQL='SELECT item_source,share_with,share_type,permissions FROM `*PREFIX*share` WHERE `uid_owner` = ? AND `item_type` = ?';
		$stmt = \OCP\DB::prepare($SQL);
		$result = $stmt->execute(array(\OCP\User::getUser(),self::SHAREADDRESSBOOK));
		$aSharees = '';
		$shareTypeDescr='';
		while( $row = $result->fetchRow()) {
			$shareWith='';
			$shareIcon='shared.svg';	
			if($row['share_with'] && $row['share_type'] != 3 ) {$shareWith=': '.$row['share_with'];}
			if($row['share_with'] && $row['share_type'] == 3 ) {$shareWith=': password protected ';}
			$shareTypeDescr.=self::shareTypeDescription($row['share_type']).' '.$shareWith.' ('.self::permissionReader($row['permissions']).")<br>";
			$aSharees[$row['item_source']]=array('myShare'=>1,'shareTypeDescr'=>$shareTypeDescr,'shareTypeIcon' => $shareIcon);
		}
		
		if(is_array($aSharees)) return $aSharees;
		else return false;
    }

	public static function shareTypeDescription($ShareType){
		$ShareTypeDescr='';	
		if($ShareType==0) $ShareTypeDescr = App::$l10n->t('user');
		if($ShareType==1) $ShareTypeDescr = App::$l10n->t('group');
		if($ShareType==3) $ShareTypeDescr = 'By Link';
		
		return $ShareTypeDescr;
	}
	
	public static function permissionReader($iPermission){
			
			$l = App::$l10n;
			
			
			$aPermissionArray=array(
			   16 =>(string) $l->t('share'),
			   8 => (string)$l->t('delete'),
			   4 => (string)$l->t('create'),
			   2 =>(string) $l->t('update'),
			   1 =>(string) $l->t('readonly')
			);
			
			if($iPermission==1) return (string) $l->t('readonly');
			if($iPermission==31) return (string) $l->t('full access');
			
			$outPutPerm='';
			foreach($aPermissionArray as $key => $val){
				if($iPermission>= $key){
					if($outPutPerm=='') $outPutPerm.=$val;
					else $outPutPerm.=', '.$val;
					$iPermission-=$key;
				}
			}
			return $outPutPerm;
		
	}
	
	public static function getIosGroups(){
		   $sql = "SELECT `id`, `fullname`, `addressbookid`, `carddata`  FROM `".self::ContactsTable."` WHERE `component` = 'GROUP' ORDER BY `fullname`";
		   $stmt = \OCP\DB::prepare($sql);
		   $result = $stmt->execute();
		   $groups = array();
		  if(!is_null($result)) {
			while( $row = $result->fetchRow()) {
				
				$groups[$row['fullname']]= array('name' => $row['fullname'], 'id' => $row['id'], 'color' => '#ccc');
				//\OCP\Util::writeLog(self::$appname,'IOS-GROUPS'. $row['fullname'], \OCP\Util::DEBUG);
			}
		}

		return $groups;
	}
	public static function getIosSingleGroup($sFullname){
		   $sql = "SELECT `id`, `fullname`, `addressbookid`, `carddata`, `lastmodified`  FROM `".self::ContactsTable."` WHERE `component` = 'GROUP' AND `fullname` = ?";
		   $stmt = \OCP\DB::prepare($sql);
		   $result = $stmt->execute(array($sFullname));
		 
		   if(!is_null($result)) {
			  $row = $result->fetchRow(); 
			  return $row;
		   }else{
		   	return false;
		   }
		

		
	}
	
	public static function getGroups() {
		$tagMgr = \OC::$server->getTagManager()->load(self::$appname);
		$tags = $tagMgr->getTags();
		foreach($tags as &$tag) {
			try {
				
				
			$ids = $tagMgr->getIdsForTag($tag['name']);
			\OCP\Util::writeLog(self::$appname,'ID->NAME'. $tag['name'].':'.count($ids), \OCP\Util::DEBUG);
			foreach($ids as $key => $val){
				\OCP\Util::writeLog(self::$appname,'ID->NAME'. $tag['name'].':'.$key.':'.$val, \OCP\Util::DEBUG);
			}
			
			$tag['contacts'] = $ids;
			} catch(\Exception $e) {
				//$this->api->log(__METHOD__ . ' ' . $e->getMessage());
			}
		}

		//$favorites = $tagMgr->getFavorites();

		$groups = array(
			'categories' => $tags,
			'shared' => \OCP\Share::getItemsSharedWith(self::SHAREADDRESSBOOK, \OCA\ContactsPlus\Share\Backend\Addressbook::FORMAT_ADDRESSBOOKS),
			'lastgroup' => \OCP\Config::getUserValue(\OCP\User::getUser(), self::$appname, 'lastgroup', 'all'),
			'sortorder' => \OCP\Config::getUserValue(\OCP\User::getUser(), self::$appname, 'sortorder', ''),
			);

		return $groups;
	}
	
	 /*
	 * @brief generates the text color for the calendar
	 * @param string $calendarcolor rgb calendar color code in hex format (with or without the leading #)
	 * (this function doesn't pay attention on the alpha value of rgba color codes)
	 * @return boolean
	 */
	public static function generateTextColor($calendarcolor) {
		if(substr_count($calendarcolor, '#') == 1) {
			$calendarcolor = substr($calendarcolor,1);
		}
		$red = hexdec(substr($calendarcolor,0,2));
		$green = hexdec(substr($calendarcolor,2,2));
		$blue = hexdec(substr($calendarcolor,4,2));
		//recommendation by W3C
		$computation = ((($red * 299) + ($green * 587) + ($blue * 114)) / 1000);
		return ($computation > 130)?'#000000':'#FAFAFA';
	}
	
	 /**
     * genColorCodeFromText method
     *
     * Outputs a color (#000000) based Text input
     *
     * (https://gist.github.com/mrkmg/1607621/raw/241f0a93e9d25c3dd963eba6d606089acfa63521/genColorCodeFromText.php)
     *
     * @param String $text of text
     * @param Integer $min_brightness: between 0 and 100
     * @param Integer $spec: between 2-10, determines how unique each color will be
     * @return string $output
	  * 
	  */
	  
	 public static function genColorCodeFromText($text, $min_brightness = 100, $spec = 10){
        // Check inputs
        if(!is_int($min_brightness)) throw new Exception("$min_brightness is not an integer");
        if(!is_int($spec)) throw new Exception("$spec is not an integer");
        if($spec < 2 or $spec > 10) throw new Exception("$spec is out of range");
        if($min_brightness < 0 or $min_brightness > 255) throw new Exception("$min_brightness is out of range");

        $hash = md5($text);  //Gen hash of text
        $colors = array();
        for($i=0; $i<3; $i++) {
            //convert hash into 3 decimal values between 0 and 255
            $colors[$i] = max(array(round(((hexdec(substr($hash, $spec * $i, $spec))) / hexdec(str_pad('', $spec, 'F'))) * 255), $min_brightness));
        }

        if($min_brightness > 0) {
            while(array_sum($colors) / 3 < $min_brightness) {
                for($i=0; $i<3; $i++) {
                    //increase each color by 10
                    $colors[$i] += 10;
                }
            }
        }

        $output = '';
        for($i=0; $i<3; $i++) {
            //convert each color to hex and append to output
            $output .= str_pad(dechex($colors[$i]), 2, 0, STR_PAD_LEFT);
        }

        return '#'.$output;
    }

    public static function loadTags(){
		$existCats=self::getCategoryOptions();
		$tag=array();
		foreach($existCats as $groupInfo){
			$backgroundColor=	self::genColorCodeFromText(trim($groupInfo['name']));
			$tag[]=array(
				'id'=>$groupInfo['id'],
				'name'=>$groupInfo['name'],
				'bgcolor' =>$backgroundColor,
				'color' => self::generateTextColor($backgroundColor),
			);
		}
		
		//test
		
		$favorites = \OC::$server -> getTagManager() -> load(self::$appname)->getFavorites();	
		if(count($favorites)>0){
			$tag[]=array(
				'id'=>'fav',
				'name'=>'Favoriten',
				'bgcolor' =>'#ccc',
				'color' => '#000',
			);
		}	
		$tagsReturn['tagslist']=$tag;
		
		
						  
		return $tagsReturn;
	}
	
	
	
	/**
	 * @brief returns the vcategories object of the user
	 * @return (object) $vcategories
	 */
	public static function getVCategories() {
		if (is_null(self::$categories)) {
			$categories = \OC::$server -> getTagManager() -> load(self::$appname);
			if ($categories -> isEmpty(self::$appname)) {
				self::scanCategories();
			}
			self::$categories = \OC::$server -> getTagManager() -> load(self::$appname, self::getDefaultCategories());
		}
		return self::$categories;
		
	}
	

	
	/**
	 * @brief returns the categories of the vcategories object
	 * @return (array) $categories
	 */
	public static function getCategoryOptions() {
		
		$getNames = function($tag) {
				
			return $tag;
		};
		$categories = self::getVCategories() -> getTags();
		$categories = array_map($getNames, $categories);
		return $categories;
	}
	
	/**
	 * @brief returns the default categories of ownCloud
	 * @return (array) $categories
	 */
	public static function getDefaultCategories() {
		if(\OCP\Config::getUserValue(\OCP\User::getUser(), self::$appname, 'categories_scanned', 'no') === 'yes') {
			return array();
		}
		return array(
			(string)self::$l10n->t('Friends'),
			(string)self::$l10n->t('Family'),
			(string)self::$l10n->t('Work'),
			(string)self::$l10n->t('Other'),
		);
	}
	
	public static function getCounterGroups() {
			
		$stmt = \OCP\DB::prepare('SELECT value FROM `'.self::ContactsProbTable.'` WHERE `userid` = ? AND `name`= ? ');
		$result = $stmt -> execute(array(\OCP\USER::getUser(),'CATEGORIES'));
		$categoriesobjects='';
		while ($row = $result -> fetchRow()) {

			$categoriesobjects[] = $row;
		}
		
		if(is_array($categoriesobjects)){
				$aGroups='';
			  
			foreach($categoriesobjects as $catInfo){
				$temp=explode(',',$catInfo['value']);
				if(count($temp)>0)	{
					foreach($temp as $val){
						 if ( isset($aGroups[$val] ) ) {	
						 	$aGroups[$val]+=1;	
						 }else{
						 	$aGroups[$val]=1;
						 }
						
					}
				}
				
			}
		}
		
		$favorites = \OC::$server -> getTagManager() -> load(self::$appname)->getFavorites();	
		$aGroups['favo'] = 0;
		if(count($favorites) > 0){
			$aGroups['favo']=count($favorites);
		}

		if(is_array($aGroups)){
			 return $aGroups;
		}else return false;
			
		  
	}
	/**
	 * scan vcards for categories.
	 * @param $vccontacts VCards to scan. null to check all vcards for the current user.
	 */
	public static function scanCategories($vccontacts = null) {
		if(\OCP\Config::getUserValue(\OCP\User::getUser(), self::$appname, 'categories_scanned', 'no') === 'yes') {
			return;
		}
		if (is_null($vccontacts)) {
			$vcaddressbooks = Addressbook::all(\OCP\USER::getUser());
			if(count($vcaddressbooks) > 0) {
				$vcaddressbookids = array();
				foreach($vcaddressbooks as $vcaddressbook) {
					if($vcaddressbook['userid'] === \OCP\User::getUser()) {
						$vcaddressbookids[] = $vcaddressbook['id'];
					}
				}
				$start = 0;
				$batchsize = 10;
				
				$categories = \OC::$server->getTagManager()->load(self::$appname);
				
				$getName = function($tag) {
					return $tag['name'];
				};
				
				$tags = array_map($getName, $categories->getTags());
			    $categories->delete($tags);
				
				while($vccontacts =VCard::all($vcaddressbookids, $start, $batchsize)) {
					$cards = array();
					foreach($vccontacts as $vccontact) {
						$cards[] = array($vccontact['id'], $vccontact['carddata']);
					}
					\OCP\Util::writeLog(self::$appname,
						__CLASS__.'::'.__METHOD__
							.', scanning: '.$batchsize.' starting from '.$start,
						\OCP\Util::DEBUG);
					// only reset on first batch.
					/*
					$categories->rescan($cards,
						true,
						($start == 0 ? true : false));*/
					$start += $batchsize;
				}
			}
		}
		\OCP\Config::setUserValue(\OCP\User::getUser(), self::$appname, 'categories_scanned', 'yes');
	}

/**
	 * check VEvent for new categories.
	 * @see \OC_VCategories::loadFromVObject
	 */
	public static function loadCategoriesFromVCard($id, VObject\Component $vcard) {
		
		if (isset($vcard -> CATEGORIES)) {
			$values = explode(',', (string)$vcard -> CATEGORIES);
			$values = array_map('trim', $values);
			
			self::getVCategories() -> addMultiple($values, true, $id);
		}
	}

	/**
	 * @return types for property $prop
	 */
	public static function getAdditionalFields() {
		$l = self::$l10n;
		return array(
			'gender' => $l->t('Title'),
			'nickname' => $l->t('Nickname'),
			'position' =>  $l->t('Position'),
			'department' =>  $l->t('Department'),
			'bday' =>  $l->t('Birthday'),
	    );
	}
	
	/**
	 * @return types for property $prop
	 */
	public static function getTypesOfProperty($prop) {
		$l = self::$l10n;
		switch($prop) {
			case 'ADR':
			case 'IMPP':
				return array(
					'WORK' => $l->t('Work'),
					'HOME' => $l->t('Home'),
					'OTHER' =>  $l->t('Other'),
				);
			case 'TEL':
				return array(
					'HOME'  =>  $l->t('Home'),
					'CELL'  =>  $l->t('Mobile'),
					'WORK'  =>  $l->t('Work'),
					'TEXT'  =>  $l->t('Text'),
					'VOICE' =>  $l->t('Voice'),
					'MSG'   =>  $l->t('Message'),
					'WORK_FAX'   =>  $l->t('Fax').' '.$l->t('Work'),
					'HOME_FAX'   =>  $l->t('Fax').' '.$l->t('Home'),
					'OTHER_FAX'   =>  $l->t('Fax').' '.$l->t('Other'),
					'FAX'   =>  $l->t('Fax'),
					'VIDEO' =>  $l->t('Video'),
					'PAGER' =>  $l->t('Pager'),
					'OTHER' =>  $l->t('Other'),
				);
			case 'EMAIL':
			case 'URL':	
				return array(
					'WORK' => $l->t('Work'),
					'HOME' => $l->t('Home'),
					'INTERNET' => $l->t('Internet'),
					'OTHER' =>  $l->t('Other'),
				);
			case 'CLOUD':
				return array(
					'HOME' => (string)$l->t('Home'),
					'WORK' => (string)$l->t('Work'),
					'OTHER' =>  (string)$l->t('Other'),
				);	
		}
	}
	
	/**
	 * Get options for IMPP properties
	 * @param string $im
	 * @return array of vcard prop => label
	 */
	public static function getIMOptions($im = null) {
		$l10n = self::$l10n;
		$ims = array(
				'jabber' => array(
					'displayname' => (string)$l10n->t('Jabber'),
					'xname' => 'X-JABBER',
					'protocol' => 'xmpp',
				),
				'sip' => array(
					'displayname' => (string)$l10n->t('Internet call'),
					'xname' => 'X-SIP',
					'protocol' => 'sip',
				),
				'aim' => array(
					'displayname' => (string)$l10n->t('AIM'),
					'xname' => 'X-AIM',
					'protocol' => 'aim',
				),
				'msn' => array(
					'displayname' => (string)$l10n->t('MSN'),
					'xname' => 'X-MSN',
					'protocol' => 'msn',
				),
				'twitter' => array(
					'displayname' => (string)$l10n->t('Twitter'),
					'xname' => 'X-TWITTER',
					'protocol' => 'twitter',
				),
				'googletalk' => array(
					'displayname' => (string)$l10n->t('GoogleTalk'),
					'xname' => null,
					'protocol' => 'xmpp',
				),
				'facebook' => array(
					'displayname' => (string)$l10n->t('Facebook'),
					'xname' => null,
					'protocol' => 'xmpp',
				),
				'xmpp' => array(
					'displayname' => (string)$l10n->t('XMPP'),
					'xname' => null,
					'protocol' => 'xmpp',
				),
				'icq' => array(
					'displayname' => (string)$l10n->t('ICQ'),
					'xname' => 'X-ICQ',
					'protocol' => 'icq',
				),
				'yahoo' => array(
					'displayname' => (string)$l10n->t('Yahoo'),
					'xname' => 'X-YAHOO',
					'protocol' => 'ymsgr',
				),
				'skype' => array(
					'displayname' => (string)$l10n->t('Skype'),
					'xname' => 'X-SKYPE',
					'protocol' => 'skype',
				),
				'qq' => array(
					'displayname' => (string)$l10n->t('QQ'),
					'xname' => 'X-SKYPE',
					'protocol' => 'x-apple',
				),
				'gadugadu' => array(
					'displayname' => (string)$l10n->t('GaduGadu'),
					'xname' => 'X-SKYPE',
					'protocol' => 'x-apple',
				),
				'owncloud-handle' => array(
				    'displayname' => (string)$l10n->t('ownCloud'),
				    'xname' => null,
				    'protocol' => 'x-owncloud-handle'
				),
		);
		if(is_null($im)) {
			return $ims;
		} else {
			$ims['ymsgr'] = $ims['yahoo'];
			$ims['gtalk'] = $ims['googletalk'];
			return isset($ims[$im]) ? $ims[$im] : null;
		}
	}
	
	/**
	 * @brief Get the last modification time.
	 * @param OC_VObject|Sabre\VObject\Component|integer|null $contact
	 * @returns DateTime | null
	 */
	public static function lastModified($contact = null) {
		if(is_null($contact)) {
			$addressBooks = Addressbook::all(\OCP\User::getUser());
			$lastModified = 0;
			foreach($addressBooks as $addressBook) {
				if(isset($addressBook['ctag']) and (int)$addressBook['ctag'] > $lastModified) {
					$lastModified = $addressBook['ctag'];
				}
			}
			return new \DateTime('@' . $lastModified);
		} else if(is_numeric($contact)) {
			$card = VCard::find($contact, array('lastmodified'));
			return ($card ? new \DateTime('@' . $card['lastmodified']) : null);
		} elseif($contact instanceof VObject\Component\VCard || $contact instanceof VObject\Component) {
			return isset($contact->REV) 
				? \DateTime::createFromFormat(\DateTime::W3C, $contact->REV)
				: null;
		}
	}
	 /**
	 * @brief Gets the VCard as a \Sabre\VObject\Component
	 * @param integer $id
	 * @returns \Sabre\VObject\Component|null The card or null if the card could not be parsed.
	 */
	public static function getContactVCard($id) {
		$card = null;
		$vcard = null;
		try {
			$card = VCard::find($id);
		} catch(\Exception $e) {
			return null;
		}
      
		if(!$card) {
			return null;
		}
		try {
			$vcard =VObject\Reader::read($card['carddata']);
			
		} catch(\Exception $e) {
			\OCP\Util::writeLog(self::$appname, __METHOD__.', exception: ' . $e->getMessage(), \OCP\Util::ERROR);
			\OCP\Util::writeLog(self::$appname, __METHOD__.', id: ' . $id, \OCP\Util::DEBUG);
			return null;
		}

		if (!is_null($vcard) && !isset($vcard->REV)) {
			$rev = new \DateTime('@'.$card['lastmodified']);
			$vcard->REV = $rev->format(\DateTime::W3C);
		}
		
		return $vcard;
	}
	
	
	public static function cacheThumbnail($id, \OC_Image $image = null, $remove = false, $update = false) {
		$key = self::THUMBNAIL_PREFIX . $id;
		if(\OC::$server->getCache()->hasKey($key) && $image === null && $remove === false && $update === false) {
			return \OC::$server->getCache()->get($key);
		}
		if($remove) {
			\OC::$server->getCache()->remove($key);
			if(!$update) {
				return false;
			}
		}
		if(is_null($image)) {
			$vcard = self::getContactVCard($id);
           
			// invalid vcard
			if(is_null($vcard)) {
				\OCP\Util::writeLog(self::$appname,
					__METHOD__.' The VCard for ID ' . $id . ' is not RFC compatible',
					\OCP\Util::ERROR);
				return false;
			}
			$image = new \OCP\Image();
			if(!isset($vcard->PHOTO)) {
				return false;
			}
			if(!$image->loadFromBase64((string)$vcard->PHOTO)) {
				return false;
			}
		}
		if(!$image->centerCrop()) {
			\OCP\Util::writeLog(self::$appname,
				'thumbnail.php. Couldn\'t crop thumbnail for ID ' . $id,
				\OCP\Util::ERROR);
			return false;
		}
		if(!$image->resize(self::THUMBNAIL_SIZE)) {
			\OCP\Util::writeLog(self::$appname,
				'thumbnail.php. Couldn\'t resize thumbnail for ID ' . $id,
				\OCP\Util::ERROR);
			return false;
		}
		 // Cache for around a month
		\OC::$server->getCache()->set($key, $image->data(), 3000000);
		\OCP\Util::writeLog(self::$appname, 'Caching ' . $id, \OCP\Util::DEBUG);
		return \OC::$server->getCache()->get($key);
	}
	
	
	public static function updateDBProperties($contactid, $vcard = null) {
		$stmt = \OCP\DB::prepare('DELETE FROM `'.self::ContactsProbTable.'` WHERE `contactid` = ?');
		try {
			$stmt->execute(array($contactid));
		} catch(\Exception $e) {
			\OCP\Util::writeLog(self::$appname, __METHOD__.
				', exception: ' . $e->getMessage(), \OCP\Util::ERROR);
			\OCP\Util::writeLog(self::$appname, __METHOD__.', id: '
				. $id, \OCP\Util::DEBUG);
			throw new \Exception(
				App::$l10n->t(
					'There was an error deleting properties for this contact.'
				)
			);
		}

		if(is_null($vcard)) {
			return;
		}

		$stmt = \OCP\DB::prepare( 'INSERT INTO `'.self::ContactsProbTable.'` '
			. '(`userid`, `contactid`,`name`,`value`,`preferred`) VALUES(?,?,?,?,?)' );
		foreach($vcard->children as $property) {
			if(!in_array($property->name, self::$index_properties)) {
				continue;
			}
			$preferred = 0;
			foreach($property->parameters as $parameter) {
				if($parameter->name == 'TYPE' && strtoupper($parameter->getValue()) == 'PREF') {
					$preferred = 1;
					break;
				}
			}
			try {
				$result = $stmt->execute(
					array(
						\OCP\User::getUser(), 
						$contactid, 
						$property->name, 
						$property->getValue(), 
						$preferred,
					)
				);
				if (\OCP\DB::isError($result)) {
					\OCP\Util::writeLog(self::$appname, __METHOD__. 'DB error: ' 
						. \OCP\DB::getErrorMessage($result), \OCP\Util::ERROR);
					return false;
				}
			} catch(\Exception $e) {
				\OCP\Util::writeLog(self::$appname, __METHOD__.', exception: '.$e->getMessage(), \OCP\Util::ERROR);
				return false;
			}
		}
	}

    public static function renderSingleCardParameter($aCon,$Name,$depth=1,$pref=0,$bTrans=true){
    		
    	if(isset($aCon[$Name]) && count($aCon[$Name])>0){
				
			$sReturn='';	
			$count=1;	
			if($bTrans) $trans=self::getTypesOfProperty($Name);	
			
			foreach($aCon[$Name] as $Info){
				$sReturnTypeOutput='';
				
				if((array_key_exists('TYPE',$Info['parameters']) || array_key_exists('X-SERVICE-TYPE',$Info['parameters'])) &&  array_key_exists('PREF',$Info['parameters'])){
					//\OCP\Util::writeLog(self::$appname,'Tel PREF: '.$Info['parameters']['PREF'], \OCP\Util::DEBUG);
					if(array_key_exists('TYPE',$Info['parameters'])){
						foreach($Info['parameters']['TYPE'] as $TypeInfo){
							 $TypeInfo=strtoupper($TypeInfo);	
							
							 if($TypeInfo!='PREF' && $TypeInfo!='INTERNET' && $TypeInfo!='VOICE'){
						          		
						          if($bTrans) {
						          	 $sTransLationName = $TypeInfo;
									  if(isset($trans[$TypeInfo])){
									  	$sTransLationName=$trans[$TypeInfo];	
									  }
						          	$sReturnTypeOutput.='<b>'.$sTransLationName.'</b> ';
								  }
								  else $sReturnTypeOutput.='<b>,'.$TypeInfo.'</b> ';
							 }
						   
						}
					}else{
						$IMTYPE = self::getIMOptions();	
						$sReturnTypeOutput = $IMTYPE[$Info['parameters']['X-SERVICE-TYPE']]['displayname'].' ';
					} 
					$sReturn['descr'] = $sReturnTypeOutput;
					$sReturn['value'] = $Info['value'];
					
				   return $sReturn;
				}
				
				
				
			}
			
			
		}else return false;
    }
   public static function prepareSingleCardParameter($aCon,$Name,$depth=1,$pref=0,$bTrans=true){
    		
    	if(isset($aCon[$Name]) && count($aCon[$Name])>0){
				
			$sReturn='';	
			$count=1;	
			if($bTrans) $trans=self::getTypesOfProperty($Name);	
			
			foreach($aCon[$Name] as $Info){
				$sReturnTypeOutput='';
				if(count($Info['parameters']['TYPE'])>0){
					
					foreach($Info['parameters']['TYPE'] as $TypeInfo){
						 $TypeInfo=strtoupper($TypeInfo);	
						
						 if($TypeInfo!='PREF' && $TypeInfo!='INTERNET'){	
					          if($bTrans) $sReturnTypeOutput.='<b>'.$trans[$TypeInfo].'</b> ';
							  else $sReturnTypeOutput.='<b>'.$TypeInfo.'</b> ';
						 }
					   
					}
				}
				if($sReturn=='') $sReturn=$sReturnTypeOutput.$Info['value'];
				else $sReturn.='<br>'.$sReturnTypeOutput.$Info['value'];
				
				if($depth==$count){
					return $sReturn;
				}else{
					$count++;
				}
			}
			
			//return $sReturn;
			
		}else return false;
    }

   public static function renderOutput($addrbookId, $grpId='all'){
   		       
			   
				if($grpId === 'all'){
					$contacts_alphabet = VCard::all($addrbookId,null,null,array(),true);
				}elseif($grpId === 'fav'){
					$contacts_alphabet = VCard::allByFavourite();
				}else{
					$contacts_alphabet = VCard::getCardsByGroups($addrbookId,$grpId,null,null,true);
				}
				$addressBookPerm=Addressbook::find($addrbookId);
				//Favourites
				$favorites = \OC::$server -> getTagManager() -> load(self::$appname)->getFavorites();
				
				$aFavourites=array();
				if(is_array($favorites)){
					foreach($favorites as $fav){
						$aFavourites[$fav]=1;
					}
				}
				 	
				$contacts = array();
				$counterAlle=0;
				$aLetter = [];
				
				if($contacts_alphabet) {
					$oldLetter = '';
					foreach($contacts_alphabet as $contact) {
						try {
							$vcard = VObject\Reader::read($contact['carddata']);
							
							$details = VCard::structureContact($vcard);
							$imgBuild=false;
							if($vcard->PHOTO){
								$imgBuild=true;
							}
							$sLetter=strtoupper(mb_substr($contact['sortFullname'],0,1,"UTF-8"));
							
							
							if($sLetter !== $oldLetter){
								$aLetter[]=$sLetter;
							}
							
							//\OCP\Util::writeLog(self::$appname,'LETTER: '. $sLetter.':'.$contact['fullname'], \OCP\Util::DEBUG);
							$contacts[$sLetter][] = array(
									'id' => $contact['id'],
									'aid' => $contact['addressbookid'],
									'letter'=>$sLetter,
									'photo' => $imgBuild,
									'component'=> $contact['component'],
									'fullname' =>  $contact['fullname'],
									'surename' =>  $contact['surename'],
									'lastname' =>  $contact['lastname'],
									'organization' =>  $contact['organization'],
									'bcompany' =>  $contact['bcompany'],
									'data' => $details,
								);
								
								$oldLetter = $sLetter;
								$counterAlle++;
						} catch (Exception $e) {
							continue;
						}
					}
				
				
				  $oldLetter='';
				  $buildingOutput='<ul id="contacts-list">';
				  foreach($aLetter as $letterInfo){
				  	
					    $bFound = false;
					   $ContactsOutput = '';
					   if(isset($contacts[$letterInfo])){
							  	foreach($contacts[$letterInfo] as $contactInfo){
							  		 $bFound = true;	
							  		 $CONTACTDATA=$contactInfo['data'];	
									 $prepareOutput = self::renderSingleCard($CONTACTDATA, $contactInfo, $addressBookPerm,$aFavourites);
								
									 $ContactsOutput.='<li class="contactsrow visible">'.$prepareOutput.'</li>';
							  	}
						  }
						if($bFound === true){
							$buildingOutput.='<li class="letter" data-scroll="'.$letterInfo.'"><span>'.$letterInfo.'</span></li>'.$ContactsOutput;	
						}else{
							$buildingOutput.='<li class="letter hidden" data-scroll="'.$letterInfo.'"><span>'.$letterInfo.'</span></li>';
						}
				  }
				   $buildingOutput.='<li><span class="noitem hidden">'.(string)self::$l10n->t('No Cards found!').'</span></li>';
				   $buildingOutput.='</ul>';
				   }else{
				   	$buildingOutput='<ul id="contacts-list">';
					  foreach($aLetter as $letterInfo){
					  	$buildingOutput.='<li class="letter hidden" data-scroll="'.$letterInfo.'"><span>'.$letterInfo.'</span></li>';
					  }
					  $buildingOutput.='<li><span class="noitem">'.(string)self::$l10n->t('Add a new contact or import existing contacts from a file (VCF) per Drag & Drop.').' <i id="importAddrStart" title="'.(string)self::$l10n->t('Import addressbook per Drag & Drop').'" class="toolTip ioc ioc-upload"></i></span></li>';
					  $buildingOutput.='</ul>'; 
				   	
				   }
				  
					 				
   	   	 return $buildingOutput;
   	   
   }

   public static function renderSingleCard($CONTACTDATA,$contactInfo, $addressBookPerm, $aFavourites){
   		$catOutput = '';
		$searchCat='';
		$hiddenClass = 'hidden';
		if(isset($CONTACTDATA['CATEGORIES'][0]['value']) && count($CONTACTDATA['CATEGORIES'][0]['value'])>0){
			$hiddenClass ='';
			foreach($CONTACTDATA['CATEGORIES'] as $key => $catInfo){
				//\OCP\Util::writeLog(self::$appname,'CAT: '.$catInfo['value'], \OCP\Util::DEBUG);	
				if($key == 'value'){	
					$aCatInfo = explode(',',$catInfo['value']);	
					foreach($aCatInfo as $key => $val){
						$backgroundColor=	self::genColorCodeFromText(trim($val),80);
						$color=self::generateTextColor($backgroundColor);
						$catOutput.='<span class="colorgroup" data-category="'.$val.'"  style="background-color:'.$backgroundColor.';color:'.$color.';" title="'.$val.'">'.mb_substr($val, 0,1,"UTF-8").'</span> ';
						$searchCat.=' '.$val;
					}
				}
			}
		}
		
		$lon='';
		$lat='';
		if(isset($CONTACTDATA['GEO'][0])){
			$tempLatLon=explode(';',$CONTACTDATA['GEO'][0]['value']);	
			if($tempLatLon[0]!=''){
				$lat = $tempLatLon[0];
				$lon = $tempLatLon[1];
			}
		}
		
		$sEmailOutput = self::renderSingleCardParameter($CONTACTDATA,'EMAIL',0,1);
		$sEmailOutputHeader = '&nbsp;';
		if($sEmailOutput!=''){
			$sEmailOutputHeader='<a href="mailto:'.$sEmailOutput['value'].'">'.$sEmailOutput['value'].'</a> ';
			$sEmailOutput='<a href="mailto:'.$sEmailOutput['value'].'"><i class="ioc ioc-mail"></i></a> '.$sEmailOutput['descr'].'<a href="mailto:'.$sEmailOutput['value'].'">'.$sEmailOutput['value'].'</a>';
		}
		
		$sTelOutput = self::renderSingleCardParameter($CONTACTDATA,'TEL',0,1);
		$sTelOutputHeader = '&nbsp;';
		if($sTelOutput!=''){
			$sTelOutputHeader = $sTelOutput['value'];	
			$sTelOutput='<i class="ioc ioc-phone"></i> '.$sTelOutput['descr'].$sTelOutput['value'];
		}
		/*
		$sUrlOutput = self::renderSingleCardParameter($CONTACTDATA,'URL',0,1);
		if($sUrlOutput != ''){
			$sUrlOutput='<a href="'.$sEmailOutput['value'].'" target="_blank"><i class="ioc ioc-publiclink"></i></a> '.$sUrlOutput['descr'].'<a href="'.$sUrlOutput['value'].'" target="_blank">'.$sUrlOutput['value'].'</a>';
		}
		
		$sImppOutput = self::renderSingleCardParameter($CONTACTDATA,'IMPP',0,1);
		if($sImppOutput != ''){
			$sImppOutput='<i class="ioc ioc-users"></i> '.$sImppOutput['descr'].$sImppOutput['value'];
		}
		
		$sCloudOutput = self::renderSingleCardParameter($CONTACTDATA,'CLOUD',0,1);
		if($sCloudOutput != ''){
			$sCloudOutput='<i class="ioc ioc-upload-cloud"></i> '.$sCloudOutput['descr'].$sCloudOutput['value'];
		}*/
		
		$sAddrOutput='';
		 $addressDefArray=array('0'=>'','1'=>'','2'=>'street','3'=>'city','4'=>'state','5'=>'postalcode','6'=>'country');
		if(isset($CONTACTDATA['ADR'][0]['value']) && count($CONTACTDATA['ADR'][0]['value'])>0){
			foreach($CONTACTDATA['ADR'][0]['value'] as $key => $val){
				if($val!='') {
					$addrOutput[$addressDefArray[$key]]=$val;
				}
			}
			
			$sStreet =  isset($addrOutput['street'])?$addrOutput['street']:'';
			$sPostal =  isset($addrOutput['postalcode'])?$addrOutput['postalcode']:'';
			$sCity =  isset($addrOutput['city'])?$addrOutput['city']:'';
			$sState =  isset($addrOutput['state'])?$addrOutput['state']:'';
			$sCountry =  isset($addrOutput['country'])?$addrOutput['country']:'';
			
			$linkAddr=' <a target="_blank" href="http://open.mapquest.com/?q='.$sStreet.','.$sPostal.','.$sCity.','.$sState.','.$sCountry.'"><i style="font-size:18px;color:#999;" class="ioc ioc-search"></i></a>';
			
			$sAddrOutput='<i class="ioc ioc ioc-address"></i>';
			if($sStreet != ''){
				$sAddrOutput.= '<span class="addrStreet">'.$sStreet.$linkAddr.'</span>';
			}
			
			if($sPostal != ''){
				$sAddrOutput.= $sPostal;
			}
			if($sCity != ''){
				$sAddrOutput.= ' '.$sCity;
			}
			
			if($sState != ''){
				$sAddrOutput.= ' '.$sState;
			}
			if($sCountry != ''){
				$sAddrOutput.= ' ('.$sCountry.') ';
			}
			
			
			
			unset($addrOutput);
		}
	
		 $sNameOutput='';
		 $aDefNArray=array('0'=>'fname','1'=>'lname','2'=>'test','3'=>'title','4'=>'');
		if(isset($CONTACTDATA['N'][0]['value']) && count($CONTACTDATA['N'][0]['value'])>0){
			foreach($CONTACTDATA['N'][0]['value'] as $key => $val){
				if($val!=='') {	
					$aNameOutput[$aDefNArray[$key]]=$val;
					
				}
			}
			
			$sNameOutput=isset($aNameOutput['title'])?$aNameOutput['title'].' ':'';
			$sNameOutput.=isset($aNameOutput['lname'])?$aNameOutput['lname'].' ':'';
			$sNameOutput.=isset($aNameOutput['fname'])?$aNameOutput['fname'].' ':'';
			
			$lastname = isset($aNameOutput['fname'])?$aNameOutput['fname'].' ':'';
			$surname = isset($aNameOutput['lname'])?$aNameOutput['lname'].' ':'';
			
			unset($aNameOutput);
		}
		
		if(!$contactInfo['bcompany']){
			$sNameOutput = $contactInfo['organization'];
		}
		
		/*
		 $aDefOrgArray=array('0'=>'orgname','1'=>'abteilung');
		if(isset($CONTACTDATA['ORG'][0]['value']) && count($CONTACTDATA['ORG'][0]['value'])>0){
			foreach($CONTACTDATA['ORG'][0]['value'] as $key => $val){
				if($val!=='') {	
					$aNameOutput[$aDefOrgArray[$key]]=$val;
					
				}
			}
			
			$sNameOutput=isset($aNameOutput['orgname'])?$aNameOutput['orgname'].' ':'';
			$sNameOutput.=isset($aNameOutput['abteilung'])?' ('.$aNameOutput['abteilung'].') ':'';
			
			
			unset($aNameOutput);
		}*/
		
		//FIXME
		$l = App::$l10n;
		$favLink='<a class="favourite"><i class="ioc ioc-star" title="'.$l ->t('Add to favourite').'"></i></a>';
		if(array_key_exists($contactInfo['id'], $aFavourites)){
			$favLink='<a class="favourite"><i class="ioc ioc-star yellow" title="'.$l ->t('Delete from favourite').'"></i></a>';
		}

		$thumb=$favLink.'<i id="photo-id-'.$contactInfo['id'].'" class="nopic-row ioc ioc-user"></i>';
		$thumbHead=$favLink.'<i id="photo-small-id-'.$contactInfo['id'].'" class="nopic-row ioc ioc-user"></i>';
		if($contactInfo['photo']!=''){
			$thumb=$favLink.'<img id="photo-id-'.$contactInfo['id'].'" class="svg" src="'.\OC::$server->getURLGenerator()->linkToRoute('contactsplus.contacts.getContactPhoto',array('id' => $contactInfo['id'])).'"  />';
			$thumbHead=$favLink.'<img id="photo-small-id-'.$contactInfo['id'].'" class="svg" src="'.\OC::$server->getURLGenerator()->linkToRoute('contactsplus.contacts.getContactPhoto',array('id' => $contactInfo['id'])).'"  />';
	
		}
		/**ADDRESSBOOK PERMISSIONS**/
		$editLink='';
		if($addressBookPerm['permissions'] & \OCP\PERMISSION_UPDATE){
			$editLink='<a class="edit"><i class="ioc ioc-edit" title="'.$l ->t('Edit').'"></i></a>';
		}

		$delLink='';
		if($addressBookPerm['permissions'] & \OCP\PERMISSION_DELETE){
			$delLink='<a class="delete"><i class="ioc ioc-delete" title="'.$l ->t('Delete').'"></i></a>';
		}
		
		$DisplayName = $contactInfo['fullname'];
		
		
		/*<span class="url">'.$sUrlOutput.'</span>
			  <span class="impp">'.$sImppOutput.'</span>
			  <span class="clouding">'.$sCloudOutput.'</span>*/
		
		$prepareOutput='<span data-contactid="'.$contactInfo['id'].'" data-letter="'.$contactInfo['letter'].'" data-lat="'.$lat.'" data-lon="'.$lon.'" data-company="'.$contactInfo['bcompany'].'" class="container">
		 <span class="rowHeader">
		 	 <span class="head-picture">'.$thumbHead.'</span>
		 	 <span class="head-check"><input class="contact-select regular-checkbox" type="checkbox" value="'.$contactInfo['id'].'" id="chk-'.$contactInfo['id'].'" /><label data-conid="'.$contactInfo['id'].'" class="is-checkbox" for="chk-'.$contactInfo['id'].'"></label></span>
			 <span class="fullname" data-id="'.$contactInfo['id'].'"><a>'.strip_tags($DisplayName).'</a></span>
			
			 <span class="tel">'.$sTelOutputHeader.'</span>
			  <span class="email">'.$sEmailOutputHeader.'</span>
			  <span class="categories '.$hiddenClass.'">'.$catOutput.'</span>
			   <span class="option">'.$editLink.' '.$delLink.'</span>
		   </span>
		   <span class="rowBody" data-id="'.$contactInfo['id'].'">
			 <span class="picture">'.$thumb.'</span>
			 <span class="name">'.$sNameOutput.'</span>
			 
			 <span class="address">'.$sAddrOutput.'</span>
			  <span class="tel telsearch">'.$sTelOutput.'</span>
			  <span class="email emailsearch">'.$sEmailOutput.'</span>
			  <span class="hidden-category">'.$searchCat.'</span>
			  
		  </span>
		  <span>
		';
		
		return $prepareOutput;
   }
   public static function addingDummyContacts($iNum){
			$active_addressbooks = array();

			$active_addressbooks = Addressbook::active(\OCP\USER::getUser());
			
			$contacts_addressbook = array();
			$ids = array();
			foreach($active_addressbooks as $addressbook) {
				$ids[] = $addressbook['id'];
				
			}
            
			for($i=0; $i<$iNum; $i++){
				
				//$dummyName=self::generateFnDummy();
				$dummySure=self::generateFnDummy(10);	
				$dummyLast=self::generateFnDummy(10);	
					
				$uid = substr(md5(rand().time()), 0, 10);
				$aid = $ids[0];
				
				$vcard = new VObject\Component\VCard(array(
				     'N'   => array($dummySure,$dummyLast, '', '', ''),
				     'UID'=>$uid
				));
				$vcard->FN = $dummySure.' '.$dummyLast;
				$vcard->CATEGORIES = 'familie';
				$vcard->add('TEL', '+1 555 34567 456', array('type' => 'fax'));
				$vcard->add('TEL', '+1 555 34567 457', array('type' => 'voice','pref'=>1));
				$vcard->add('EMAIL', 'info@dummy.de', array('type' => 'work'));
				$vcard->add('EMAIL', 'sd@dummy.de', array('type' => 'private','pref'=>1));
				
				$id = VCard::add($aid, $vcard, null, true);
		}
	 	
		
	 } 
   
   public static function createVCardFromRequest($sRequest){
   	        	$uid = substr(md5(rand().time()), 0, 10);
				$appinfo = \OCP\App::getAppInfo(self::$appname);
			    $appversion = \OCP\App::getAppVersion(self::$appname);
			    $prodid = '-//ownCloud//NONSGML '.$appinfo['name'].' '.$appversion.'//EN';
				
				$vcard = new VObject\Component\VCard(array(
				    'PRODID'  => $prodid,
				     'VERSION'   => '3.0',
				     'UID'=>$uid
				));
		
		return self::updateVCardFromRequest($sRequest, $vcard);
	
   }

    public static function updateVCardFromRequest($sRequest, $vcard){
    	/*phonetype,emailtype,urltype,addrtype
		 * fname,lname,firm,phone,email,homepage,street,postal
		 * city,country,notice,selectedContactgroup
		 
		 * AddFields
		  * gender,nickname,position,department,bday
		  * BDAY NICKNAME 
				ORG:Muster GmbH;Personalwesen
				TITLE:Gesch�ftsf�hrer
		
		 * [0]=Family Names (also known as surnames), 
		 * [1]=Given Names, 
		 * [2]= Additional Names, 
		 * [3]=Honorific Prefixes, 
		 * [4]= Honorific Suffixes.
		 * */
		 
		$lname=isset($sRequest['lname']) && !empty($sRequest['lname'])?$sRequest['lname']:'';
		$fname=isset($sRequest['fname']) && !empty($sRequest['fname'])?$sRequest['fname']:'';
		$gender=isset($sRequest['gender']) && !empty($sRequest['gender'])?$sRequest['gender']:'';
		$categorySelection = $sRequest['selectedContactgroup'];
		
		if($categorySelection !== 'all' && $categorySelection !== 'none' && $categorySelection !== 'fav'){
			
			if(isset($vcard->CATEGORIES)){
				$property = $vcard->select('CATEGORIES');
				$property = array_shift($property);
				$oldValue = stripslashes($property->getValue());
				
				if(!stristr($oldValue,$categorySelection)){
					$newValue=(string) $oldValue.','.$value;
					$property->setValue((string) $newValue);	
				}
			}	
				
			if(!empty($categorySelection) && !isset($vcard->CATEGORIES)){	
				$vcard->CATEGORIES= $categorySelection;
			}
		}
		if(isset($sRequest['bcompany'])){
			$vcard->{'X-ABSHOWAS'} = 'COMPANY';
		}else{
			unset($vcard->{'X-ABSHOWAS'});
		}
		
		if(isset($sRequest['GEO']) && $sRequest['GEO']['lat'] != ''){
			//GEO:37.386013;-122.082932
			$vcard->GEO = $sRequest['GEO']['lat'].';'.$sRequest['GEO']['lon'];
		}else{
			unset($vcard->GEO);
		}
		//\OCP\Util::writeLog(self::$appname,'GEO: '.$vcard->GEO, \OCP\Util::DEBUG);
		if($lname!='' || $fname!='') {
			$vcard->N = array($lname,$fname,'',$gender,'');
		}
		
		$department=isset($sRequest['department']) && !empty($sRequest['department'])?$sRequest['department']:'';
		$firm=isset($sRequest['firm']) && !empty($sRequest['firm'])?$sRequest['firm']:'';
		if($department!='' || $firm!='') {
			$vcard->ORG = array($firm,$department);
		}
		
		 if($firm!='') {
		 	 $vcard->FN=$firm;
		 }else{
		 	 $vcard->FN=$lname.' '.$fname;
		 } 
		
		if(isset($sRequest['nickname']) && !empty($sRequest['nickname'])) {
			$vcard->NICKNAME = $sRequest['nickname'];
		}else{
			if(isset($vcard->NICKNAME))	unset($vcard->NICKNAME);
		}
		
		if(isset($sRequest['position']) && !empty($sRequest['position'])) {
			$vcard->TITLE=$sRequest['position'];
		}else{
			if(isset($vcard->TITLE))	unset($vcard->TITLE);
		}
		
		if(isset($sRequest['bday']) && !empty($sRequest['bday'])) {
				$date = New \DateTime($sRequest['bday']);
			    $value = $date->format('Ymd');
				$vcard->BDAY = $value;
				$vcard->BDAY->add('VALUE', 'DATE');
		}else{
			if(isset($vcard->BDAY))	unset($vcard->BDAY);
		}
		 
    	if(isset($sRequest['phone']) && !empty($sRequest['phone'][0])) {
    		$ipCount=0;
			if(isset($vcard->TEL))	unset($vcard->TEL);
			$iPref='';
			if(isset($sRequest['phonePref'])){
				$temp=explode('_',$sRequest['phonePref']);
				$iPref=$temp[1];
			}
    		foreach($sRequest['phone'] as $val){
    			//\OCP\Util::writeLog(self::$appname,'Tel PARA Found: '.$sRequest['phonePref'], \OCP\Util::DEBUG);
	    		if($val!=''){	
		    		
					$tType=$sRequest['phonetype'][$ipCount].',VOICE';
					
		    		if(stristr($sRequest['phonetype'][$ipCount],'FAX')){
		    			$tempT=explode('_',$sRequest['phonetype'][$ipCount]);
						$tType=$tempT[0].','.$tempT[1];
		    		}
					if($iPref!='' && $iPref==$ipCount){
						$vcard->add('TEL',$val,array('type'=>$tType,'pref'=>'1'));	
					}else{
						$vcard->add('TEL',$val,array('type'=>$tType));	
					} 
		    			
				}
				$ipCount++;
			}
		}else{
			if(isset($vcard->TEL))	unset($vcard->TEL);
		}
		
		if(isset($sRequest['email']) && !empty($sRequest['email'][0])) {
			$iECount=0;
			if(isset($vcard->EMAIL))	unset($vcard->EMAIL);
				$iPref='';
				if(isset($sRequest['emailPref'])){
					$temp=explode('_',$sRequest['emailPref']);
					$iPref=$temp[1];
				}
					
				foreach($sRequest['email'] as $val){
	    		if($val!=''){	
		    		
					$tType='INTERNET,'.$sRequest['emailtype'][$iECount];
		    		
				    
				    if($iPref!='' && $iPref==$iECount){
						$vcard->add('EMAIL',$val,array('type'=>$tType,'pref'=>'1'));		
					}else{
						$vcard->add('EMAIL',$val,array('type'=>$tType));		
					} 
				
				}
				$iECount++;
			}
		}else{
			if(isset($vcard->EMAIL))	unset($vcard->EMAIL);
		}
		
		//Messenger
		if(isset($sRequest['im']) && !empty($sRequest['im'][0])) {
			
			if(isset($vcard->IMPP))	unset($vcard->IMPP);
			
			$iCIm=0;
			$iPref = '';
			if(isset($sRequest['imPref'])){
				$temp=explode('_',$sRequest['imPref']);
				$iPref=$temp[1];
			}
						
			foreach($sRequest['im'] as $key => $val){
	    		if($val!== ''){
		    		$messengerType=(isset($sRequest['imtype'][$iCIm])?$sRequest['imtype'][$iCIm]:'');		
		    		$aMessenger=self::getIMOptions($messengerType);
					if($iPref !='' && $iPref == $iCIm){
						$vcard->add('IMPP',$aMessenger['protocol'].':'.$val,array('X-SERVICE-TYPE'=>$messengerType,'pref'=>'1'));	
					}else{
						$vcard->add('IMPP',$aMessenger['protocol'].':'.$val,array('X-SERVICE-TYPE'=>$messengerType));	
					} 
					
				}
				$iCIm++;
			}
		}else{
			if(isset($vcard->IMPP))	unset($vcard->IMPP);
		}
		//Cloud
		if(isset($sRequest['cloud']) && !empty($sRequest['cloud'][0])) {
			
			if(isset($vcard->CLOUD))	unset($vcard->CLOUD);
			$iCCm=0;
			$iPref = '';
			if(isset($sRequest['cloudPref'])){
				$temp=explode('_',$sRequest['cloudPref']);
				$iPref=$temp[1];
			}			
			foreach($sRequest['cloud'] as $val){
	    		if($val!== ''){
					$cloudType=(isset($sRequest['cloudtype'][$iCCm])?$sRequest['cloudtype'][$iCCm]:'');		
					if($iPref !='' && $iPref == $iCCm){
						$vcard->add('CLOUD',$val,array('type'=>$cloudType,'pref'=>'1'));	
					}else{
						$vcard->add('CLOUD',$val,array('type'=>$cloudType));	
					}
					
				}
				$iCCm++;
			}
		}else{
			if(isset($vcard->CLOUD)) 	unset($vcard->CLOUD);
		}
		
		
		if(isset($sRequest['url']) && !empty($sRequest['url'][0])) {
			
			if(isset($vcard->URL))	unset($vcard->URL);
				$iCUm = 0;
				$iPref = '';
				if(isset($sRequest['urlPref'])){
					$temp=explode('_',$sRequest['urlPref']);
					$iPref=$temp[1];
				}			
				foreach($sRequest['url'] as $val){
		    		if($val!== ''){
						$urlType=(isset($sRequest['urltype'][$iCUm])?$sRequest['urltype'][$iCUm]:'');	
						if($iPref !='' && $iPref == $iCUm){
							$vcard->add('URL',$val,array('type'=>$urlType,'pref' =>'1'));
						}else{
							$vcard->add('URL',$val,array('type'=>$urlType));
						}	
					}
				$iCUm++;
			}
		}else{
			if(isset($vcard->URL)) 	unset($vcard->URL);
		}
		
		
		if(isset($sRequest['notice']) && !empty($sRequest['notice'])) {
			$vcard->NOTE = $sRequest['notice'];
		}else{
			if(isset($vcard->NOTE))	unset($vcard->NOTE);
		}
		
		/*ADR
		 [0]= the post office box;
         [1]=the extended address (e.g., apartment or suite number);
         [2]= the street address;
         [3]=the locality (e.g., city);
         [4]=the region (e.g., state or province);
         [5]=the postal code;
         [6]=the country name*/
		$sAddrStreet=isset($sRequest['addr'][0]['street']) && !empty($sRequest['addr'][0]['street'])?$sRequest['addr'][0]['street']:'';
		$sAddrZip=isset($sRequest['addr'][0]['postal']) && !empty($sRequest['addr'][0]['postal'])?$sRequest['addr'][0]['postal']:'';
		$sAddrState=isset($sRequest['addr'][0]['state']) && !empty($sRequest['addr'][0]['state'])?$sRequest['addr'][0]['state']:'';
		$sAddrCity=isset($sRequest['addr'][0]['city']) && !empty($sRequest['addr'][0]['city'])?$sRequest['addr'][0]['city']:'';
		$sAddrCountry=isset($sRequest['addr'][0]['country']) && !empty($sRequest['addr'][0]['country'])?$sRequest['addr'][0]['country']:'';
		
		if($sAddrStreet!='' || $sAddrZip!='' || $sAddrCity!='' || $sAddrCountry!='' || $sAddrState!=''){
			 if(isset($vcard->ADR))	unset($vcard->ADR);
			 $iACount=0;
			 $iPref = '';
			if(isset($sRequest['addrPref'])){
				$temp=explode('_',$sRequest['addrPref']);
				$iPref=$temp[1];
			}
			
			 foreach($sRequest['addr'] as $val){
			 	 
				 if($val['street']!='' || $val['city']!='' || $val['postal']!='' || $val['country']!='' || $val['state']!=''){	
				 	 $saveAdress=array('','',$val['street'],$val['city'],$val['state'],$val['postal'],$val['country']);
					 
					 $tType=$sRequest['addrtype'][$iACount];
					 if($iPref !='' && $iPref == $iACount){
					 	$vcard->add('ADR',$saveAdress,array('type'=>$tType,'pref'=>'1'));
					 }
					 else{
					 	$vcard->add('ADR',$saveAdress,array('type'=>$tType));
					 }
			    	 
		    	 }
				 $iACount++;
			 }	
			
		}else{
			if(isset($vcard->ADR))	unset($vcard->ADR);
		}
		
		
		return $vcard;
		
    }
   
   public static function generateFnDummy($length=9, $strength=0) {
	    $vowels = 'aeuy';
	    $consonants = 'bdghjmnpqrstvz';
	    if ($strength & 1) {
	        $consonants .= 'BDGHJLMNPQRSTVWXZ';
	    }
	    if ($strength & 2) {
	        $vowels .= "AEUY";
	    }
	    if ($strength & 4) {
	        $consonants .= '23456789';
	    }
	    if ($strength & 8) {
	        $consonants .= '@#$%';
	    }
	    $password = '';
	    $alt = time() % 2;
	    for ($i = 0; $i < $length; $i++) {
	        if ($alt == 1) {
	            $password .= $consonants[(rand() % strlen($consonants))];
	            $alt = 0;
	        } else {
	            $password .= $vowels[(rand() % strlen($vowels))];
	            $alt = 1;
	        }
	    }
	    return $password;
	}

}
