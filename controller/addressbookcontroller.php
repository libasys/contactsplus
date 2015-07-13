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

use OCA\ContactsPlus\App as ContactsApp;
use OCA\ContactsPlus\VCard;
use OCA\ContactsPlus\Addressbook;

class AddressbookController extends Controller {
	
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
    public function getAddressBooks() {
			
		$active_addressbooks = array();

		$active_addressbooks = Addressbook::all($this->userId);
		if(count($active_addressbooks) === 0) {
			Addressbook::addDefault($this->userId);
			$active_addressbooks = Addressbook::all($this->userId);
		}
		
		$countCardsAddressbooks = Addressbook::getCountCardsAddressbook($this->userId);
		
		$contacts_addressbook = array();
		$ids = array();
		foreach($active_addressbooks as $addressbook) {
			$ids[] = $addressbook['id'];
			
		}
		$idActiveAddressbook=$ids[0];
			
		$mySharees=ContactsApp::getAddressbookSharees();
		$bShareApi = \OC::$server->getAppConfig()->getValue('core', 'shareapi_enabled', 'yes');
		
		$output='';
		foreach($active_addressbooks as $addressbookInfo){
		 	$activeClass='';
			$checked='';
			$rightsOutput='';
		    $share = (string) $this->l10n->t('Share Addressbook');
			$bShared = false;
		 
			if($addressbookInfo['active']){
				 $checked = 'checked="checked"';
			}
		 	if($idActiveAddressbook === $addressbookInfo['id']) {
		 		$activeClass='isActiveABook';
			}
			
			$sync = '';
			//Ios syncs only one Addressbook
			
			if($this->configInfo->getUserValue($this->userId, $this->appName, 'syncaddrbook') === $addressbookInfo['uri']){
				$sync = ' <i class="ioc ioc-info toolTip" title="'.$this->l10n->t('Is active sync Book on Mac/ IOS').'"></i> ';
			}
			
		 	if((is_array($mySharees) && array_key_exists($addressbookInfo['id'], $mySharees)) && $mySharees[$addressbookInfo['id']]['myShare']) {
		 	    $share = '<b>'.(string) $this->l10n->t('Shared with').'</b><br>'.$mySharees[$addressbookInfo['id']]['shareTypeDescr']; 	
		    	$bShared =true; 
			}
			
		     $displayName='<span  class="groupname">'.$addressbookInfo['displayname'].$sync.'</span>';
			 
		 	 if($addressbookInfo['userid'] !== $this->userId){
  	        	$rightsOutput=ContactsApp::permissionReader($addressbookInfo['permissions']);	
  	          	$displayName='<span class="toolTip groupname" title="'.$notice.'('.$rightsOutput.')">'.$addressbookInfo['displayname'].' (' .(string) $this->l10n->t('by') . ' ' .$addressbookInfo['userid'].')</span>';
 		    }
			 
			$checkBox='<input class="regular-checkbox isActiveAddressbook" data-id="'.$addressbookInfo['id'].'" style="float:left;" id="edit_active_'.$addressbookInfo['id'].'" type="checkbox" '.$checked.' /><label style="float:left;margin-top:4px;margin-right:5px;" for="edit_active_'.$addressbookInfo['id'].'"></label>';
			$shareLink='';
		  
		  if($addressbookInfo['permissions'] & \OCP\PERMISSION_SHARE && $bShareApi === 'yes') {
			  $addCss = '';
			  if($bShared === true){
			  	 $addCss ='opacity:1';
			  }	 
			  $shareLink='<a href="#" class="share icon-share toolTip" 
			  	data-item-type="'.ContactsApp::SHAREADDRESSBOOK.'" 
			    data-item="'.ContactsApp::SHAREADDRESSBOOKPREFIX.$addressbookInfo['id'].'" 
			    data-link="false"
			    data-title="'.$addressbookInfo['displayname'].'"
				data-possible-permissions="'.$addressbookInfo['permissions'].'"
				title="'.$share.'"
				style="position:absolute;float:right;right:22px;margin-top:-6px;display:block;height:30px;width:30px;'.$addCss.'"
				>
				</a>';
		  }
						  
		 	$output.='<li class="dropcontainerAddressBook '.$activeClass.'" data-adrbid="'.$addressbookInfo['id'].'"  data-perm="'.$addressbookInfo['permissions'].'">'.$checkBox.$displayName.$shareLink.'<span class="groupcounter">'.$countCardsAddressbooks[$addressbookInfo['id']].'</span></li>';

		 }
		
		return $output;
		
	}
	
	
	/**
     * @NoAdminRequired
     */
    public function addIosGroupsSupport() {
    	
		$pActive = $this -> params('active');
		
		$this->configInfo->setUserValue($this->userId, $this->appName, 'iossupport',$pActive);
		$params = [
			'status' => 'success',
			'active' => $pActive
		];
		
		$response = new JSONResponse($params);
		return $response;
	}
	
