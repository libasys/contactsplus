<?php
/**
 * ownCloud - Addressbook
 *
 * @author Thomas Tanghus
 * @copyright 2012 Thomas Tanghus (thomas@tanghus.net)
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

/**
 * This class overrides Sabre_CardDAV_Card::getACL()
 * to return read/write permissions based on user and shared state.
*/
namespace OCA\ContactsPlus\Connector\Sabre\Carddav;

use \OCA\ContactsPlus\Addressbook as AddrBook;
use \OCA\ContactsPlus\App as ContactsApp;

class Card extends \Sabre\CardDAV\Card {

   


	/**
	* Returns a list of ACE's for this node.
	*
	* Each ACE has the following properties:
	*   * 'privilege', a string such as {DAV:}read or {DAV:}write. These are
	*     currently the only supported privileges
	*   * 'principal', a url to the principal who owns the node
	*   * 'protected' (optional), indicating that this ACE is not allowed to
	*      be updated.
	*
	* @return array
	*/
	public function getACL() {

		$readprincipal = $this->getOwner();
		$writeprincipal = $this->getOwner();
		$uid = AddrBook::extractUserID($this->getOwner());

		if($uid !== \OCP\USER::getUser()) {
			$sharedAddressbook = \OCP\Share::getItemSharedWithBySource(ContactsApp::SHAREADDRESSBOOK,ContactsApp::SHAREADDRESSBOOKPREFIX.$this->addressBookInfo['id']);
			if ($sharedAddressbook && ($sharedAddressbook['permissions'] & \OCP\PERMISSION_READ)) {
				$readprincipal = 'principals/' . \OCP\USER::getUser();
			}
			if ($sharedAddressbook && ($sharedAddressbook['permissions'] & \OCP\PERMISSION_UPDATE)) {
				$writeprincipal = 'principals/' . \OCP\USER::getUser();
			}
		}

		return array(
			array(
				'privilege' => '{DAV:}read',
				'principal' => $readprincipal,
				'protected' => true,
			),
			array(
				'privilege' => '{DAV:}write',
				'principal' => $writeprincipal,
				'protected' => true,
			),

		);

	}

}