<div id="edit-contact">
 <div class="innerContactontent">
 	
 	
	<form name="contactForm" id="contactForm" action=" " enctype="multipart/form-data">	
    <input type="hidden" name="hiddenfield" value="" />
    <textarea id="imgsrc" name="imgsrc" style="display:none;"><?php p($_['imgsrc']); ?></textarea>
     <input type="hidden" name="id" id="photoId" value="<?php p($_['id']); ?>" />
      <input type="hidden" name="imgmimetype" id="imgmimetype" value="<?php p($_['imgMimeType']); ?>" />
	<input type="hidden" name="isphoto" id="isphoto" value="<?php p($_['isPhoto']); ?>" />
   <input type="hidden" name="tmpkey" id="tmpkey" value="<?php p($_['tmpkey']); ?>" />
     <input type="hidden" name="oldaddressbookid" value="<?php p($_['oldaddressbookid']); ?>" />
    <input type="hidden" name="selectedContactgroup" id="selectedContactgroup" value="" />
    <input type="hidden" name="urltype" id="urltype" value="<?php p($_['aUrl']['type']); ?>" />


<span class="labelLeft" style="text-align:right;padding-right:30px;"><?php p($l->t('Addressbook')); ?></span>
<select name="addressbooks">	
<?php foreach($_['addressbooks'] as $addressbook) {
	$selected='';
	if($addressbook['id']==$_['oldaddressbookid']) $selected='selected="selected"';
	print_unescaped('<option value="'.$addressbook['id'].'" '.$selected.'>'.$addressbook['displayname'].'</option>');
}
?>
</select>
<br style="clear:both;">
<span class="labelLeft" style="min-height:120px;padding-right:30px;text-align:center;">
	<span class="labelPhoto" id="contactPhoto"><?php print_unescaped($_['thumbnail']); ?>
  <div class="tip"  id="contacts_details_photo_wrapper" title="<?php p($l->t('Drop photo to upload')); ?> (max <?php p($_['uploadMaxHumanFilesize']); ?>)" data-element="PHOTO">
	<ul id="phototools" class="transparent hidden"  style="margin-top:30px;">
		<li><a class="delete" title="<?php p($l->t('Delete current photo')); ?>"><i class="ioc ioc-delete"></i></a></li>
		<li><a class="edit" title="<?php p($l->t('Edit current photo')); ?>"><i class="ioc ioc-edit"></i></a></li>
		<li><a class="upload" title="<?php p($l->t('Upload new photo')); ?>"><i class="ioc ioc-upload "></i></a></li>
		<li><a class="cloud" title="<?php p($l->t('Select photo from ownCloud')); ?>"><i class="ioc ioc-upload-cloud"></i></a></li>
	</ul>
	</div>
	<iframe name="file_upload_target" id="file_upload_target" src=""></iframe>	
 </span>
</span>	

<input type="text" placeholder="<?php p($l->t('First name')); ?>" value="<?php p($_['fname']); ?>" maxlength="100" id="fname"  name="fname" />
<input type="text" placeholder="<?php p($l->t('Last name')); ?>" value="<?php p($_['lname']); ?>" maxlength="100" id="lname"  name="lname" />
  <?php 
$isAnredeActive='';
if($_['anrede']!='') $isAnredeActive=' activeAddFieldEdit';
 ?>

<span class="additionalField<?php p($isAnredeActive); ?>" data-addfield="gender">
  	<span class="labelLeft" style="text-align:right;padding-right:30px;"><?php p($l->t('Title')); ?></span><input style="width:210px;" type="text" placeholder="<?php p($l->t('Title')); ?>" value="<?php p($_['anrede']); ?>" maxlength="100" id="gender"  name="gender" />
 </span>  

  <?php 
$isNickActive='';
if($_['nickname']!='') $isNickActive=' activeAddFieldEdit';
 ?>  
<span class="additionalField<?php p($isNickActive); ?>" data-addfield="nickname">
	 	<span class="labelLeft" style="text-align:right;padding-right:30px;"><?php p($l->t('Nickname')); ?></span><input style="width:210px;" type="text" placeholder="<?php p($l->t('Nickname')); ?>" value="<?php p($_['nickname']); ?>" maxlength="100" id="nickname"  name="nickname" />
</span>
<?php 
$isBdayActive='';
if($_['sBday']!='') $isBdayActive=' activeAddFieldEdit';
 ?>