	/**
     * @NoAdminRequired
     */
    public function prepareIosGroups() {
    	$pGroups = $this -> params('agroups');
		$pAddrBookId = $this -> params('aid');
		
		if($pAddrBookId >0){
			$existIosGroups =ContactsApp::getIosGroups();
			
			$iCountExist=count($pGroups);
			$iCountIosExist=count($existIosGroups);
			 
			if($iCountExist < $iCountIosExist){
				//Group Delete
				
				foreach($existIosGroups as $key => $value){
					if(!array_key_exists($key,$pGroups)){
						VCard::delete($value['id']);
					}
				}
			
			}
			
			if($iCountExist > $iCountIosExist){
				//Group Added
			
				$newGroup=array();
				foreach($pGroups as $key => $value){
					if(!array_key_exists($key,$existIosGroups)){
						$newGroup[]=$key;
						
					}
				}
			
				foreach($newGroup as $val){
						$uid = substr(md5(rand().time()), 0, 10);
						$appinfo = \OCP\App::getAppInfo($this->appName);
					    $appversion = \OCP\App::getAppVersion($this->appName);
					    $prodid = '-//ownCloud//NONSGML '.$appinfo['name'].' '.$appversion.'//EN';
						
						$vcard = new \Sabre\VObject\Component\VCard(array(
						    'PRODID'  => $prodid,
						     'VERSION'   => '3.0',
						     'UID'=>$uid
						));
						
						$vcard->N=$val;
						$vcard->FN=$val;
						//X-ADDRESSBOOKSERVER-KIND:group
						$vcard->{'X-ADDRESSBOOKSERVER-KIND'}='group';
						
						$id = VCard::add($pAddrBookId, $vcard, null, true);
					
				}
			}
			$params = [
			'status' => 'success',
		];
		
		$response = new JSONResponse($params);
		return $response;
		
		}//END $addrBk
    }
	
	/**
     * @NoAdminRequired
     */
    public function activate() {
    	
		$pId = $this -> params('id');
		$pActive = $this -> params('active');	
	
		try {
			$book =Addressbook::find($pId); // is owner access check
		} catch(Exception $e) {
			
		}
		
		if(!Addressbook::setActive($pId, $pActive)) {
			\OCP\Util::writeLog($this->appName,'Error activating addressbook: '. $pId,\OCP\Util::ERROR);
			
			$params = [
			'status' => 'error',
			'data' =>[
				'message' =>(string) $this->l10n->t('Error (de)activating addressbook.'),
			]];
				
			$response = new JSONResponse($params);
			return $response;
		}
		
		$params = [
			'status' => 'success',
			'active' => Addressbook::isActive($pId),
			'id' => $pId,
			'addressbook'   => $book
		];
		
		$response = new JSONResponse($params);
		return $response;
    }
	
	 /**
     * @NoAdminRequired
     */
    public function add() {
    		
    	$pName = $this -> params('name');
		$pDescription = $this -> params('description');
		$pName = isset($pName)?trim(strip_tags($pName)) : null;
		$description = isset($pDescription)? trim(strip_tags($pDescription)) : null;
		if(is_null($pName)) {
			$msg= 'Cannot add addressbook with an empty name.';
		}
		$bookid = Addressbook::add($this->userId, $pName, $pDescription);
		if(!$bookid) {
			$msg= 'Error adding addressbook: '.$pName;
		}
		
		if(!Addressbook::setActive($bookid, 1)) {
			$msg= 'Error activating addressbook.';
		}
		$addressbook =Addressbook::find($bookid);
		
		$params = [
		'status' => 'success',
		'data' =>[
			'addressbook' =>$addressbook
		]];
		
		$response = new JSONResponse($params);
		return $response;
    }
	
