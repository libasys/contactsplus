<?php
/**
 * ownCloud - OCS API for local shares
 *
 * @author Bjoern Schiessle
 * @copyright 2013 Bjoern Schiessle schiessle@owncloud.com
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

namespace OCA\ContactsPlus\API;

class Local {

	/**
	 * get all shares
	 *
	 * @param array $params option 'file' to limit the result to a specific file/folder
	 * @return \OC_OCS_Result share information
	 */
	
	  private static $sItems = array();
	  const ITEM_TYPE = 'cpladdrbook';
	
	public static function getAllShares($params) {
		
		if (isset($_GET['shared_with_me']) && $_GET['shared_with_me'] !== 'false') {
				return self::getItemsSharedWithMe();
			}
		//Show all reshared options
		$bReshare=false;
		if(isset($_GET['reshares']) && $_GET['reshares'] === 'true'){
			$bReshare=true;
		}

		$shares = \OCP\Share::getItemShared(self::ITEM_TYPE, null);

		if ($shares === false) {
			return new \OC_OCS_Result(null, 404, 'could not get shares');
		} else {
			$reshares=array();	
			foreach ($shares as &$share) {
					if($bReshare === true){	
						self::checkReShare($share['item_source']);
					}	
			}
			
			if(count(self::$sItems)>0){
				$shares=array_merge($shares, self::$sItems);
			}
			
			return new \OC_OCS_Result($shares);
		}

	}
/**
	 * resolves reshares down to the last real share
	 * @param array $linkItem
	 * @return array file owner
	 */
	public static function checkReShare($itemsource){
			
			
			$getReshares = \OCP\DB::prepare("SELECT * FROM `*PREFIX*share` WHERE `item_source` = ?  AND `uid_owner` != ?  AND `item_type` =  ? AND `parent` != 'NULL' ");
			$items = $getReshares->execute(array($itemsource, \OCP\User::getUser(), self::ITEM_TYPE))->fetchAll();
			
			foreach($items as $reshare){
				$reshare['share_type'] = (int) $reshare['share_type'];
	
				if (isset($reshare['share_with']) && $reshare['share_with'] !== '') {
					$reshare['share_with_displayname'] = \OCP\User::getDisplayName($reshare['share_with']);
				}
				self::$sItems[$reshare['id']]=$reshare;
			}
	
	}
	
	/**
	 * get share information for a given share
	 *
	 * @param array $params which contains a 'id'
	 * @return \OC_OCS_Result share information
	 */
	public static function getShare($params) {

		$s = self::getShareFromId($params['id']);
		//Show all reshared options
		$bReshare=false;
		if(isset($_GET['reshares']) && $_GET['reshares'] === 'true'){
			$bReshare=true;
		}
		$params['itemSource'] = $s['item_source'];
		$params['itemType'] = $s['item_type'];
		$params['itemTarget'] = $s['item_target'];
		$params['specificShare'] = true;
		$params['reshare']=$bReshare;

		return self::collectShares($params);
	}

	/**
	 * collect all share information, either of a specific share or all
	 *        shares for a given path
	 * @param array $params
	 * @return \OC_OCS_Result
	 */
	private static function collectShares($params) {

		$itemSource = $params['itemSource'];
		$itemType = $params['itemType'];
		$getSpecificShare = isset($params['specificShare']) ? $params['specificShare'] : false;

		if ($itemSource !== null) {
			$shares = \OCP\Share::getItemShared($itemType, $itemSource);
			$receivedFrom = \OCP\Share::getItemSharedWithBySource($itemType, $itemSource);
			// if a specific share was specified only return this one
			if ($getSpecificShare === true) {
				$shareEE=array();	
				foreach ($shares as $share) {
					if ($share['id'] === (int) $params['id']) {
						
						$shareEE[] = $share;
						
						break;
					}
				}
				
				if($params['reshare'] === true){
					self::checkReShare($itemSource,$itemType);
				
					if(count(self::$sItems)>0){
						$shares=array_merge($shareEE, self::$sItems);
					}
				}

			} 

			if ($receivedFrom) {
				foreach ($shares as $key => $share) {
					$shares[$key]['received_from'] = $receivedFrom['uid_owner'];
					$shares[$key]['received_from_displayname'] = \OCP\User::getDisplayName($receivedFrom['uid_owner']);
				}
			}
		} else {
			$shares = null;
		}

		if ($shares === null || empty($shares)) {
			return new \OC_OCS_Result(null, 404, 'share doesn\'t exist');
		} else {
			return new \OC_OCS_Result($shares);
		}
	}

	/**
	 * get files shared with the user
	 * @return \OC_OCS_Result
	 */
	private static function getItemsSharedWithMe() {
		try	{
			$shares = \OCP\Share::getItemsSharedWith(self::ITEM_TYPE);
			
			$result = new \OC_OCS_Result($shares);
		} catch (\Exception $e) {
			$result = new \OC_OCS_Result(null, 403, $e->getMessage());
		}

		return $result;

	}


	/**
	 * get some information from a given share
	 * @param int $shareID
	 * @return array with: item_source, share_type, share_with, item_type, permissions
	 */
	private static function getShareFromId($shareID) {
		$sql = 'SELECT  `id`,`item_source`, `share_type`, `share_with`, `item_type`, `item_target`, `permissions`, `stime` FROM `*PREFIX*share` WHERE `id` = ? AND `item_type` = ?';
		$args = array($shareID,self::ITEM_TYPE);
		$query = \OCP\DB::prepare($sql);
		$result = $query->execute($args);

		if (\OCP\DB::isError($result)) {
			\OCP\Util::writeLog('contactsplus', \OCP\DB::getErrorMessage($result), \OCP\Util::ERROR);
			return null;
		}
		if ($share = $result->fetchRow()) {
			
				
			return $share;
		}

		return null;

	}

}