<span class="additionalField<?php p($isBdayActive); ?>" data-addfield="bday">
	<span class="labelLeft" style="text-align:right;padding-right:30px;"><?php p($l->t('Birthday')); ?></span><input style="width:210px;" type="text" placeholder="tt.mm.jjjj" value="<?php p($_['sBday']); ?>" maxlength="100" id="bday"  name="bday" />
</span>
  <?php 
$isPositionActive='';
if($_['position']!='') $isPositionActive=' activeAddFieldEdit';
 ?>  
<span class="additionalField<?php p($isPositionActive); ?>" data-addfield="position">
<span class="labelLeft" style="text-align:right;padding-right:30px;"><?php p($l->t('Position')); ?></span>	<input style="width:210px;" type="text" placeholder="<?php p($l->t('Position')); ?>" value="<?php p($_['position']); ?>" maxlength="100" id="position"  name="position" />
</span>
<?php 
$isDActive='';
if($_['department']!='') $isDActive=' activeAddFieldEdit';
 ?>
<span class="additionalField<?php p($isDActive); ?>" data-addfield="department">
<span class="labelLeft" style="text-align:right;padding-right:30px;"><?php p($l->t('Department')); ?></span><input style="width:210px;" type="text" placeholder="<?php p($l->t('Department')); ?>" value="<?php p($_['department']); ?>" maxlength="100" id="department"  name="department" />
</span>
 	<input type="text" placeholder="<?php p($l->t('Organization')); ?>" value="<?php p($_['firm']); ?>" maxlength="100" id="firm"  name="firm" />

<br style="clear:both;"><br>


<?php
        $iCount=0;
		foreach($_['aTel'] as  $VALUE){ ?>
			<span class="fullWidth phone-container" data-id="<?php p($iCount); ?>" id="phone-container-<?php p($iCount); ?>">
			 
			 <input type="hidden" class="phone-type" name="phonetype[<?php p($iCount); ?>]" id="phonetype-<?php p($iCount); ?>" value="<?php p($VALUE['type']); ?>" />
			<span class="labelLeft">
			 <i class="ioc ioc-phone icon-descr"></i>
			<div id="phone-typeselect-<?php p($iCount); ?>" class="phone-select combobox">
				<div class="comboSelHolder">	
			    <div class="selector">select</div>
			    <div class="arrow-down"></div>
			    </div>
			    <ul>
			    	<?php
					    foreach($_['TELTYPE'] as $KEY => $VAL){
					       print_unescaped('<li data-id="'.$KEY.'">'.$VAL.'</li>');
						}
					?>
			    </ul>
			</div>
		</span>
		<?php 
		    $sChecked='';
			if(array_key_exists('pref', $VALUE)  && $VALUE['pref'] == '1'){
				 $sChecked='checked="checked"';
			}
		  ?> 
		  &nbsp;<input type="radio" class="phone-pref regular-radio"  value="phone_<?php p($iCount); ?>" id="phonePref-<?php p($iCount); ?>"  name="phonePref" <?php print_unescaped($sChecked); ?> />
		  <label style="float:left;margin-top:5px;" class="phone-labelpref" for="phonePref-<?php p($iCount); ?>"></label>

          <input id="phone-<?php p($iCount); ?>"  name="phone[<?php p($iCount); ?>]" type="text" class="inputMobil phone-val" placeholder="<?php p($l->t('Phone')); ?>" value="<?php p($VALUE['val']); ?>"  />
          <?php if($iCount == 0){ ?>
          <i class="ioc ioc-add add-phone" data-add="<?php p($iCount); ?>"></i>
           <?php } ?>
          <i class="ioc ioc-delete delete-phone" data-del="<?php p($iCount); ?>"></i>
   		
     </span>
     
      <?php 
      $iCount++;
      } ?>

