<?php
//Prerendering for iCalendar file
/*
if(is_resource($file)) {
	//OCP\JSON::error(array('error'=>'404'));
	$file = $_['filename'];
}else{
	$file = \OC\Files\Filesystem::file_get_contents($_['path'] . '/' . $_['filename']);
}

$import = new OCA\Kontakte\Import($file);
$import->setUserID(OCP\User::getUser());
//$newaddressbookname = OCP\Util::sanitizeHTML($import->createAddressbookName());
//$guessedaddressbookname = OCP\Util::sanitizeHTML($import->guessAddressbookName());
$newaddressbookname = '';
$guessedaddressbookname = '';
*/
//loading calendars for select box
$newaddressbookname = '';
$guessedaddressbookname = '';
$contacts_options = OCA\ContactsPlus\Addressbook::all(OCP\USER::getUser());

?>
<div id="contacts_import_dialog" title="<?php p($l->t("Import a contacts file"));?>">
<div id="contacts_import_form">
	<form action=" " onsubmit="return false;" >
		<input type="hidden" id="contacts_import_filename" value="<?php p($_['filename']);?>">
		<input type="hidden" id="contacts_import_path" value="<?php p($_['path']);?>">
		<input type="hidden" id="contacts_import_progresskey" value="<?php p('contacts-import-' .time()) ?>">
		<input type="hidden" id="contacts_import_availablename" value="<?php p($newaddressbookname) ?>">
		<div id="contacts_import_form_message"><?php p($l->t('Please choose the addressbook')); ?></div>
		<select style="width:98%;" id="contacts_import_addressbook" name="contacts_import_addressbook">
		<?php
		
		for($i = 0;$i<count($contacts_options);$i++) {
				
		$addressbookChoose[]= array(
				'id' => $contacts_options[$i]['id'],
				'displayname' => $contacts_options[$i]['displayname']
				);
			
		}
		$addressbookChoose[] = array('id'=>'newaddressbook', 'displayname'=>$l->t('create a new addressbook'));
		
		print_unescaped(OCP\Template::html_select_options($addressbookChoose, $addressbookChoose[0]['id'], array('value'=>'id', 'label'=>'displayname')));
		?>
		</select>
		<br><br>
		<div id="contacts_import_newaddrform">
			<input id="contacts_import_newaddressbook"  class="" type="text" placeholder="<?php p($l->t('Name of new Addressbook')); ?>" value="<?php p($guessedaddressbookname) ?>"><br>
			<div  id="contacts_import_mergewarning" class="hint"><?php p($l->t('A addressbook with this name already exists. If you continue anyhow, these addressbooks will be merged.')); ?></div>
		<br style="clear:both;" />
		</div>
		<input type="checkbox" id="contacts_import_overwrite" value="1">
		<label for="contacts_import_overwrite"><?php p($l->t('Remove all vcards from the selected addressbook')); ?></label>
		<br>
		<button id="contacts_import_submit" class="button primary-button"><?php p($l->t('Import')); ?></button>
	<form>
</div>
<div id="contacts_import_process">
	<div id="contacts_import_process_message"></div>
	<div  id="contacts_import_progressbar"></div>
	<br>
	<div id="contacts_import_status" class="hint"></div>
	<br>
	<input id="contacts_import_done" type="button" value="<?php p($l->t('Close Dialog')); ?>">
</div>
</div>
