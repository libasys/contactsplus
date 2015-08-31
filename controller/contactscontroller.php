<?php
/**
 * ownCloud - Pinit
 *
 * @author Sebastian Doell
 * @copyright 2014 sebastian doell sebastian@libasys.de
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
 
namespace OCA\ContactsPlus\Controller;

use \OCP\AppFramework\Controller;
use \OCP\AppFramework\Http\JSONResponse;
use \OCP\AppFramework\Http\TemplateResponse;
use \OCP\IRequest;
use \OCP\Share;
use \OCP\IConfig;
use Sabre\VObject;

use OCA\ContactsPlus\App as ContactsApp;
use OCA\ContactsPlus\VCard;
use OCA\ContactsPlus\Addressbook;
use OCA\ContactsPlus\Http\ImageResponse;

class ContactsController extends Controller {
	
	private $userId;
	private $l10n;
	private $configInfo;

	public function __construct($appName, IRequest $request, $userId, $l10n, IConfig $settings) {
		parent::__construct($appName, $request);
		$this -> userId = $userId;
		$this->l10n = $l10n;
		$this->configInfo = $settings;
	}
	
	  /**
     * @NoAdminRequired
     */
    public function getNewFormContact() {
    	$active_addressbooks = Addressbook::active($this->userId);
		$TELTYPE = ContactsApp::getTypesOfProperty('TEL');
		$EMAILTYPE = ContactsApp::getTypesOfProperty('EMAIL');
		$URLTYPE = ContactsApp::getTypesOfProperty('URL');
		$ADRTYPE = ContactsApp::getTypesOfProperty('ADR');
		$IMTYPE = ContactsApp::getIMOptions();
		$ADDFIELDS = ContactsApp::getAdditionalFields();
		
		$params =[
			'addressbooks' => $active_addressbooks,
			'TELTYPE' => $TELTYPE,
			'EMAILTYPE' => $EMAILTYPE,
			'URLTYPE' => $URLTYPE,
			'IMTYPE' => $IMTYPE,
			'ADRTYPE' => $ADRTYPE,
			'ADDFIELDS' => $ADDFIELDS,
			'TELTYPE_DEF' => 'WORK',
			'EMAILTYPE_DEF' => 'INTERNET',
			'URLTYPE_DEF' => 'WORK',
			'ADRTYPE_DEF' => 'WORK',
			'MESSENGERTYPE_DEF' => 'facebook',
		];
		 $response = new TemplateResponse($this->appName, 'contact.new',$params, '');  
        
        return $response;
    	
    }
	
	  /**
     * @NoAdminRequired
     */
    public function newContactSave() {
    		$postRequestAll = $this -> getParams();
    		$pHiddenField = $this -> params('hiddenfield');
			$pAddressbook = (int)$this -> params('addressbooks');
			
			if(isset($pHiddenField) && $pHiddenField==='newitContact'){
				 
			    $vcard = ContactsApp::createVCardFromRequest($postRequestAll);
			    try {
					
					$id =VCard::add(intval($pAddressbook), $vcard, null, true);
					
					$addressBookPerm=Addressbook::find($pAddressbook);
				
				$aFavourites=array();
				$carddata = VCard::find($id);
				$vcard = VObject\Reader::read($carddata['carddata']);
				$carddata['photo'] = '';
				$fullname = strtoupper(substr($carddata['fullname'],0,1));
				$newLetter= $fullname;
				$carddata['letter'] = $newLetter;
				
				$details = VCard::structureContact($vcard);
				$cardOutput = ContactsApp::renderSingleCard($details, $carddata, $addressBookPerm,$aFavourites);
				
				$params = [
					'status' => 'success',
					'data' =>[
						'id' =>$id,
						'card' => $cardOutput,
						'letter' => $newLetter,
						'addrBookId' => $pAddressbook,
					]];
	
				$response = new JSONResponse($params);
				return $response;
					
					
	
				$response = new JSONResponse($params);
				return $response;
					
				} catch(Exception $e) {
					$params = [
						'status' => 'error',
						'message' =>$e->getMessage()];
	
					$response = new JSONResponse($params);
					return $response;	
				}
				
				
		 }
    }
	
	  /**
     * @NoAdminRequired
     */
    public function getEditFormContact() {
    		
		$pId = $this -> params('id');
	
		$vcard = ContactsApp::getContactVCard($pId);
				
    	$active_addressbooks = Addressbook::active($this->userId);

		$editInfoCard = VCard::structureContact($vcard);
		
		$aDefNArray=array('0'=>'lname','1'=>'fname','2'=>'anrede', '3'=>'title');
		$aN='';
		if(isset($editInfoCard['N'][0]['value']) && count($editInfoCard['N'][0]['value'])>0){
			foreach($editInfoCard['N'][0]['value'] as $key => $val){
				if($val!='') {	
					$aN[$aDefNArray[$key]]=$val;
				}
			}
		}
		
		 $aOrgDef=array('0'=>'firm','1'=>'department');
		  $aOrg=array();
		  if(isset($editInfoCard['ORG'][0]['value']) && count($editInfoCard['ORG'][0]['value'])>0){
		  	foreach($editInfoCard['ORG'][0]['value'] as $key => $val){
					if($val!='') {	
						$aOrg[$aOrgDef[$key]]=$val;
					}
		     }
		  }
		  
		  $aUrl = array();
		   if(isset($editInfoCard['URL']) && count($editInfoCard['URL'])>0){
		   		$iUrlCount=0;	
		    	foreach($editInfoCard['URL'] as $urlInfo){
		    		$aUrl[$iUrlCount]['val'] = $urlInfo['value'];
					
					if(array_key_exists('PREF', $urlInfo['parameters'])){
						$aUrl[$iUrlCount]['pref'] = 1;
					}
					
					if(array_key_exists('TYPE', $urlInfo['parameters'])){
						foreach($urlInfo['parameters']['TYPE'] as $typeInfo){
							if($typeInfo !=='' && strtoupper($typeInfo) != 'PREF'){	
								$aUrl[$iUrlCount]['type'] = strtoupper($typeInfo);
							}
						}
					}
					$iUrlCount++;
		    	}
				
		   }else{
		   		$aUrl[0]['val'] = '';
				$aUrl[0]['type'] = 'INTERNET';
		   }
		 
		 
		 $sBday='';
		 if(isset($editInfoCard['BDAY'][0]['value']) && !empty($editInfoCard['BDAY'][0]['value'])){
		 	$sBday=$editInfoCard['BDAY'][0]['value'];
			 $date = New \DateTime($sBday);
			 $sBday = $date->format('d.m.Y');
		 }
		 
		$sNotice='';
		if(isset($editInfoCard['NOTE'][0]['value']) && !empty($editInfoCard['NOTE'][0]['value'])){
		 	$sNotice=$editInfoCard['NOTE'][0]['value'];
		 }
		
		 $sNickname='';
		 if(isset($editInfoCard['NICKNAME'][0]['value']) && !empty($editInfoCard['NICKNAME'][0]['value'])){
		 	$sNickname=$editInfoCard['NICKNAME'][0]['value'];
		 }
		 
		 $sPosition='';
		 if(isset($editInfoCard['TITLE'][0]['value']) && !empty($editInfoCard['TITLE'][0]['value'])){
		 	$sPosition=$editInfoCard['TITLE'][0]['value'];
		 }
		 
		 $addressDefArray=array('0'=>'','1'=>'','2'=>'street','3'=>'city','4'=>'state','5'=>'postalcode','6'=>'country');
		 $aAddr=array();
		 if(isset($editInfoCard['ADR']) && count($editInfoCard['ADR'])>0){
			$iACount=0;	
			foreach($editInfoCard['ADR'] as $addrInfo){
				
				foreach($addrInfo['value'] as $key => $val){	
						$aAddr[$iACount]['val'][$addressDefArray[$key]]=$val;	
				}
				if(array_key_exists('PREF', $addrInfo['parameters'])){
						$aAddr[$iACount]['pref'] = 1;
				}	
				if(array_key_exists('TYPE', $addrInfo['parameters'])){
					$temp_sel='';	
					foreach($addrInfo['parameters']['TYPE'] as $typeInfo){
						if($typeInfo !=='' && strtoupper($typeInfo) != 'PREF'){
							  if($temp_sel=='')	$temp_sel = strtoupper($typeInfo);
							  else $temp_sel.='#'.strtoupper($typeInfo);
						}
				   }
				   $aAddr[$iACount]['type'] = $temp_sel;
				   if($aAddr[$iACount]['type'] === ''){
				   		$aAddr[$iACount]['type'] = 'WORK';
				   }
				}
				$iACount++;
			}
			
		   }else{
		   	  $aAddr[0]['val']=array('street'=>'','postalcode'=>'','city'=>'','state'=>'','country'=>'');
			  $aAddr[0]['type']='WORK';
		   }
		   
		   /*TEL;TYPE=pref:0151 50114479
			TEL;TYPE=WORK,VOICE:0824 7254
			TEL;TYPE=WORK,FAX:08224 78335
			TEL;TYPE=CELL,VOICE:0160 8042056*/
		  
		   $aTel=array();
		    if(isset($editInfoCard['TEL']) && count($editInfoCard['TEL'])>0){
		    	$iCount=0;	
		    	foreach($editInfoCard['TEL'] as $telInfo){
		    		
		    		$aTel[$iCount]['val']=$telInfo['value'];
					
					if(array_key_exists('PREF', $telInfo['parameters'])){
						$aTel[$iCount]['pref'] = 1;
					}	
					
					if(array_key_exists('TYPE', $telInfo['parameters'])){
						$testType = strtoupper(implode('_',$telInfo['parameters']['TYPE']));	
					
						if(stristr($testType,'_PREF')){
							$aTel[$iCount]['pref'] = 1;
							$testType = str_replace('_PREF', '', $testType);	
						}
						if(stristr($testType,'_VOICE')){
							$sTypeTemp=explode('_',$testType);	
	                		$aTel[$iCount]['type'] = $sTypeTemp[0];
						}else{
							$aTel[$iCount]['type'] = $testType;
						}
						
						if($aTel[$iCount]['type'] === ''){
							$aTel[$iCount]['type'] = 'OTHER';
						}
						
					//\OCP\Util::writeLog($this->appName,'TYPE OF TEL '. strtoupper($test), \OCP\Util::DEBUG);	
					
					
				}
					$iCount++;
			    }
		       
			}else{
				$aTel[0]['val']='';
				$aTel[0]['type']='WORK';
			}
			//FIXME
			$aMessenger=array();
		    if(isset($editInfoCard['IMPP']) && count($editInfoCard['IMPP'])>0){
		    	$iMCount=0;	
		    	foreach($editInfoCard['IMPP'] as $messengerInfo){
		    		if(array_key_exists('PREF', $messengerInfo['parameters'])){
						$aMessenger[$iMCount]['pref'] = 1;
					}		
		    		$aMessenger[$iMCount]['val'] = $messengerInfo['value'];
					$aMessenger[$iMCount]['type'] = $messengerInfo['parameters']['X-SERVICE-TYPE'];
					
		    	$iMCount++;
				}
			
		    }else{
		    	$aMessenger[0]['val'] = '';
				$aMessenger[0]['type'] = 'facebook';	
		    }
			
			$aCloud=array();
			
		    if(isset($editInfoCard['CLOUD']) && count($editInfoCard['CLOUD'])>0){
		    	$iClCount=0;	
		    	foreach($editInfoCard['CLOUD'] as $cloudInfo){
		    		if(array_key_exists('PREF', $cloudInfo['parameters'])){
						$aCloud[$iClCount]['pref'] = 1;
					}	
		    		$aCloud[$iClCount]['val'] = $cloudInfo['value'];
					
					if(array_key_exists('TYPE', $cloudInfo['parameters'])){
						foreach($cloudInfo['parameters']['TYPE'] as $typeInfo){
							$aCloud[$iClCount]['type'] = $typeInfo;
						}
					}
					$iClCount++;
		    	}
				
		    }else{
		    	$aCloud[0]['val'] = '';
				$aCloud[0]['type'] = 'WORK';
		    }
			
			$aEmail=array();
		    if(isset($editInfoCard['EMAIL']) && count($editInfoCard['EMAIL'])>0){
		    	$iECount=0;	
		    	foreach($editInfoCard['EMAIL'] as $emailInfo){
		    		
		    		$aEmail[$iECount]['val']=$emailInfo['value'];
					
					if(array_key_exists('PREF', $emailInfo['parameters'])){
						$aEmail[$iECount]['pref']=$emailInfo['parameters']['PREF'];
					}	
					
					if(array_key_exists('TYPE', $emailInfo['parameters'])){
						 $temp_sel='';	
						foreach($emailInfo['parameters']['TYPE'] as $typeInfo){
							if($typeInfo !== '' && strtoupper($typeInfo) != 'PREF'){
								  if($temp_sel=='')	$temp_sel = strtoupper($typeInfo);
							      else $temp_sel.='#'.strtoupper($typeInfo);
							}
							
							if(stristr($temp_sel,'INTERNET')){
					   			$sTypeTemp=explode('#',$temp_sel);
								if(count($sTypeTemp)	> 1){
		                			$aEmail[$iECount]['type']=$sTypeTemp[1];
								}else{
									$aEmail[$iECount]['type']=$temp_sel;
								}
								
						   }else{
						   	   $aEmail[$iECount]['type'] = $temp_sel;
						   }
							if($aEmail[$iECount]['type'] === ''){
								$aEmail[$iECount]['type'] = 'WORK';
							}
						}
				   }
					$iECount++;
			    }
			}else{
				$aEmail[0]['val']='';
				$aEmail[0]['type']='WORK';
			}
			
			$bPhoto=0;
			 $imgSrc='';
			 $imgMimeType='';
			 $thumb = '<div id="noimage" class="ioc ioc-user"></div>';
			 if (isset($vcard->PHOTO)){
			 	$bPhoto=1;
				 $thumb='';
				 $image = new \OCP\Image();
				 $image->loadFromData((string)$vcard->PHOTO);
				 $imgSrc=$image->__toString();
				 $imgMimeType=$image->mimeType();
				 \OC::$server->getCache()->set('edit-contacts-foto-' . $pId, $image -> data(), 600);
			 }
			 	
		$TELTYPE = ContactsApp::getTypesOfProperty('TEL');
		$EMAILTYPE = ContactsApp::getTypesOfProperty('EMAIL');
		$URLTYPE = ContactsApp::getTypesOfProperty('URL');
		$ADRTYPE = ContactsApp::getTypesOfProperty('ADR');
		$IMTYPE = ContactsApp::getIMOptions();
		$ADDFIELDS = ContactsApp::getAdditionalFields();

	    $oldaddressbookid=VCard::getAddressbookid($pId);
		$addressBookPerm=Addressbook::find($oldaddressbookid);
	
		$maxUploadFilesize = \OCP\Util::maxUploadFilesize('/');
		//FIXME
		$params =[
			'id' => $pId,
			'uploadMaxHumanFilesize' => \OCP\Util::humanFileSize($maxUploadFilesize),
			'oldaddressbookid' => $oldaddressbookid,
			'addressbooks' => $active_addressbooks,
			'addressbooksPerm' => $addressBookPerm,
			'tmpkey' =>  'edit-contacts-foto-' . $pId,
			'isPhoto' => $bPhoto,
			'thumbnail' => $thumb,
			'imgsrc' => $imgSrc,
			'imgMimeType' => $imgMimeType,
			'anrede' => isset($aN['title']) ? $aN['title'] : '',
			'fname' => isset($aN['fname']) ? $aN['fname'] : '',
			'lname' =>  isset($aN['lname']) ? $aN['lname'] : '',
			'firm' => isset($aOrg['firm']) ? $aOrg['firm'] : '',
			'department' => isset($aOrg['department']) ? $aOrg['department'] : '',
			'aTel' => isset($aTel) ? $aTel : '',
			'aEmail' => isset($aEmail) ? $aEmail : '',
			'aAddr' => isset($aAddr) ? $aAddr : '',
			'aUrl' => isset($aUrl) ? $aUrl : '',
			'aCloud' => isset($aCloud) ? $aCloud : '',
			'aMessenger' => isset($aMessenger) ? $aMessenger : '',
			'sBday' => isset($sBday) ? $sBday : '',
			'nickname' => isset($sNickname) ? $sNickname : '',
			'position' => isset($sPosition) ? $sPosition : '',
			'sNotice' => isset($sNotice) ? $sNotice : '',
			'TELTYPE' => $TELTYPE,
			'EMAILTYPE' => $EMAILTYPE,
			'URLTYPE' => $URLTYPE,
			'ADRTYPE' => $ADRTYPE,
			'IMTYPE' => $IMTYPE,
			'ADDFIELDS' => $ADDFIELDS,
		];
		
		 $response = new TemplateResponse($this->appName, 'contact.edit',$params, '');  
 		
        return $response;
    	
    }
	
	  /**
     * @NoAdminRequired
     */
    public function editContactSave() {
    			
    		$postRequestAll = $this -> getParams();
    		$pHiddenField = $this -> params('hiddenfield');
			$pId = $this -> params('id');
			$pAddressbook = $this -> params('addressbooks');
			$pOldAddressbook = $this -> params('oldaddressbookid');
			$pOldLastname = $this -> params('lname');
			$pOldLastname=strtoupper(substr($pOldLastname,0,1));
			$pOldFirm = $this -> params('firm');
			$pOldFirm=strtoupper(substr($pOldFirm,0,1));
			
			$vcard = ContactsApp::getContactVCard($pId);
			
			if(isset($pHiddenField) && $pHiddenField==='editContact'){
				  $vcard = ContactsApp::updateVCardFromRequest($postRequestAll,$vcard);
		    try {
				VCard::edit($pId, $vcard);
				$changeAddrBook = '';
				if($pOldAddressbook !== $pAddressbook){
					VCard::moveToAddressBook(intval($pAddressbook), $pId);
					$changeAddrBook = $pAddressbook;
				}
				
				$addressBookPerm=Addressbook::find($pAddressbook);
				//Favourites
				$favorites = \OC::$server -> getTagManager() -> load(ContactsApp::$appname)->getFavorites();
				
				$aFavourites=array();
				if(is_array($favorites)){
					foreach($favorites as $fav){
						$aFavourites[$fav]=1;
					}
				}
				$carddata = VCard::find($pId);
				$vcard = VObject\Reader::read($carddata['carddata']);
				$carddata['photo'] = '';
				if($vcard->PHOTO){
					$image = new \OCP\Image();
					 $image->loadFromData((string)$vcard->PHOTO);
					 $imgSrc=$image->__toString();
					 $carddata['photo'] ='data:'.$image->mimeType().';base64,' .$imgSrc;
				}
				$fullname = strtoupper(substr($carddata['fullname'],0,1));
				$newLetter = $fullname;
				$carddata['letter'] = $newLetter;
				
				$details = VCard::structureContact($vcard);
				$cardOutput = ContactsApp::renderSingleCard($details, $carddata, $addressBookPerm,$aFavourites);
				
				
			
				
				$params = [
					'status' => 'success',
					'data' =>[
						'id' =>$pId,
						'card' => $cardOutput,
						'letter' => $newLetter,
						'newAddrBookId' => $changeAddrBook
					]];
	
				$response = new JSONResponse($params);
				return $response;
				
			} catch(Exception $e) {
				$params = [
						'status' => 'error',
						'message' =>$e->getMessage()];
	
				$response = new JSONResponse($params);
				return $response;	
				
			}
				
				
		 }
    }
	
	  public function ApiContactData($id) {
	  		
	  	$vcard = ContactsApp::getContactVCard($id);
		$oldaddressbookid = VCard::getAddressbookid($id);
		$addressBookPerm = Addressbook::find($oldaddressbookid);
		
		$editInfoCard = VCard::structureContact($vcard);
		
		$TELTYPE = ContactsApp::getTypesOfProperty('TEL');
		$EMAILTYPE = ContactsApp::getTypesOfProperty('EMAIL');	
		$URLTYPE = ContactsApp::getTypesOfProperty('URL');
		$ADRTYPE = ContactsApp::getTypesOfProperty('ADR');
		
		$aDefNArray=array('0'=>'lname','1'=>'fname','2'=>'anrede', '3'=>'title');
		$aN='';
		if(isset($editInfoCard['N'][0]['value']) && count($editInfoCard['N'][0]['value'])>0){
			foreach($editInfoCard['N'][0]['value'] as $key => $val){
				if($val!='') {	
					$aN[$aDefNArray[$key]]=$val;
				}
			}
		}
		
		 $aOrgDef=array('0'=>'firm','1'=>'department');
		  $aOrg=array();
		  if(isset($editInfoCard['ORG'][0]['value']) && count($editInfoCard['ORG'][0]['value'])>0){
		  	foreach($editInfoCard['ORG'][0]['value'] as $key => $val){
					if($val!='') {	
						$aOrg[$aOrgDef[$key]]=$val;
					}
		     }
		  }
		  
		 $sBday='';
		 if(isset($editInfoCard['BDAY'][0]['value']) && !empty($editInfoCard['BDAY'][0]['value'])){
		 	$sBday = $editInfoCard['BDAY'][0]['value'];
			$date = New \DateTime($sBday);
			$sBday = $date->format('d. M Y');
		 }

		 $sNotice='';
		if(isset($editInfoCard['NOTE'][0]['value']) && !empty($editInfoCard['NOTE'][0]['value'])){
		 	$sNotice = $editInfoCard['NOTE'][0]['value'];
		 }

		 $sNickname='';
		 if(isset($editInfoCard['NICKNAME'][0]['value']) && !empty($editInfoCard['NICKNAME'][0]['value'])){
		 	$sNickname = $editInfoCard['NICKNAME'][0]['value'];
		 }
		 
		 $sPosition='';
		 if(isset($editInfoCard['TITLE'][0]['value']) && !empty($editInfoCard['TITLE'][0]['value'])){
		 	$sPosition = $editInfoCard['TITLE'][0]['value'];
		 }
		 
		$aAddr = '';
		if(array_key_exists('ADR', $editInfoCard)){
			$aAddr = $this->getAddressInfo($editInfoCard['ADR'], $ADRTYPE);
		}
		$aTel = '';
		if(array_key_exists('TEL', $editInfoCard)){
			$aTel = $this->getPhoneInfo($editInfoCard['TEL'], $TELTYPE);
		}
		$aEmail = '';
		if(array_key_exists('EMAIL', $editInfoCard)){
	  		$aEmail = $this->getEmailInfo($editInfoCard['EMAIL'], $EMAILTYPE);
		}
		
		$aUrl = '';
		if(array_key_exists('URL', $editInfoCard)){
			$aUrl = $this->getUrlInfo($editInfoCard['URL'], $URLTYPE);
		}
		
		
		 $imgSrc='';
		 $imgMimeType='';
		
		 if (isset($vcard->PHOTO)){
			 $image = new \OCP\Image();
			 $image->loadFromData((string)$vcard->PHOTO);
			 $imgSrc=$image->__toString();
			 $imgMimeType=$image->mimeType();
			 \OC::$server->getCache()->set('show-contacts-foto-' . $id, $image -> data(), 600);
		}
		
		$params = [
			'id' => $id,
			'tmpkey' => 'show-contacts-foto-' . $id,
			'addressbookinfo' => $addressBookPerm,
			'imgsrc' => $imgSrc,
			'imgMimeType' => $imgMimeType,
			'anrede' => isset($aN['title']) ? $aN['title'] : '',
			'surename' => isset($aN['fname']) ? $aN['fname'] : '',
			'lastname' => isset($aN['lname']) ? $aN['lname'] : '',
			'firm' => isset($aOrg['firm']) ? $aOrg['firm'] : '',
			'department' => isset($aOrg['department']) ? $aOrg['department'] : '',
			'phone' => isset($aTel) ? $aTel : '',
			'email' => isset($aEmail) ? $aEmail : '',
			'address' => isset($aAddr) ? $aAddr : '',
			'url' => isset($aUrl) ? $aUrl : '',
			'birthday' =>  isset($sBday) ? $sBday : '',
			'nickname' => isset($sNickname) ? $sNickname : '',
			'position' => isset($sPosition) ? $sPosition : '',
			'notice' => isset($sNotice) ? $sNotice : '',
		];
		
		return $params;
	  }
		 /**
	     * @NoAdminRequired
		  *
		 * @NoCSRFRequired
	     */
     
		public function getContactPhoto($id){
			$vcard = ContactsApp::getContactVCard($id);
			
			if (isset($vcard->PHOTO)){
				 $image = new \OCP\Image();
				 $image->loadFromData((string)$vcard->PHOTO);
				 \OC::$server->getCache()->set('show-contacts-foto-' . $id, $image -> data(), 600);
				 
				 return $response = new ImageResponse($image);
			}
		}
	
	  /**
     * @NoAdminRequired
     */
    public function showContact($id) {
    	$id = $this -> params('id');
		
		$vcard = ContactsApp::getContactVCard($id);
		$oldaddressbookid = VCard::getAddressbookid($id);
		$addressBookPerm = Addressbook::find($oldaddressbookid);
		
		$editInfoCard = VCard::structureContact($vcard);
		
		$TELTYPE = ContactsApp::getTypesOfProperty('TEL');
		$EMAILTYPE = ContactsApp::getTypesOfProperty('EMAIL');	
		$URLTYPE = ContactsApp::getTypesOfProperty('URL');
		$ADRTYPE = ContactsApp::getTypesOfProperty('ADR');
		$IMTYPE = ContactsApp::getIMOptions();
		
		$aDefNArray=array('0'=>'lname','1'=>'fname','2'=>'anrede', '3'=>'title');
		$aN='';
		if(isset($editInfoCard['N'][0]['value']) && count($editInfoCard['N'][0]['value'])>0){
			foreach($editInfoCard['N'][0]['value'] as $key => $val){
				if($val!='') {	
					$aN[$aDefNArray[$key]]=$val;
				}
			}
		}
		
		 $aOrgDef=array('0'=>'firm','1'=>'department');
		  $aOrg=array();
		  if(isset($editInfoCard['ORG'][0]['value']) && count($editInfoCard['ORG'][0]['value'])>0){
		  	foreach($editInfoCard['ORG'][0]['value'] as $key => $val){
					if($val!='') {	
						$aOrg[$aOrgDef[$key]]=$val;
					}
		     }
		  }
		  
		 $sBday='';
		 if(isset($editInfoCard['BDAY'][0]['value']) && !empty($editInfoCard['BDAY'][0]['value'])){
		 	$sBday=$editInfoCard['BDAY'][0]['value'];
			 $date = New \DateTime($sBday);
			$sBday = $date->format('d. M Y');
		 }

		 $sNotice='';
		if(isset($editInfoCard['NOTE'][0]['value']) && !empty($editInfoCard['NOTE'][0]['value'])){
		 	$sNotice=$editInfoCard['NOTE'][0]['value'];
		 }

		 $sNickname='';
		 if(isset($editInfoCard['NICKNAME'][0]['value']) && !empty($editInfoCard['NICKNAME'][0]['value'])){
		 	$sNickname=$editInfoCard['NICKNAME'][0]['value'];
		 }
		 
		 $sPosition='';
		 if(isset($editInfoCard['TITLE'][0]['value']) && !empty($editInfoCard['TITLE'][0]['value'])){
		 	$sPosition=$editInfoCard['TITLE'][0]['value'];
		 }
		 
		$aAddr = '';
		if(array_key_exists('ADR', $editInfoCard)){
			$aAddr = $this->getAddressInfo($editInfoCard['ADR'], $ADRTYPE);
		}
		$aTel = '';
		if(array_key_exists('TEL', $editInfoCard)){
			$aTel = $this->getPhoneInfo($editInfoCard['TEL'], $TELTYPE);
		}
		$aEmail = '';
		if(array_key_exists('EMAIL', $editInfoCard)){
	  		$aEmail = $this->getEmailInfo($editInfoCard['EMAIL'], $EMAILTYPE);
		}
		
		$aUrl = '';
		if(array_key_exists('URL', $editInfoCard)){
			$aUrl = $this->getUrlInfo($editInfoCard['URL'], $URLTYPE);
		}
		
		$aImpp = '';
		if(array_key_exists('IMPP', $editInfoCard)){
			$aImpp = $this->getImppInfo($editInfoCard['IMPP'], $IMTYPE);
		}
		
		$aCloud = '';
		if(array_key_exists('CLOUD', $editInfoCard)){
			$aCloud = $this->getCloudInfo($editInfoCard['CLOUD'], $ADRTYPE);
		}
		
		 $bPhoto=0;
		 $imgSrc='';
		 $imgMimeType='';
		 $thumb = '<div id="noimage" class="ioc ioc-user"></div>';
		 if (isset($vcard->PHOTO)){
		 	$bPhoto=1;
			 $thumb='';
			 $image = new \OCP\Image();
			 $image->loadFromData((string)$vcard->PHOTO);
			 $imgSrc=$image->__toString();
			 $imgMimeType=$image->mimeType();
			 \OC::$server->getCache()->set('show-contacts-foto-' . $id, $image -> data(), 600);
			 
		}
		 
		 
		 
		$maxUploadFilesize = \OCP\Util::maxUploadFilesize('/');
		
		$params = [
			'id' => $id,
			'tmpkey' => 'show-contacts-foto-' . $id,
			'oldaddressbookid' => $oldaddressbookid,
			'addressbooksPerm' => $addressBookPerm,
			'isPhoto' => $bPhoto,
			'thumbnail' => $thumb,
			'imgsrc' => $imgSrc,
			'imgMimeType' => $imgMimeType,
			'anrede' => isset($aN['title']) ? $aN['title'] : '',
			'fname' => isset($aN['fname']) ? $aN['fname'] : '',
			'lname' => isset($aN['lname']) ? $aN['lname'] : '',
			'firm' => isset($aOrg['firm']) ? $aOrg['firm'] : '',
			'department' => isset($aOrg['department']) ? $aOrg['department'] : '',
			'uploadMaxHumanFilesize' => \OCP\Util::humanFileSize($maxUploadFilesize),
			'aTel' => isset($aTel) ? $aTel : '',
			'aEmail' => isset($aEmail) ? $aEmail : '',
			'aAddr' => isset($aAddr) ? $aAddr : '',
			'aUrl' => isset($aUrl) ? $aUrl : '',
			'aImpp' => isset($aImpp) ? $aImpp : '',
			'aCloud' => isset($aCloud) ? $aCloud : '',
			'sBday' =>  isset($sBday) ? $sBday : '',
			'nickname' => isset($sNickname) ? $sNickname : '',
			'position' => isset($sPosition) ? $sPosition : '',
			'sNotice' => isset($sNotice) ? $sNotice : '',
		];
		
		 $response = new TemplateResponse($this->appName, 'contact.show',$params, '');  
        
        return $response;
		
		
    }
	
	  /**
     * @NoAdminRequired
     */
    public function deleteContact() {
    	$pId = $this -> params('id');
		
		$vcard = ContactsApp::getContactVCard($pId);

		$property = $vcard->select('CATEGORIES');
		
		if(count($property) === 0) {
			$oldValue='';
		}else{
			$property = array_shift($property);	
			$oldValue=stripslashes($property->getValue());
		}
		$carddata = VCard::find($pId);
		$fullname = strtoupper(substr($carddata['fullname'],0,1));
		VCard::delete($pId);
		
		$params = [
		'status' => 'success',
		'data' =>[
			'id' =>$pId,
			'groups' => $oldValue,
			'letter' => $fullname
		]];
		
		$response = new JSONResponse($params);
		return $response;
		
    }
	
	  /**
     * @NoAdminRequired
     */
	public function copyContact(){
		
		$pId = $this -> params('id');
		$pAddrBookId = $this -> params('addrid');	
		
		$vcard = ContactsApp::getContactVCard($pId);
		$oldcard = VCard::find($pId);
		
		if($oldcard['addressbookid']!== $pAddrBookId){
			VCard::add($pAddrBookId, $vcard);
			$params = [
				'status' => 'success',
				'data' =>[
					'id' =>$pId
				]];
			
		}else{
				$sMsg='Kontakt konnte nicht kopiert werden!';
			$params = [
				'status' => 'error',
				'data' =>[
					'msg' =>$sMsg
				]];
		}
		
		$response = new JSONResponse($params);
		return $response;
		
	}
	
	 /**
     * @NoAdminRequired
     */
	public function moveContact(){
		$pId = $this -> params('id');
		$pAddrBookId = $this -> params('addrid');	
		
		
		$oldcard = VCard::find($pId);
		
		if($oldcard['addressbookid']!== $pAddrBookId){
			VCard::moveToAddressBook($pAddrBookId, $pId);
			$params = [
				'status' => 'success',
				'data' =>[
					'id' =>$pId
				]];
			
		}else{
			$sMsg='Kontakt konnte nicht verschoben werden!';
			$params = [
				'status' => 'error',
				'data' =>[
					'msg' =>$sMsg
				]];
		}
		
		$response = new JSONResponse($params);
		return $response;
	}
	
	  /**
     * @NoAdminRequired
     */
    public function deleteContactFromGroup() {
    	$pId = $this -> params('id');
		
		$vcard = ContactsApp::getContactVCard($pId);

		$property = $vcard->select('CATEGORIES');
		
		if(count($property) === 0) {
			$oldValue='';
		}else{
			$property = array_shift($property);	
			$oldValue=stripslashes($property->getValue());
		}
		
		unset($vcard->CATEGORIES);
		VCard::edit($pId, $vcard);
		$carddata = VCard::find($pId);
		$fullname = strtoupper(substr($carddata['fullname'],0,1));
		
		$params = [
		'status' => 'success',
		'data' =>[
			'id' =>$pId,
			'scat' => $oldValue,
			'letter' => $fullname
		]];
		
		$response = new JSONResponse($params);
		return $response;
		
    }
	
	  /**
     * @NoAdminRequired
     */
    public function addProbertyToContact() {
    	$cardId = $this -> params('cardId');
		$param = trim(strip_tags($this -> params('param')));
		$value = trim(strip_tags($this -> params('value')));
		
		$vcard = ContactsApp::getContactVCard($cardId);
		$vcardMember='urn:uuid:'.$vcard->{'UID'};
		
		$property = $vcard->select($param);
		
		if(count($property) === 0) {
			//Neu
			$vcard->add($param,$value);
			$iNumber = 1;
		} else {
			$property = array_shift($property);
			$oldValue = stripslashes($property->getValue());
			
			if(!stristr($oldValue,$value)){
				$newValue=(string) $oldValue.','.$value;
				$property->setValue((string) $newValue);	
				$iNumber=1;
			}else{
				$iNumber=0;
			}
		}
		
		VCard::edit($cardId, $vcard);
		

		//IOS Support $vcardMember
		$iosSupport = $this->configInfo->getUserValue($this->userId, $this->appName,'iossupport');
		if($param === 'CATEGORIES' && $iosSupport){
			
			if (!is_null($vcard) && !isset($vcard->REV)) {
				$rev = new \DateTime('@'.$groupData['lastmodified']);
				$vcard->REV = $rev->format(\DateTime::W3C);
			}
			
			$paramsGroupMembers=array();
			$iosGroupMember = $vcard->{'X-ADDRESSBOOKSERVER-MEMBER'};
			$bExist=false;
			if($iosGroupMember){
				foreach($iosGroupMember as $key => $param){
					if($param === $vcardMember){
						$bExist=true;
					}	
				}
			}else{
				$vcard->add('X-ADDRESSBOOKSERVER-MEMBER',$vcardMember);
			}
			
			if(!$bExist){
				$vcard->add('X-ADDRESSBOOKSERVER-MEMBER',$vcardMember);
			}
			
			VCard::edit($cardId, $vcard);
		}
		if($param === 'CATEGORIES'){
			$backgroundColor=	ContactsApp::genColorCodeFromText(trim($value),80);
			$color = ContactsApp::generateTextColor($backgroundColor);
			$aCat['name'] = $value;
			$aCat['color'] = $color;
			$aCat['bgcolor'] = $backgroundColor;
		}
		$params = [
		'status' => 'success',
		'data' =>[
			'cardid' => $cardId,
			'iCounter'=>$iNumber,
			'newcat' => $aCat
		]];
		
		$response = new JSONResponse($params);
		return $response;
		
    }
	  /**
     * @NoAdminRequired
     */
    public function getContactCards() {
    	$grpId = $this -> params('grpid');
		$addrbookId = intval($this -> params('ardbid'));
		$grpId=isset($grpId) ? $grpId:'all';
		
		if($addrbookId==0){
			$active_addressbooks = Addressbook::all($this->userId);
			$ids = array();
			foreach($active_addressbooks as $addressbook) {
				$ids[] = $addressbook['id'];
				
			}
			$addrbookId=$ids[0];
		}
		$cardslist=ContactsApp::renderOutput($addrbookId,$grpId);
		
		return $cardslist;
    }
	
	private function getAddressInfo(array $editInfoAddr, $ADRTYPE){
	   	
	   $addressDefArray=array('0'=>'','1'=>'','2'=>'street','3'=>'city','4'=>'state','5'=>'postalcode','6'=>'country');
	   $aAddr='';
	   if(isset($editInfoAddr) && count($editInfoAddr)>0){
			$iACount=0;	
			foreach($editInfoAddr as $addrInfo){
				if(array_key_exists('PREF', $addrInfo['parameters'])){
					$aAddr[$iACount]['pref']=$addrInfo['parameters']['PREF'];
				}
				
				foreach($addrInfo['value'] as $key => $val){	
					
						$aAddr[$iACount]['val'][$addressDefArray[$key]]=$val;	
					
				}
				
				 if(array_key_exists('TYPE', $addrInfo['parameters'])){
					$temp_sel='';	
					foreach($addrInfo['parameters']['TYPE'] as $typeInfo){
						if(strtoupper($typeInfo) != 'PREF' && $typeInfo !== ''){
							  if($temp_sel=='')	$temp_sel=$ADRTYPE[$typeInfo];
							  else $temp_sel.=', '.$ADRTYPE[$typeInfo];
						}
				   }
				   $aAddr[$iACount]['type']=$temp_sel;
				   if($aAddr[$iACount]['type'] === ''){
						$aAddr[$iACount]['type'] = $ADRTYPE['WORK'];
					}
				   
				}
				$iACount++;
			}
	   }
	   return $aAddr;
	}
	
	private function getPhoneInfo(array $editInfoPhone, $TELTYPE){
		$aTel='';
	    if(isset($editInfoPhone) && count($editInfoPhone)>0){
	    	$iCount=0;	
	    	foreach($editInfoPhone as $telInfo){
	    		
	    		$aTel[$iCount]['val']=$telInfo['value'];
				
				if(array_key_exists('PREF', $telInfo['parameters'])){
					$aTel[$iCount]['pref']=$telInfo['parameters']['PREF'];
				}	
				
				if(array_key_exists('TYPE', $telInfo['parameters'])){
				 $temp_sel='';
				 $temp_search='';	
				 
				foreach($telInfo['parameters']['TYPE'] as $typeInfo){
						
					if($typeInfo !=='' && strtoupper($typeInfo) !== 'PREF' && strtoupper($typeInfo) !== 'VOICE'){
						  if($temp_sel === '')	{
						  	$temp_sel = $TELTYPE[$typeInfo];
							 $temp_search = $typeInfo; 
						  }else{
						  	 $temp_sel.=', '.$TELTYPE[$typeInfo];
							 $temp_search.='_'.$typeInfo; 
						  }
					}
				}
				if(stristr($temp_search,'FAX')){
					$temp_sel=$TELTYPE['WORK_FAX'];
				}
		      	$aTel[$iCount]['type']=$temp_sel;
				
				if($aTel[$iCount]['type'] === ''){
					$aTel[$iCount]['type'] = $TELTYPE['WORK'];
				}
					
			}
				$iCount++;
		    }
		}
		
		return $aTel;
	}
	
	private function getEmailInfo(array $editInfoEmail,$EMAILTYPE){
		$aEmail='';
	    if(isset($editInfoEmail) && count($editInfoEmail)>0){
	    	
	    	$iECount=0;	
	    	foreach($editInfoEmail as $emailInfo){
	    		
	    		$aEmail[$iECount]['val']=$emailInfo['value'];
				
				if(array_key_exists('PREF', $emailInfo['parameters'])){
					$aEmail[$iECount]['pref']=$emailInfo['parameters']['PREF'];
				}
	
				if(array_key_exists('TYPE', $emailInfo['parameters'])){
					 $temp_sel='';	
					foreach($emailInfo['parameters']['TYPE'] as $typeInfo){
						if($typeInfo !== '' && strtoupper($typeInfo) !== 'PREF' && strtoupper($typeInfo) !== 'INTERNET'){
							  if($temp_sel === ''){
							  		$temp_sel=$EMAILTYPE[$typeInfo];
						      }else{
						      	 $temp_sel.=', '.$EMAILTYPE[$typeInfo];
						      }
						}
	                	$aEmail[$iECount]['type'] = $temp_sel;
					}
					if($aEmail[$iECount]['type'] === ''){
						$aEmail[$iECount]['type'] = $EMAILTYPE['WORK'];
					}
			   }
				$iECount++;
		    }
		}
		
		return $aEmail;
	}
	
	private function getUrlInfo(array $editInfoUrl, $URLTYPE){
		  
		 $aUrl = array();
		   if(isset($editInfoUrl) && count($editInfoUrl)>0){
		   		$iUrlCount=0;	
		    	foreach($editInfoUrl as $urlInfo){
		    		$aUrl[$iUrlCount]['val'] = $urlInfo['value'];
					if(array_key_exists('PREF', $urlInfo['parameters'])){
						$aUrl[$iUrlCount]['pref']=$urlInfo['parameters']['PREF'];
					}
					if(array_key_exists('TYPE', $urlInfo['parameters'])){
						foreach($urlInfo['parameters']['TYPE'] as $typeInfo){
							if($typeInfo !=='' && strtoupper($typeInfo) != 'PREF'){	
								$aUrl[$iUrlCount]['type'] = $URLTYPE[$typeInfo];
							}
						}
					}
					$iUrlCount++;
		    	}
				
		   }else{
		   		$aUrl[0]['val'] = '';
				$aUrl[0]['type'] = $URLTYPE['INTERNET'];
		   }
		   
		 
		  
		  return $aUrl;
	}
	private function getImppInfo(array $editInfoImpp, $IMTYPE){
		  
		 $returnArray = array();
		   if(isset($editInfoImpp) && count($editInfoImpp)>0){
		   		$iCount=0;	
		    	foreach($editInfoImpp as $editInfo){
		    		$returnArray[$iCount]['val'] = $editInfo['value'];
					if(array_key_exists('PREF', $editInfo['parameters'])){
						$returnArray[$iCount]['pref']=$editInfo['parameters']['PREF'];
					}
					if(array_key_exists('X-SERVICE-TYPE', $editInfo['parameters'])){
						$returnArray[$iCount]['type'] = $IMTYPE[$editInfo['parameters']['X-SERVICE-TYPE']]['displayname'];
					}
					
					$iCount++;
					}
					
		  
				
		   }else{
		   		$returnArray[0]['val'] = '';
				$returnArray[0]['type'] = $IMTYPE['facebook'];
		   }
		   
		 
		  
		  return $returnArray;
	}
	private function getCloudInfo(array $editInfoCloud, $CLOUDTYPE){
		  
		 $returnArray = array();
		   if(isset($editInfoCloud) && count($editInfoCloud)>0){
		   		$iCount=0;	
		    	foreach($editInfoCloud as $editInfo){
		    		$returnArray[$iCount]['val'] = $editInfo['value'];
					if(array_key_exists('PREF', $editInfo['parameters'])){
						$returnArray[$iCount]['pref']=$editInfo['parameters']['PREF'];
					}
					if(array_key_exists('TYPE', $editInfo['parameters'])){
						foreach($editInfo['parameters']['TYPE'] as $typeInfo){
							if($typeInfo !=='' && strtoupper($typeInfo) != 'PREF'){	
								$returnArray[$iCount]['type'] = $CLOUDTYPE[$typeInfo];
							}
						}
					}
					$iCount++;
		    	}
				
		   }else{
		   		$returnArray[0]['val'] = '';
				$returnArray[0]['type'] = $CLOUDTYPE['INTERNET'];
		   }
		   
		 
		  
		  return $returnArray;
	}
}