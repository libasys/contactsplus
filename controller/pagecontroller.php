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

use \OCP\AppFramework\Controller;
use \OCP\AppFramework\Http\TemplateResponse;
use \OCP\IRequest;
use \OCP\IL10N;
use \OCP\IConfig;
use OCA\ContactsPlus\Addressbook;
use OCA\ContactsPlus\App as ContactsApp;
/**
 * Controller class for main page.
 */
class PageController extends Controller {
	
	private $userId;
	private $l10n;
	private $configInfo;
	

	public function __construct($appName, IRequest $request,  $userId, IL10N $l10n, IConfig $settings) {
		parent::__construct($appName, $request);
		$this -> userId = $userId;
		$this->l10n = $l10n;
		$this->configInfo = $settings;
	}
	
	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index() {
			
		
		
		$iosSupport= $this->configInfo->getUserValue($this->userId, $this->appName,'iossupport');
		
		$activeView = $this->configInfo->getUserValue($this->userId, $this->appName,'view','listview');
		
		$lastSelectedBook = $this->configInfo->getUserValue($this->userId, $this->appName, 'currentbook', 0);
		
		$maxUploadFilesize = \OCP\Util::maxUploadFilesize('/');
		
		$addressbooks = Addressbook::all($this->userId);
		if(count($addressbooks) == 0) {
			Addressbook::addDefault($this->userId);
			$addressbooks = Addressbook::all($this->userId);
		}
		//ContactsApp::addingDummyContacts(1000);

		$params = [
			'uploadMaxFilesize' => $maxUploadFilesize,
			'uploadMaxHumanFilesize' => 	\OCP\Util::humanFileSize($maxUploadFilesize),
			'iossupport' => $iosSupport,
			'addressbooks' => $addressbooks,
			'activeView' => $activeView,
			'lastSelectedBook' => $lastSelectedBook,
		];
		
		$csp = new \OCP\AppFramework\Http\ContentSecurityPolicy();
		$csp->addAllowedImageDomain('\'self\'');
		$csp->addAllowedImageDomain('data:');
		$csp->addAllowedImageDomain('*');
		$csp->addAllowedFrameDomain('*');	
		$response = new TemplateResponse($this->appName, 'index');
		$response->setContentSecurityPolicy($csp);
		$response->setParams($params);

		return $response;
	}
}