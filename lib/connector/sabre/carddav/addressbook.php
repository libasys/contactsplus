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
 * This class overrides __construct to get access to $addressBookInfo and
 * $carddavBackend, Sabre_CardDAV_AddressBook::getACL() to return read/write
 * permissions based on user and shared state and it overrides
 * Sabre_CardDAV_AddressBook::getChild() and Sabre_CardDAV_AddressBook::getChildren()
 * to instantiate OC_Connector_Sabre_CardDAV_Cards.
*/
namespace OCA\ContactsPlus\Connector\Sabre\Carddav;

use \OCA\ContactsPlus\Addressbook as AddrBook;
use \OCA\ContactsPlus\App as ContactsApp;

class AddressBook extends \Sabre\CardDAV\AddressBook {

	

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
		$createprincipal = $this->getOwner();
		$deleteprincipal = $this->getOwner();
		$uid = AddrBook::extractUserID($this->getOwner());
		
		//\OCP\Config::setUserValue($uid, 'contactsplus', 'syncaddrbook', $this->addressBookInfo['uri']);	
		
		$readWriteACL = array(
			array(
				'privilege' => '{DAV:}read',
				'principal' => 'principals/' . \OCP\User::getUser(),
				'protected' => true,
			),
			array(
				'privilege' => '{DAV:}write',
				'principal' => 'principals/' . \OCP\User::getUser(),
				'protected' => true,
			),
		);

		if($uid !== \OCP\USER::getUser()) {
			$sharedAddressbook = \OCP\Share::getItemSharedWithBySource(ContactsApp::SHAREADDRESSBOOK, ContactsApp::SHAREADDRESSBOOKPREFIX.$this->addressBookInfo['id']);
			if($sharedAddressbook) {
				if(($sharedAddressbook['permissions'] & \OCP\PERMISSION_CREATE)
					&& ($sharedAddressbook['permissions'] & \OCP\PERMISSION_UPDATE)
					&& ($sharedAddressbook['permissions'] & \OCP\PERMISSION_DELETE)
				) {
					return $readWriteACL;
				}
				if ($sharedAddressbook['permissions'] & \OCP\PERMISSION_CREATE) {
					$createprincipal = 'principals/' . \OCP\USER::getUser();
				}
				if ($sharedAddressbook['permissions'] & \OCP\PERMISSION_READ) {
					$readprincipal = 'principals/' . \OCP\USER::getUser();
				}
				if ($sharedAddressbook['permissions'] & \OCP\PERMISSION_UPDATE) {
					$writeprincipal = 'principals/' . \OCP\USER::getUser();
				}
				if ($sharedAddressbook['permissions'] & \OCP\PERMISSION_DELETE) {
					$deleteprincipal = 'principals/' . \OCP\USER::getUser();
				}
			}
		} else {
			return parent::getACL();
		}

		return array(
			array(
				'privilege' => '{DAV:}read',
				'principal' => $readprincipal,
				'protected' => true,
			),
			array(
				'privilege' => '{DAV:}write-content',
				'principal' => $writeprincipal,
				'protected' => true,
			),
			array(
				'privilege' => '{DAV:}bind',
				'principal' => $createprincipal,
				'protected' => true,
			),
			array(
				'privilege' => '{DAV:}unbind',
				'principal' => $deleteprincipal,
				'protected' => true,
			),
		);

	}

	function getSupportedPrivilegeSet() {

		return array(
			'privilege'  => '{DAV:}all',
			'abstract'   => true,
			'aggregates' => array(
				array(
					'privilege'  => '{DAV:}read',
					'aggregates' => array(
						array(
							'privilege' => '{DAV:}read-acl',
							'abstract'  => true,
						),
						array(
							'privilege' => '{DAV:}read-current-user-privilege-set',
							'abstract'  => true,
						),
					),
				), // {DAV:}read
				array(
					'privilege'  => '{DAV:}write',
					'aggregates' => array(
						array(
							'privilege' => '{DAV:}write-acl',
							'abstract'  => true,
						),
						array(
							'privilege' => '{DAV:}write-properties',
							'abstract'  => true,
						),
						array(
							'privilege' => '{DAV:}write-content',
							'abstract'  => false,
						),
						array(
							'privilege' => '{DAV:}bind',
							'abstract'  => false,
						),
						array(
							'privilege' => '{DAV:}unbind',
							'abstract'  => false,
						),
						array(
							'privilege' => '{DAV:}unlock',
							'abstract'  => true,
						),
					),
				), // {DAV:}write
			),
		); // {DAV:}all

	}

	/**
	* Returns a card
	*
	* @param string $name
	* @return OC_Connector_Sabre_DAV_Card
	*/
	public function getChild($name) {

		$obj = $this->carddavBackend->getCard($this->addressBookInfo['id'],$name);
		if (!$obj) throw new \Sabre\DAV\Exception\NotFound('Card not found');
		return new Card($this->carddavBackend,$this->addressBookInfo,$obj);

	}

	/**
	* Returns the full list of cards
	*
	* @return array
	*/
	public function getChildren() {

		$objs = $this->carddavBackend->getCards($this->addressBookInfo['id']);
		$children = array();
		foreach($objs as $obj) {
			$children[] = new Card($this->carddavBackend,$this->addressBookInfo,$obj);
		}
		return $children;

	}

}
