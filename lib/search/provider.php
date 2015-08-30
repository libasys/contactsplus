<?php
/**
 * ownCloud
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

namespace OCA\ContactsPlus\Search;

use \OCA\ContactsPlus\Addressbook;
use \OCA\ContactsPlus\App as ContactsApp;
use \OCA\ContactsPlus\VCard;
/**
 * Provide search results from the 'calendar' app
 */
class Provider extends \OCP\Search\Provider {

	/**
	 * 
	 * @param string $query
	 * @return \OCP\Search\Result
	 */
	function search($query) {
		$unescape = function($value) {
			return strtr($value, array('\,' => ',', '\;' => ';'));
		};

		$searchresults = array(	);
		$results = ContactsApp::searchProperties($query);
		$l = \OC::$server->getL10N(ContactsApp::$appname);
		
		foreach($results as $result) {
			$vcard = VCard::find($result['id']);
			
			$link = '#'.intval($vcard['id']);
		
			$props = '';
			
			
			foreach(array('EMAIL', 'NICKNAME', 'ORG','TEL') as $searchvar) {
				if(isset($result['name']) &&  $searchvar == $result['name']) {
					//\OCP\Util::writeLog(ContactsApp::$appname,'FOUND id: ' . $result['value'], \OCP\Util::DEBUG);	
					$props .= $searchvar.':'.$result['value'].' ';
				}
			}
			
			
			$returnData['id']=$vcard['id'];
			$returnData['description']=$vcard['fullname'].' '.$props;
			$returnData['link']=$link;
					
		     $results[]=new Result($returnData);
			
			
		}
		return $results;
	}
}
