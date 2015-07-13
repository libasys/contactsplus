<?php
/**
 * Copyright (c) 2014 Sebastian Doell <sebastian.doell@libasys.de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

/**
 * This class contains all hooks.
 */
 
 namespace OCA\ContactsPlus;
 use Sabre\VObject;
 
class Hooks{
		
	/**
	 * @brief Deletes all addressbooks of a certain user
	 * @param parameters parameters from postDeleteUser-Hook
	 * @return array
	 */
	public static function deleteUser($parameters) {
		\OCP\Util::writeLog('contactsplus', 'Hook DEL ID-> '.$parameters['uid'], \OCP\Util::DEBUG);	
		$addressbooks = Addressbook::all($parameters['uid']);

		foreach($addressbooks as $addressbook) {
			if($parameters['uid'] === $addressbook['userid']) {
				Addressbook::delete($addressbook['id']);
			}
		}
		//delete preferences
		
		return true;
	}
	
		
	public static function getBirthdayCalender($params){
			
		$isAktiv= 1;	
		if(\OCP\Config::getUserValue(\OCP\USER::getUser(), 'calendarplus', 'calendar_birthday_'. \OCP\USER::getUser()) !== ''){
	        $isAktiv = (int)\OCP\Config::getUserValue(\OCP\USER::getUser(), 'calendarplus','calendar_birthday_'. \OCP\USER::getUser());
        }
		    $base_url = \OC::$server->getURLGenerator()->linkToRoute('calendarplus.event.getEvents').'?calendar_id=';
			
		      $Calendar=array(
			    'url' => $base_url.'birthday_'. \OCP\USER::getUser(),
			    'uri' => 'birthday_'. \OCP\USER::getUser(),
			    'externuri'=> '',
			    'displayname'=> App::$l10n->t('Birthdays'),
			    'permissions'=>\OCP\PERMISSION_READ,
			    'id'=>'birthday_'. \OCP\USER::getUser(),
			    'owner'=>\OCP\USER::getUser(),
			    'userid'=>\OCP\USER::getUser(),
			    'issubscribe' => 1,
			    'calendarcolor' => '#FFFF00',
				'cache' => true,
				'ctag'=>1,
				'className' => 'birthday-calendar', 
				'editable' => false,
				'startEditable' => false,
			    'active' => $isAktiv,
			  );
			 
			  $params['calendar'][] =$Calendar;
		
	}
	
	public static function getCalenderSources($parameters) {

			    $base_url = \OC::$server->getURLGenerator()->linkToRoute('calendarplus.event.getEvents').'?calendar_id=';
			
				$addSource= array(
					'url' => $base_url.'birthday_'. \OCP\USER::getUser(),
					'backgroundColor' => '#FFFF00',
					'borderColor' => '#FFFF00',
					 'id'=>'birthday_'. \OCP\USER::getUser(),
					'textColor' => 'black',
					'permissions'=>\OCP\PERMISSION_READ,
					'cache' => false,
					'ctag'=>2,
					'editable' => false,
					'startEditable' => false,
					'issubscribe' => 1,
				);
				
				$parameters['sources']=$addSource;
				
		
	}
	
	 public static function getBirthdayEvents($params) {
	 
		if(\OCP\Config::getUserValue(\OCP\USER::getUser(), 'calendarplus','calendar_birthday_'. \OCP\USER::getUser())){
				
			$name = $params['calendar_id'];
				
			if (strpos($name, 'birthday_') != 0) {
				return;
			}
			
			$info = explode('_', $name);
			$aid = $info[1];
			 $aDefNArray=array('0'=>'fname','1'=>'lname','3'=>'title','4'=>'');
			 
			foreach(Addressbook::all($aid) as $addressbook) {
				
				foreach(VCard::all($addressbook['id']) as $contact) {
					try {
						$vcard = VObject\Reader::read($contact['carddata']);
					} catch (Exception $e) {
						continue;
					}
					
					
					$birthday = $vcard->BDAY;
					
					if ((string)$birthday) {
						$details = VCard::structureContact($vcard);
						
						$BirthdayTemp = new \DateTime($birthday);	
						$checkForm=$BirthdayTemp->format('d-m-Y');
						$temp=explode('-',$checkForm);	
						$getAge=self::getAge($temp[2],$temp[1],$temp[0]);
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
							
						$sTitle1 =(string)App::$l10n->t('%1$s (%2$s)',array($sNameOutput,$getAge));
						
						
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
						
							
							
						
						// DESCRIPTION?
						$aktYear1=$BirthdayTemp->format('-m-d');
						$aktYear1=date('Y').$aktYear1;
						$params['events'][] = array(
							'id' => 0,//$card['id'],
							'vevent' => $vevent,
							'repeating' => true,
							'calendarid'=>$params['calendar_id'],
							'privat'=>false,
							'bday'=>true,
							'shared'=>false,
							'isalarm'=>false,
							'summary' =>$sTitle1,
							'start' => $aktYear1,
							'allDay'=>true,
							'startlist' =>$aktYear1,
							'editable' => false,
							'className' => 'birthdayevent',
							'startEditable ' => false,
							'durationEditable ' => false,
							);
					}
				}
			}
		}
		return true;
	 }
	
	public static function getAge ($y, $m, $d) {
    return date('Y') - $y - (date('n') < (ltrim($m,'0') + (date('j') < ltrim($d,'0'))));
   }
}