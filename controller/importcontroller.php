<?php
/**
 * ownCloud - Calendar
 *
 * @author Sebastian Doell
 * @copyright 2015 sebastian doell sebastian@libasys.de
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


use OCA\ContactsPlus\App as ContactsApp;
use OCA\ContactsPlus\VCard;
use OCA\ContactsPlus\Addressbook;
use  \OCA\ContactsPlus\Import;
use Sabre\VObject;

use \OCP\AppFramework\Controller;
use \OCP\AppFramework\Http\JSONResponse;
use \OCP\AppFramework\Http\TemplateResponse;
use \OCP\IRequest;
use \OCP\Share;
use \OCP\IConfig;

class ImportController extends Controller {

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
	public function getImportDialogTpl() {
			
		$pPath = $this -> params('path');	
		$pFile = $this -> params('filename');
		
		$params=[
			'path' => $pPath,
			'filename' => $pFile,
		];
		
		$response = new TemplateResponse($this->appName, 'part.import',$params, '');  
        
        return $response;
		
	}
	
	
	/**
	 * @NoAdminRequired
	 */
	public function importVcards() {
		$pProgresskey = $this -> params('progresskey');
		$pGetprogress = $this -> params('getprogress');
		$pPath = $this -> params('path');
		$pFile = $this -> params('file');
		$pMethod = $this -> params('method');
		$pAddressbook = $this -> params('addressbookname');
		$pIsDragged = $this->params('isDragged');
		$pId = $this -> params('id');
		
		$pOverwrite = $this -> params('overwrite');
		\OC::$server->getSession()->close();
		
		
		if (isset($pProgresskey) && isset($pGetprogress)) {
				$params = [
					'status' => 'success',
					'percent' => \OC::$server->getCache()->get($pProgresskey),
				];
				$response = new JSONResponse($params);
				return $response;	
		}
		
		if($pIsDragged === 'true') {
			//OCP\JSON::error(array('error'=>'404'));
			$file = explode(',', $pFile);
			$file = end($file);
			$file = base64_decode($file);
		}else{
			$file = \OC\Files\Filesystem::file_get_contents($pPath . '/' . $pFile);
		}
		
		if(!$file) {
				$params = [
					'status' => 'error',
					'error' => '404',
				];
				$response = new JSONResponse($params);
				return $response;	
			
		}
		$file = \Sabre\VObject\StringUtil::convertToUTF8($file);
		
		$import = new Import($file);
		$import->setUserID($this->userId);
		//$import->setTimeZone(CalendarApp::$tz);
		$import->enableProgressCache();
		$import->setProgresskey($pProgresskey);
		
		\OCP\Util::writeLog($this->appName,' PROG: '.$pProgresskey, \OCP\Util::DEBUG);
		
		if(!$import->isValid()) {
			$params = [
					'status' => 'error',
					'error' => 'notvalid',
				];
				$response = new JSONResponse($params);
				return $response;	
		}
		
		$newAddressbook = false;
		
		if($pMethod == 'new') {
			$id = Addressbook::add($this->userId,	$pAddressbook);	
			
			if($id) {
				Addressbook::setActive($id, 1);
				$newAddressbook = true;
			}
		}else{
			$id=	$pId;
			Addressbook::find($id);
			
			$import->setOverwrite($pOverwrite);
		}
		//\OCP\Util::writeLog($this->appName,' METHOD: '.$pMethod.'ID:'.$id, \OCP\Util::DEBUG);
		$import->setAddressbookId($id);
		try{
			$import->import();
		
			
		}catch (\Exception $e) {
			$params = [
					'status' => 'error',
					'message' => $this->l10n -> t('Import failed'),
				];
				$response = new JSONResponse($params);
				return $response;		
		}
		$count = $import->getCount();
		if($count == 0) {
			if($newAddressbook) {
				Addressbook::delete($id);
			}
			$params = [
				'status' => 'error',
				'message' => $this->l10n -> t('The file contained either no vcard or all vcards are already saved in your addessbook.'),
			];
			$response = new JSONResponse($params);
			return $response;
		}else{
			if($newAddressbook) {
				$params = [
					'status' => 'success',
					'message' => $count . ' ' . $this->l10n -> t('vcards has been saved in the new addressbook'). ' ' .  strip_tags($pAddressbook),
				];
				
				$response = new JSONResponse($params);
				return $response;		
			}else{
				$params = [
					'status' => 'success',
					'message' => $count . ' ' . $this->l10n -> t('vcards has been saved in your addressbook'),
				];
				
				$response = new JSONResponse($params);
				return $response;		
			}
		}
		
	}

	/**
	 * @NoAdminRequired
	 */
	public function checkAddressbookExists() {
		$pAddressbook = $this -> params('addrbookname');	
		$id = Addressbook::checkIfExist($pAddressbook);	
		//\OCP\Util::writeLog($this->appName,'CHECK: '.$id, \OCP\Util::DEBUG);
		if($id){
			$params = [
			'status' => 'success',
			'message' => 'exists'
			];
			$response = new JSONResponse($params);
			return $response;
		}
	}
	
}