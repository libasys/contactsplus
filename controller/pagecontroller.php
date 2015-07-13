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
			
		\OCP\Util::addscript('core','tags');

		\OCP\Util::addStyle($this->appName,'style');
		\OCP\Util::addStyle($this->appName, 'jquery.Jcrop');
		\OCP\Util::addStyle($this->appName, '3rdparty/fontello/css/animation');
		\OCP\Util::addStyle($this->appName, '3rdparty/fontello/css/fontello');
		\OCP\Util::addStyle($this->appName, '3rdparty/jquery.webui-popover');		
		\OCP\Util::addscript($this->appName, 'app');
		\OCP\Util::addscript($this->appName, '3rdparty/jquery.webui-popover');
		\OCP\Util::addscript($this->appName,'settings');
		\OCP\Util::addscript($this->appName,'loader');
		\OCP\Util::addscript($this->appName,'jquery.scrollTo.min');
		\OCP\Util::addscript($this->appName,'jquery.nicescroll.min');
		\OCP\Util::addscript('files', 'jquery.fileupload');
		\OCP\Util::addscript($this->appName, 'jquery.Jcrop');
		
		$iosSupport= $this->configInfo->getUserValue($this->userId, $this->appName,'iossupport');
		
		$maxUploadFilesize = \OCP\Util::maxUploadFilesize('/');
		
		$addressbooks = Addressbook::all($this->userId);
		if(count($addressbooks) == 0) {
			Addressbook::addDefault($this->userId);
			$addressbooks = Addressbook::all($this->userId);
		}
		//ContactsApp::addingDummyContacts(50);

		$params = [
			'uploadMaxFilesize' => $maxUploadFilesize,
			'uploadMaxHumanFilesize' => 	\OCP\Util::humanFileSize($maxUploadFilesize),
			'iossupport' => $iosSupport,
			'addressbooks' => $addressbooks,
		];
		
		$csp = new \OCP\AppFramework\Http\ContentSecurityPolicy();
		$csp->addAllowedImageDomain('*');
		$csp->addAllowedFrameDomain('*');	
		$response = new TemplateResponse($this->appName, 'index');
		$response->setContentSecurityPolicy($csp);
		$response->setParams($params);

		return $response;
	}
}