	 /**
     * @NoAdminRequired
     */
    public function update() {
    	$pId = $this -> params('id');
		$pActive = $this -> params('active');	
    	$pName = $this -> params('name');
		$pDescription = $this -> params('description');
		$pName = isset($pName)?trim(strip_tags($pName)) : null;
		$description = isset($pDescription)? trim(strip_tags($pDescription)) : null;
		
		if(!$pId) {
			$msg= (string) $this->l10n->t('id is not set.');
		}
		
		if(!$pName) {
			$msg= (string) $this->l10n->t('Cannot update addressbook with an empty name.');
		}
		
		try {
			Addressbook::edit($pId, $pName, $pDescription);
		} catch(Exception $e) {
			//bailOut($e->getMessage());
		}
		
		if(!Addressbook::setActive($pId,$pActive)) {
			$msg= (string) $this->l10n->t('Error (de)activating addressbook.');	
		}
		
		$addressbook = Addressbook::find($pId);
		
		$params = [
		'status' => 'success',
		'data' =>[
			'addressbook' =>$addressbook
		]];
		
		$response = new JSONResponse($params);
		return $response;
    }
	
	 /**
     * @NoAdminRequired
     */
    public function delete() {
    	$pId = $this -> params('id');
		if(!$pId) {
			$msg= (string) $this->l10n->t('id is not set.');
		}
		
		try {
			Addressbook::delete($pId);
		} catch(Exception $e) {
			//bailOut($e->getMessage());
		}
		
		$params = [
		'status' => 'success',
		'data' =>[
			'id' =>$pId
		]];
		
		$response = new JSONResponse($params);
		return $response;

    }

 	/**
     * @NoAdminRequired
     */
    public function getCategories() {
    		
    	$aCountGroups=ContactsApp::getCounterGroups();
		
		 $checkCat=ContactsApp::loadTags();
		 
		 $checkCat['tagslist'][]=array('name' =>(string) $this->l10n->t('All'), 'bgcolor' => '#ccc','color' => '#000','id' => 'all');
		 $checkCat['tagslist'][]=array('name' =>(string) $this->l10n->t('Not in group'), 'bgcolor' => '#ccc','color' => '#000','id' => 'none');
		 
			$checkCatTagsList='';
			
			$aSortOrderGroups='';
			if($sortOrderGroups = $this->configInfo->getUserValue($this->userId, $this->appName, 'sortorder')){
				
				$aSortOrderGroupsTmp=json_decode($sortOrderGroups, true);
				$counter =0;
				foreach($aSortOrderGroupsTmp as $sortInfo){
				//	\OCP\Util::writeLog($this->appName,'SORT: '.$sortInfo, \OCP\Util::DEBUG);	
					$aSortOrderGroups[$sortInfo]=$counter;
					$counter++;
				}
			}
			$counter=0;
			foreach($checkCat['tagslist'] as $tag){
				$iCount=0;
				if($aCountGroups != '' && array_key_exists((string)$tag['name'], $aCountGroups)){
					$iCount=$aCountGroups[$tag['name']];
				}	
				if($tag['id'] === 'fav'){	
					$iCount=isset($aCountGroups['favo'])?$aCountGroups['favo']:0;
				}
				$sortOrder=$tag['id'];
				if($aSortOrderGroups !='' && array_key_exists($tag['id'], $aSortOrderGroups)){
					$sortOrder=$aSortOrderGroups[$tag['id']];
				}	
				$checkCatTagsList[$sortOrder]=array('id'=>$tag['id'],'name'=>$tag['name'],'color'=>$tag['color'],'bgcolor'=>$tag['bgcolor'],'icount'=>$iCount);
			    $counter++;
			}
			
			$params = [
			'status' => 'success',
			'data' =>[
				'groups' => $checkCatTagsList
			]];
			
			$response = new JSONResponse($params);
			return $response;
			
    }

	/**
     * @NoAdminRequired
     */
    public function saveSortOrderGroups() {
    	$pValue = $this -> params('jsortorder');

		$this->configInfo->setUserValue($this->userId, $this->appName, 'sortorder', json_encode($pValue));
		
		$params = [
		'status' => 'success',
		];
		
		$response = new JSONResponse($params);
		return $response;
		
		
    }
}