<?php
        $iECount=0;
		foreach($_['aEmail'] as  $VALUE){ ?>
			 <span class="fullWidth email-container" data-id="<?php p($iECount); ?>" id="email-container-<?php p($iECount); ?>">
			 <input type="hidden" class="email-type" name="emailtype[<?php p($iECount); ?>]" id="emailtype-<?php p($iECount); ?>" value="<?php p($VALUE['type']); ?>" />
			 
			<span class="labelLeft">
			<i class="ioc ioc-mail icon-descr"></i>	
			<div id="email-typeselect-<?php p($iECount); ?>" class="combobox email-select">
				<div class="comboSelHolder">	
			    <div class="selector">select</div>
			    <div class="arrow-down"></div>
			    </div>
			    <ul>
			    	<?php
					    foreach($_['EMAILTYPE'] as $KEY => $VAL){
					       print_unescaped('<li data-id="'.$KEY.'">'.$VAL.'</li>');
						}
					?>
			    </ul>
			</div>
		</span> 
			<?php 
		    $sChecked='';
			if(array_key_exists('pref', $VALUE)  && $VALUE['pref'] == '1'){
				 $sChecked='checked="checked"';
			}
		  ?> 
		  &nbsp;<input type="radio" class="email-pref regular-radio"  value="email_<?php p($iECount); ?>"  name="emailPref"  id="emailPref-<?php p($iECount); ?>" <?php print_unescaped($sChecked); ?> /><label class="email-labelpref" style="float:left;margin-top:5px;" for="emailPref-<?php p($iECount); ?>"></label>

          <input class="inputMobil email-val" id="email-<?php p($iECount); ?>"  name="email[<?php p($iECount); ?>]" type="text" placeholder="<?php p($l->t('Email')); ?>"  value="<?php p($VALUE['val']); ?>" maxlength="100"  />
          <?php if($iECount == 0){ ?>
          <i class="ioc ioc-add add-email" data-add="<?php p($iECount); ?>"></i>
           <?php } ?>
          <i class="ioc ioc-delete delete-email" data-del="<?php p($iECount); ?>"></i>
        </span>
        
      <?php 
      $iECount++;
      } ?>

<?php
        $iUrlCount=0;
		foreach($_['aUrl'] as  $VALUE){ ?>
			 <span class="fullWidth url-container" data-id="<?php p($iUrlCount); ?>" id="url-container-<?php p($iUrlCount); ?>">
			 <input type="hidden" class="url-type" name="urltype[<?php p($iUrlCount); ?>]" id="urltype-<?php p($iUrlCount); ?>" value="<?php p($VALUE['type']); ?>" />
			 
			<span class="labelLeft">
			<i class="ioc ioc-publiclink icon-descr"></i>	
			<div id="url-typeselect-<?php p($iUrlCount); ?>" class="combobox url-select">
				<div class="comboSelHolder">	
			    <div class="selector">select</div>
			    <div class="arrow-down"></div>
			    </div>
			    <ul>
			    	<?php
					    foreach($_['URLTYPE'] as $KEY => $VAL){
					       print_unescaped('<li data-id="'.$KEY.'">'.$VAL.'</li>');
						}
					?>
			    </ul>
			</div>
		</span> 
			<?php 
		    $sChecked='';
			if(array_key_exists('pref', $VALUE)  && $VALUE['pref'] == '1'){
				 $sChecked='checked="checked"';
			}
			?>
		
		&nbsp;<input type="radio" class="url-pref regular-radio"  value="url_<?php p($iUrlCount); ?>"  name="urlPref" id="urlPref-<?php p($iUrlCount); ?>" <?php print_unescaped($sChecked); ?> /><label class="url-labelpref" style="float:left;margin-top:5px;" for="urlPref-<?php p($iUrlCount); ?>"></label>

          <input class="inputMobil url-val" id="url-<?php p($iUrlCount); ?>"  name="url[<?php p($iUrlCount); ?>]" type="text" placeholder="<?php p($l->t('Homepage')); ?>"  value="<?php p($VALUE['val']); ?>" maxlength="100"  />
          <?php if($iUrlCount == 0){ ?>
          <i class="ioc ioc-add add-url" data-add="<?php p($iUrlCount); ?>"></i>
           <?php } ?>
          <i class="ioc ioc-delete delete-url" data-del="<?php p($iUrlCount); ?>"></i>
        </span>
        
      <?php 
      $iUrlCount++;
      } ?>      

