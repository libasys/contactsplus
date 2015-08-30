<?php
/**
 * ownCloud - ContactsPlus
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
 
namespace OCA\ContactsPlus\AppInfo;

use OC\AppFramework\Utility\SimpleContainer;
use \OCP\AppFramework\App;
use \OCP\Share;
use \OCP\IContainer;
use OCP\AppFramework\IAppContainer;

use \OCA\ContactsPlus\Controller\PageController;
use \OCA\ContactsPlus\Controller\PhotoController;
use \OCA\ContactsPlus\Controller\ContactsController;
use \OCA\ContactsPlus\Controller\ExportController;
use \OCA\ContactsPlus\Controller\ImportController;
use \OCA\ContactsPlus\Controller\AddressbookController;
use \OCA\ContactsPlus\Controller\ContactsApiController;


class Application extends App {
	
	 /**
	 * An array holding the current users address books.
	 * @var array
	 */
	protected static $addressBooks = array();
	
	public function __construct (array $urlParams=array()) {
		
		parent::__construct('contactsplus', $urlParams);
        $container = $this->getContainer();
	
	
		$container->registerService('PageController', function(IContainer $c) {
			return new PageController(
			$c->query('AppName'),
			$c->query('Request'),
			$c->query('UserId'),
			$c->query('L10N'),
			$c->query('Settings')
			);
		});
		
		$container->registerService('PhotoController', function(IContainer $c) {
			return new PhotoController(
			$c->query('AppName'),
			$c->query('Request'),
			$c->query('L10N')
			);
		});
		
		
		$container->registerService('ContactsController', function(IContainer $c) {
			return new ContactsController(
			$c->query('AppName'),
			$c->query('Request'),
			$c->query('UserId'),
			$c->query('L10N'),
			$c->query('Settings')
			);
		});
		
		$container->registerService('ExportController', function(IContainer $c) {
			return new ExportController(
			$c->query('AppName'),
			$c->query('Request'),
			$c->query('UserId'),
			$c->query('L10N'),
			$c->query('Settings')
			);
		});
		
		$container->registerService('ImportController', function(IContainer $c) {
			return new ImportController(
			$c->query('AppName'),
			$c->query('Request'),
			$c->query('UserId'),
			$c->query('L10N'),
			$c->query('Settings')
			);
		});
		
		
		$container->registerService('AddressbookController', function(IContainer $c) {
			return new AddressbookController(
			$c->query('AppName'),
			$c->query('Request'),
			$c->query('UserId'),
			$c->query('L10N'),
			$c->query('Settings')
			);
		});
		/*
		$container->registerService('ContactsApiController', function(IContainer $c) {
			return new ContactsApiController(
			$c->query('AppName'),
			$c->query('Request')
			);
		});*/
		
          /**
		 * Core
		 */
		 
		 $container->registerService('URLGenerator', function(IContainer $c) {
			/** @var \OC\Server $server */
			$server = $c->query('ServerContainer');
			return $server->getURLGenerator();
		});
		 
		$container -> registerService('UserId', function(IContainer $c) {
			$server = $c->query('ServerContainer');

			$user = $server->getUserSession()->getUser();
			return ($user) ? $user->getUID() : '';
			
		});
		
		$container -> registerService('L10N', function(IContainer $c) {
			return $c -> query('ServerContainer') -> getL10N($c -> query('AppName'));
		});
		
		$container->registerService('Settings', function($c) {
			return $c->query('ServerContainer')->getConfig();
		});
		
		$container->registerService('Session', function (IAppContainer $c) {
			return $c->getServer()
					 ->getSession();
			}
		);
		 $container->registerService('Token', function (IContainer $c) {
			return $c->query('Request') ->getParam('token');
			}
		);
	}
  
   

}

