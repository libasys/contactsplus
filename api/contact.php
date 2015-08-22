<?php

namespace OCA\ContactsPlus\API;

use  OCA\ContactsPlus\VCard;
use  OCA\ContactsPlus\AddressBook;
use Sabre\VObject;
class Contact {
	
	public function getContact($params){
		//test http://{owncloudomain}/ocs/v1.php/apps/contactsplus/api/v1/contact/283
		
		 $contact = VCard::find($params['id']);
		 if(!is_null($contact['carddata'])){
		 	$vcard = VObject\Reader::read($contact['carddata']);
		 	$details = VCard::structureContact($vcard);
		
			 $addrInfo = AddressBook::find($contact['addressbookid']);
			 $details['addressbook'] = $addrInfo['displayname'];
			 $details['addressbookuri'] = $addrInfo['uri'];
		  }
		 
		 return new \OC_OCS_Result($details);
	}
	
}