<?php
        $iACount=0;
		foreach($_['aAddr'] as  $VALUE){ ?>
     <span class="fullWidth addr-container" data-id="<?php p($iACount); ?>" id="addr-container-<?php p($iACount); ?>">
    <input type="hidden" class="addr-type" name="addrtype[<?php p($iACount); ?>]" id="addrtype-<?php p($iACount); ?>" value="<?php p($VALUE['type']); ?>" />
    
    <span class="labelLeft">
    <i class="ioc ioc-address icon-descr"></i>	
	<div id="addr-typeselect-<?php p($iACount); ?>" class="combobox addr-select">
		<div class="comboSelHolder">	
	    <div class="selector">select</div>
	    <div class="arrow-down"></div>
	    </div>
	    <ul>
	    	<?php
			    foreach($_['ADRTYPE'] as $KEY => $VAL){
			       print_unescaped('<li data-id="'.$KEY.'">'.$VAL.'</li>');
				}
			?>
	    </ul>
	</div>
</span>
<?php 
    $sChecked='';
	if(array_key_exists('pref', $VALUE)  && $VALUE['pref'] == '1'){
		 $sChecked='checked="checked"';
	}
	?>

&nbsp;<input type="radio" class="addr-pref regular-radio"  value="addr_<?php p($iACount); ?>"  name="addrPref" id="addrPref-<?php p($iACount); ?>" <?php print_unescaped($sChecked); ?> /><label class="addr-labelpref" style="float:left;margin-top:5px;" for="addrPref-<?php p($iACount); ?>"></label>
<input name="addr[<?php p($iACount); ?>][street]" class="addr-val-street inputMobil marginLeft20" style="width:210px;" type="text" placeholder="<?php p($l->t('Street Address')); ?>" value="<?php p($VALUE['val']['street']); ?>" maxlength="100" />
<?php if($iACount == 0){ ?>
          <i class="ioc ioc-add add-addr" data-add="<?php p($iACount); ?>"></i>
           <?php } ?>
<i class="ioc ioc-delete delete-addr" data-del="<?php p($iACount); ?>"></i>
 <br style="clear:both;">
<span class="labelLeft" style="width:170px;">&nbsp;</span> <input class="addr-val-postal" name="addr[<?php p($iACount); ?>][postal]" type="text" style="width:58px;" placeholder="<?php p($l->t('Postal Code')); ?>" value="<?php p($VALUE['val']['postalcode']); ?>" maxlength="100"   />
 <input class="addr-val-city" name="addr[<?php p($iACount); ?>][city]" type="text" style="width:130px;" placeholder="<?php p($l->t('City')); ?>" value="<?php p($VALUE['val']['city']); ?>" maxlength="100"  />
<span class="labelLeft" style="width:170px;">&nbsp;</span> <input style="width:210px;" class="addr-val-state" name="addr[<?php p($iACount); ?>][state]" type="text" placeholder="<?php p($l->t('State')); ?>" value="<?php p($VALUE['val']['state']); ?>" maxlength="100"   />
<span class="labelLeft" style="width:170px;">&nbsp;</span> <input style="width:210px;" class="addr-val-country" name="addr[<?php p($iACount); ?>][country]" type="text" placeholder="<?php p($l->t('Country')); ?>" value="<?php p($VALUE['val']['country']); ?>" maxlength="100"   />
 </span>

 <?php 
      $iACount++;
      } ?>



