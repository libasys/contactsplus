<?php
namespace OCA\ContactsPlus\Controller;

use \OCP\AppFramework\ApiController;
use \OCP\IRequest;
use \OCP\AppFramework\Http;
use  OCA\ContactsPlus\AppInfo\Application;
//use  OCA\ContactsPlus\AddressBook;
//use Sabre\VObject;

class ContactsApiController extends ApiController {

    public function __construct($appName, IRequest $request) {
        parent::__construct($appName, $request);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @CORS
     */
    public function version() {
		$version = '1.0.2';
		 return ['version' => $version];
    }
	
	 /**
     * @NoAdminRequired
     * @NoCSRFRequired
	  * 
     * @CORS
     */
	public function contact($id){
			
		$app = new Application(); 
 		$c = $app->getContainer();
		$contactsController = $c->query('ContactsController');
 	
		return $contactsController->ApiContactData($id);
		
		//test http://{owncloudomain}/ocs/v1.php/apps/contactsplus/api/v1/contact/283
		/*
		 $contact = VCard::find($id);
		 if(!is_null($contact['carddata'])){
		 	$vcard = VObject\Reader::read($contact['carddata']);
		 	$details = VCard::structureContact($vcard);
		
			 $addrInfo = AddressBook::find($contact['addressbookid']);
			 $details['addressbook'] = $addrInfo['displayname'];
			 $details['addressbookuri'] = $addrInfo['uri'];
		  }
		 
		 return $details;*/
	}
}