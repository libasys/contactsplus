<?php
/**
 * ownCloud - Calendar
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
 
namespace OCA\ContactsPlus\Controller;


use OCA\ContactsPlus\App as ContactsApp;
use OCA\ContactsPlus\VCard;
use OCA\ContactsPlus\Addressbook;
use Sabre\VObject;

use \OCP\AppFramework\Controller;
use \OCP\AppFramework\Http\JSONResponse;
use \OCP\AppFramework\Http\DataDownloadResponse;
use \OCP\IRequest;
use \OCP\Share;
use \OCP\IConfig;

class ExportController extends Controller {

	private $userId;
	private $l10n;
	private $configInfo;

	public function __construct($appName, IRequest $request, $userId, $l10n, IConfig $settings) {
		parent::__construct($appName, $request);
		$this -> userId = $userId;
		$this->l10n = $l10n;
		$this->configInfo = $settings;
	}
	
	  /**
     * @NoAdminRequired
	 * @NoCSRFRequired
     */
	public function exportContacts(){
		$bookid=$this -> params('bookid');
		$bookid = isset($bookid) ? $bookid : null;
		
		$contactid=$this -> params('contactid');
		$contactid = isset($contactid) ? $contactid : null;
		
		$selectedids=$this -> params('selectedids');
		$selectedids = isset($selectedids) ? $selectedids : null;
		
		$nl = "\n";
		if(!is_null($bookid)) {
			try {
				$addressbook = Addressbook::find($bookid);
			} catch(Exception $e) {
				OCP\JSON::error(
					array(
						'data' => array(
							'message' => $e->getMessage(),
						)
					)
				);
				exit();
			}
			
			$start = 0;
			$ContactsOutput='';
			$batchsize = $this->configInfo->getUserValue($this->userId,$this->appName,'export_batch_size', 20);
			while($cardobjects = VCard::all($bookid, $start, $batchsize, array('carddata'))) {
				foreach($cardobjects as $card) {
					$ContactsOutput .= $card['carddata'] . $nl;
				}
				$start += $batchsize;
			}
			
			$name= str_replace(' ', '_', $addressbook['displayname']) . '.vcf';
			
			$response = new DataDownloadResponse($ContactsOutput, $name, 'text/directory');
			
			return $response;
			
		}elseif(!is_null($contactid)) {
			try {
				$data = VCard::find($contactid);
			} catch(Exception $e) {
				OCP\JSON::error(
					array(
						'data' => array(
							'message' => $e->getMessage(),
						)
					)
				);
				exit();
			}
			
			$name= str_replace(' ', '_', $data['fullname']) . '.vcf';
			
			$response = new DataDownloadResponse($data['carddata'], $name, 'text/vcard');
			return $response;
			
		}elseif(!is_null($selectedids)) {
			$selectedids = explode(',', $selectedids);
			$name= (string) $this->l10n->t('%d_selected_contacts', array(count($selectedids))) . '.vcf';
			
			$ContactsOutput='';				
			foreach($selectedids as $id) {
				try {
					$data = VCard::find($id);
					$ContactsOutput.= $data['carddata'] . $nl;
				} catch(Exception $e) {
					continue;
				}
			}
			$response = new DataDownloadResponse($ContactsOutput, $name, 'text/directory');
			return $response;
			
		}
		
	}
	
	  /**
     * @NoAdminRequired
	 * @NoCSRFRequired
     */
	public function exportBirthdays(){
		$bookid=$this -> params('aid');
		$bookid = isset($bookid) ? $bookid : null;
		
	  if(!is_null($bookid)){
		  $addressbook = Addressbook::find($bookid);
		  $aDefNArray=array('0'=>'fname','1'=>'lname','3'=>'title','4'=>'');
	      
		   foreach(VCard::all($bookid) as $contact) {
					try {
						$vcard = VObject\Reader::read($contact['carddata']);
						
					} catch (Exception $e) {
						continue;
						
					}
					
					$birthday = $vcard->BDAY;
					
					if ((string) $birthday) {
						
					$details = VCard::structureContact($vcard);
					
					$BirthdayTemp = new \DateTime($birthday);	
					$checkForm = $BirthdayTemp->format('d-m-Y');
					$temp = explode('-',$checkForm);	
					$getAge= $this->getAgeCalc($temp[2],$temp[1],$temp[0]);
					//$getAge=$BirthdayTemp->format('d-m-Y');
					$title=isset($vcard->FN)?strtr($vcard->FN->getValue(), array('\,' => ',', '\;' => ';')):'';
					
					$sNameOutput='';
					if(isset($details['N'][0]['value']) && count($details['N'][0]['value'])>0){
						foreach($details['N'][0]['value'] as $key => $val){
							if($val!='') {	
								$aNameOutput[$aDefNArray[$key]]=$val;
								
							}
						}
						//$sNameOutput=isset($aNameOutput['title'])?$aNameOutput['title'].' ':'';
						$sNameOutput.=isset($aNameOutput['lname'])?$aNameOutput['lname'].' ':'';
						$sNameOutput.=isset($aNameOutput['fname'])?$aNameOutput['fname'].' ':'';
						
						unset($aNameOutput);
					}
					
					if($sNameOutput=='') {$sNameOutput=$title;}
						
					$sTitle1 =(string)$this->l10n->t('%1$s (%2$s)',array($sNameOutput,$getAge));
					
					
					$aktYear=$BirthdayTemp->format('d-m');
					$aktYear=$aktYear.date('-Y');
					$start = new \DateTime($aktYear);
					$end = new \DateTime($aktYear.' +1 day');
					
					$vcalendar = new VObject\Component\VCalendar();
					$vevent = $vcalendar->createComponent('VEVENT');	
					$vevent->add('DTSTART');
					$vevent->DTSTART->setDateTime(
							$start
						);
					$vevent->DTSTART['VALUE'] = 'date';
					$vevent->add('DTEND');
					$vevent->DTEND->setDateTime(
							$end
						);
					$vevent->DTEND['VALUE'] = 'date';		
	                $vevent->{'SUMMARY'} = (string)$sTitle1;
	         		$vevent->{'UID'} = substr(md5(rand().time()), 0, 10);
						
					$params['events'][] =  $vevent->serialize();
					}
				}

				if(is_array($params['events'])){
	             $return = "BEGIN:VCALENDAR\nVERSION:2.0\nPRODID:ownCloud Calendar " . \OCP\App::getAppVersion('calendar') . "\nX-WR-CALNAME: export-bday-".$bookid."\n";
				  
				  foreach($params['events'] as $event) {
						$return .= $event;
					}
			     
			     $return .= "END:VCALENDAR";
				
				$name = str_replace(' ', '_', $addressbook['displayname']).'_birthdays' . '.ics';
				$response = new DataDownloadResponse($return, $name, 'text/calendar');
				
				return $response;
	           
 			}

			}
	}

	private function getAgeCalc($y, $m, $d) {
    	return date('Y') - $y - (date('n') < (ltrim($m,'0') + (date('j') < ltrim($d,'0'))));
   }	


}