<?php
        $iMCount=0;
		foreach($_['aMessenger'] as  $VALUE){
			
	?>
	<span class="fullWidth im-container"  data-id="<?php p($iMCount); ?>" id="im-container-<?php p($iMCount); ?>">		
			 <input type="hidden" class="im-type" name="imtype[<?php p($iMCount); ?>]" id="imtype-<?php p($iMCount); ?>" value="<?php p($VALUE['type']); ?>" />
			
			<span class="labelLeft">
			<i class="ioc ioc-users icon-descr"></i>	
			<div id="im-typeselect-<?php p($iMCount); ?>" class="combobox im-select">
				<div class="comboSelHolder">	
			    <div class="selector">select</div>
			    <div class="arrow-down"></div>
			    </div>
			    <ul>
			    	<?php
					    foreach($_['IMTYPE'] as $KEY => $VAL){
					       print_unescaped('<li data-id="'.$KEY.'">'.$VAL['displayname'].'</li>');
						}
					?>
			    </ul>
			</div>
		</span> 
			<?php 
		    $sChecked='';
			if(array_key_exists('pref', $VALUE)  && $VALUE['pref'] == '1'){
				 $sChecked='checked="checked"';
			}
			?>
		
		&nbsp;<input type="radio" class="im-pref regular-radio"  value="im_<?php p($iMCount); ?>" id="imPref-<?php p($iMCount); ?>" name="imPref" <?php print_unescaped($sChecked); ?> /><label class="im-labelpref" style="float:left;margin-top:5px;" for="imPref-<?php p($iMCount); ?>"></label>

          <input type="text" placeholder="<?php p($l->t('Messenger')); ?>" class="inputMobil im-val" value="<?php p($VALUE['val']); ?>" maxlength="100" id="im-<?php p($iMCount); ?>"  name="im[<?php p($iMCount); ?>]" />
          <?php if($iMCount == 0){ ?>
          <i class="ioc ioc-add add-im" data-add="<?php p($iMCount); ?>"></i>
           <?php } ?>
          <i class="ioc ioc-delete delete-im" data-del="<?php p($iMCount); ?>"></i>
        </span>
       
   <?php 
      $iMCount++;
      } ?>     
 <?php
        $iClCount=0;
		foreach($_['aCloud'] as  $VALUE){ 
			?>
			 <span class="fullWidth cloud-container"  data-id="<?php p($iClCount); ?>" id="cloud-container-<?php p($iClCount); ?>">		
			 <input type="hidden" class="cloud-type" name="cloudtype[<?php p($iClCount); ?>]" id="cloudtype-<?php p($iClCount); ?>" value="<?php p($VALUE['type']); ?>" />
			
			<span class="labelLeft">
			<i class="ioc ioc-upload-cloud icon-descr"></i>	
			<div id="cloud-typeselect-<?php p($iClCount); ?>" class="combobox cloud-select">
				<div class="comboSelHolder">	
			    <div class="selector">select</div>
			    <div class="arrow-down"></div>
			    </div>
			    <ul>
			    	<?php
					    foreach($_['ADRTYPE'] as $KEY => $VAL){
					       print_unescaped('<li data-id="'.$KEY.'">'.$VAL.'</li>');
						}
					?>
			    </ul>
			</div>
		</span> 
			<?php 
		    $sChecked='';
			if(array_key_exists('pref', $VALUE)  && $VALUE['pref'] == '1'){
				 $sChecked='checked="checked"';
			}
			?>
		
		&nbsp;<input type="radio" class="cloud-pref regular-radio"  value="icloud_<?php p($iClCount); ?>"  name="cloudPref" id="cloudPref-<?php p($iClCount); ?>" <?php print_unescaped($sChecked); ?> /><label class="cloud-labelpref" style="float:left;margin-top:5px;" for="cloudPref-<?php p($iClCount); ?>"></label>

          <input id="cloud-<?php p($iClCount); ?>"  name="cloud[<?php p($iClCount); ?>]" type="text" placeholder="<?php p($l->t('Federated-Cloud-ID')); ?>" class="inputMobil cloud-val" value="<?php p($VALUE['val']); ?>" maxlength="100"  />
          <?php if($iClCount == 0){ ?>
          <i class="ioc ioc-add add-cloud" data-add="<?php p($iClCount); ?>"></i>
           <?php } ?>
          <i class="ioc ioc-delete delete-cloud" data-del="<?php p($iClCount); ?>"></i>
        </span>
			 
			
        
 <?php 
      $iClCount++;
      } ?>              
<span class="labelLeft" style="text-align:right;padding-right:30px;"><?php p($l->t('Notice')); ?></span> <textarea style="width:210px;" placeholder="<?php p($l->t('Notice')); ?>"  id="notice"  name="notice"><?php p($_['sNotice']); ?></textarea>

   </form>	
  </div>
 <div id="showAdditionalFieds">
 	 <ul>
	    	<?php
			    foreach($_['ADDFIELDS'] as $KEY => $VALUE){
			       print_unescaped('<li class="additionalFieldsRow" data-id="'.$KEY.'">'.$VALUE.'</li>');
				}
			?>
	    </ul>
 </div>  
<div id="actions" style="border-top:1px solid #bbb;height:50px;line-height:50px;width:100%;">
	
<?php if($_['addressbooksPerm']['permissions'] & OCP\PERMISSION_UPDATE) { ?>	
<div  class="button-group" style="margin: 7px 5px;float:left;width:30%;">
	<button id="editContact-morefields" class="button"><?php p($l->t('Add Field')); ?></button> 
</div>
<?php } ?>
<div  class="button-group" style="margin: 7px 5px;float:right;">
	
		<button id="editContact-cancel" class="button"><?php p($l->t("Cancel"));?></button> 
<?php if($_['addressbooksPerm']['permissions'] & OCP\PERMISSION_UPDATE) { ?>	
<button id="editContact-submit" class="button"  style="min-width:60px;"><?php p($l->t("OK"));?></button>
<?php } ?>
	   </div>
	</div>
</div>