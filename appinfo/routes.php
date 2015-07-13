<?php

use \OCA\ContactsPlus\AppInfo\Application;

$application = new Application();
$application->registerRoutes($this, ['routes' => [
	['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
	['name' => 'photo#getImageFromCloud',	'url' => '/getimagefromcloud',	'verb' => 'GET'],
	['name' => 'photo#cropPhoto',	'url' => '/cropphoto',	'verb' => 'POST'],
	['name' => 'photo#saveCropPhotoContact',	'url' => '/savecropphotocontact',	'verb' => 'POST'],
	['name' => 'photo#uploadPhoto',	'url' => '/uploadphoto',	'verb' => 'POST'],
	['name' => 'photo#deletePhoto',	'url' => '/deletephoto',	'verb' => 'GET'],
	['name' => 'photo#clearPhotoCache',	'url' => '/clearphotocache',	'verb' => 'POST'],
	['name' => 'contacts#getNewFormContact', 'url' => '/getnewformcontact', 'verb' => 'POST'],
	['name' => 'contacts#newContactSave', 'url' => '/newcontactsave', 'verb' => 'POST'],
	['name' => 'contacts#getEditFormContact', 'url' => '/geteditformcontact', 'verb' => 'POST'],
	['name' => 'contacts#editContactSave', 'url' => '/editcontactsave', 'verb' => 'POST'],
	['name' => 'contacts#showContact', 'url' => '/showcontact', 'verb' => 'POST'],	
	['name' => 'contacts#deleteContact', 'url' => '/deletecontact', 'verb' => 'POST'],	
	['name' => 'contacts#deleteContactFromGroup', 'url' => '/deletecontactfromgroup', 'verb' => 'POST'],
	['name' => 'contacts#addProbertyToContact', 'url' => '/addprobertytocontact', 'verb' => 'POST'],
	['name' => 'contacts#copyContact', 'url' => '/copycontact', 'verb' => 'POST'],
	['name' => 'contacts#moveContact', 'url' => '/movecontact', 'verb' => 'POST'],
	['name' => 'contacts#getContactCards', 'url' => '/getcontactcards', 'verb' => 'POST'],
	['name' => 'export#exportContacts', 'url' => '/exportcontacts', 'verb' => 'GET'],	
	['name' => 'export#exportBirthdays', 'url' => '/exportbirthdays', 'verb' => 'GET'],
	['name' => 'import#getImportDialogTpl', 'url' => '/getimportdialogtplcontacts', 'verb' => 'POST'],	
	['name' => 'import#checkAddressbookExists', 'url' => '/checkaddressbookexists', 'verb' => 'POST'],
	['name' => 'import#importVcards', 'url' => '/importvcards', 'verb' => 'POST'],		
	['name' => 'addressbook#getAddressBooks', 'url' => '/getaddressbooks', 'verb' => 'GET'],	
	['name' => 'addressbook#add', 'url' => '/addaddrbook', 'verb' => 'POST'],	
	['name' => 'addressbook#update', 'url' => '/updateaddrbook', 'verb' => 'POST'],	
	['name' => 'addressbook#delete', 'url' => '/deleteaddrbook', 'verb' => 'POST'],
	['name' => 'addressbook#activate', 'url' => '/activateaddrbook', 'verb' => 'POST'],
	['name' => 'addressbook#getCategories', 'url' => '/getcategoriesaddrbook', 'verb' => 'POST'],
	['name' => 'addressbook#addIosGroupsSupport', 'url' => '/addiosgroupssupport', 'verb' => 'POST'],
	['name' => 'addressbook#prepareIosGroups', 'url' => '/prepareiosgroups', 'verb' => 'POST'],	
	['name' => 'addressbook#saveSortOrderGroups', 'url' => '/savesortordergroups', 'verb' => 'POST'],			
	]]);


\OCP\API::register('get',
		'/apps/kontakte/api/v1/shares',
		array('\OCA\ContactsPlus\API\Local', 'getAllShares'),
		'contactsplus');
\OCP\API::register('get',
		'/apps/kontakte/api/v1/shares/{id}',
		array('\OCA\ContactsPlus\API\Local', 'getShare'),
		'contactsplus');	
