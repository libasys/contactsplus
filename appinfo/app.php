<?php
namespace OCA\ContactsPlus\AppInfo;
use \OCA\ContactsPlus\App as ContactsApp;	
use \OCA\ContactsPlus\AddressbookProvider;

$app = new Application();
$c = $app->getContainer();

$contactsAppName='contactsplus';
// add an navigation entr
$navigationEntry = function () use ($c) {
	return [
		'id' => $c->getAppName(),
		'order' => 1,
		'name' => $c->query('L10N')->t('Contacts+'),
		'href' => $c->query('URLGenerator')->linkToRoute($c->getAppName().'.page.index'),
		'icon' => $c->query('URLGenerator')->imagePath($c->getAppName(), 'contacts.svg'),
	];
};
$c->getServer()->getNavigationManager()->add($navigationEntry);
  
if (\OCP\User::isLoggedIn()) {
	\OCP\Util::addScript('contactsplus','search');
}

\OC::$server->getSearch()->registerProvider('OCA\ContactsPlus\Search\Provider', array('app' => $contactsAppName));


\OCP\Share::registerBackend(ContactsApp::SHAREADDRESSBOOK, 'OCA\ContactsPlus\Share\Backend\Addressbook');
\OCP\Share::registerBackend(ContactsApp::SHARECONTACT, 'OCA\ContactsPlus\Share\Backend\Contact');


\OCP\Util::connectHook('OCA\CalendarPlus', 'getSources', 'OCA\ContactsPlus\Hooks', 'getCalenderSources');
\OCP\Util::connectHook('OCA\CalendarPlus', 'getCalendars', 'OCA\ContactsPlus\Hooks', 'getBirthdayCalender');
\OCP\Util::connectHook('OCA\CalendarPlus', 'getEvents', 'OCA\ContactsPlus\Hooks', 'getBirthdayEvents');
\OCP\Util::connectHook('OC_User', 'post_deleteUser', '\OCA\ContactsPlus\Hooks', 'deleteUser');

if (\OCP\User::isLoggedIn() && !\OCP\App::isEnabled('contacts')) {
	$request = $c->query('Request');
	if (isset($request->server['REQUEST_URI'])) {
			
		$url = $request->server['REQUEST_URI'];
		if (preg_match('%index.php/apps/files(/.*)?%', $url)	|| preg_match('%index.php/s/(/.*)?%', $url)) {
		\OCP\Util::addscript($contactsAppName,'loader');
		}
	}
	
	
}


if (\OCP\User::isLoggedIn()) {
	$cm = \OC::$server->getContactsManager();
	
	$cm->register(function() use ($cm) {
		$myApp = new Application();
		$addressBooks = $myApp->getContainer()->query('AddressbookController')->getAddressBooksForCM();
		
		foreach ($addressBooks as $addressBook)  {
				$cm->registerAddressBook(new AddressbookProvider($addressBook));
			
		}
	});
}

