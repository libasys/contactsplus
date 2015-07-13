<?php
/**
 * Copyright (c) 2011 Jakob Sack <mail@jakobsack.de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */




if(substr(OCP\Util::getRequestUri(),0,strlen(OC_App::getAppWebPath('contactsplus').'/carddav.php')) === OC_App::getAppWebPath('contactsplus'). '/carddav.php') {
	$baseuri = OC_App::getAppWebPath('contactsplus').'/carddav.php';
}

// only need authentication apps
$RUNTIME_APPTYPES=array('authentication');
OC_App::loadApps($RUNTIME_APPTYPES);

// Backends
$authBackend = new \OC\Connector\Sabre\Auth();
$principalBackend = new \OC\Connector\Sabre\Principal(
	\OC::$server->getConfig(),
	\OC::$server->getUserManager()
);



$carddavBackend   = new OCA\ContactsPlus\Connector\Sabre\Carddav\Backend();

// Root nodes
$principalCollection = new \Sabre\CalDAV\Principal\Collection($principalBackend);
$principalCollection->disableListing = true; // Disable listening

$addressBookRoot = new OCA\ContactsPlus\Connector\Sabre\Carddav\AddressBookRoot($principalBackend, $carddavBackend);
$addressBookRoot->disableListing = true; // Disable listening

$nodes = array(
	$principalCollection,
	$addressBookRoot,
	);

// Fire up server
$server = new \Sabre\DAV\Server($nodes);
$server->httpRequest->setUrl(\OC::$server->getRequest()->getRequestUri());
$server->setBaseUri($baseuri);


// Add plugins

$server->addPlugin(new \OC\Connector\Sabre\MaintenancePlugin());
$server->addPlugin(new \Sabre\DAV\Auth\Plugin($authBackend,'ownCloud'));
$server->addPlugin(new \Sabre\CardDAV\Plugin());
$server->addPlugin(new \Sabre\DAVACL\Plugin());
//$server->addPlugin(new \Sabre\DAV\Browser\Plugin(true)); // Show something in the Browser, but no upload
$server->addPlugin(new \Sabre\CardDAV\VCFExportPlugin());
$server->addPlugin(new \OC\Connector\Sabre\ExceptionLoggerPlugin('carddav', \OC::$server->getLogger()));
$server->addPlugin(new \OC\Connector\Sabre\AppEnabledPlugin(
	'contactsplus',
	OC::$server->getAppManager()
));


// And off we go!
$server->exec();
