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
  <?php 
$isAnredeActive='';
if($_['anrede']!='') $isAnredeActive=' activeAddFieldEdit';
 ?>


<span class="labelLeft"><?php p($l->t('Addressbook')); ?></span>
<select name="addressbooks">	
<?php foreach($_['addressbooks'] as $addressbook) {
	$selected='';
	if($addressbook['id']==$_['oldaddressbookid']) $selected='selected="selected"';
	print_unescaped('<option value="'.$addressbook['id'].'" '.$selected.'>'.$addressbook['displayname'].'</option>');
}
?>
</select>
<br style="clear:both;">
<span class="labelLeft" style="min-height:120px;padding-right:10px;text-align:center;">
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
<span class="additionalField<?php p($isAnredeActive); ?>" data-addfield="gender">
  	<input type="text" placeholder="<?php p($l->t('Title')); ?>" value="<?php p($_['anrede']); ?>" maxlength="100" id="gender"  name="gender" />
 </span>  

<input type="text" placeholder="<?php p($l->t('First name')); ?>" value="<?php p($_['fname']); ?>" maxlength="100" id="fname"  name="fname" />
 	<input type="text" placeholder="<?php p($l->t('Last name')); ?>" value="<?php p($_['lname']); ?>" maxlength="100" id="lname"  name="lname" />

  <?php 
$isNickActive='';
if($_['nickname']!='') $isNickActive=' activeAddFieldEdit';
 ?>  
<span class="additionalField<?php p($isNickActive); ?>" data-addfield="nickname">
	 	<input type="text" placeholder="<?php p($l->t('Nickname')); ?>" value="<?php p($_['nickname']); ?>" maxlength="100" id="nickname"  name="nickname" />
</span>
  <?php 
$isPositionActive='';
if($_['position']!='') $isPositionActive=' activeAddFieldEdit';
 ?>  
<span class="additionalField<?php p($isPositionActive); ?>" data-addfield="position">
	 	<input type="text" placeholder="<?php p($l->t('Position')); ?>" value="<?php p($_['position']); ?>" maxlength="100" id="position"  name="position" />
</span>
<?php 
$isDActive='';
if($_['department']!='') $isDActive=' activeAddFieldEdit';
 ?>
<span class="additionalField<?php p($isDActive); ?>" data-addfield="department">
	 	<input type="text" placeholder="<?php p($l->t('Department')); ?>" value="<?php p($_['department']); ?>" maxlength="100" id="department"  name="department" />
</span>
 	<input type="text" placeholder="<?php p($l->t('Organization')); ?>" value="<?php p($_['firm']); ?>" maxlength="100" id="firm"  name="firm" />

<br style="clear:both;"><br>


<?php
        $iCount=0;
		foreach($_['aTel'] as  $VALUE){ ?>
			<span id="tel_container_<?php p($iCount); ?>">
			 <input type="hidden" class="isPhone" name="phonetype[<?php p($iCount); ?>]" id="phonetype_<?php p($iCount); ?>" value="<?php p($VALUE['type']); ?>" />
			<span class="labelLeft">
			<div id="sPhoneTypeSelect_<?php p($iCount); ?>" class="combobox">
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
		  &nbsp;<input type="radio"  value="phone_<?php p($iCount); ?>"  name="phonePref" <?php print_unescaped($sChecked); ?> />

          <input type="text" class="inputMobil" placeholder="<?php p($l->t('Phone')); ?>" value="<?php p($VALUE['val']); ?>" id="phone_<?php p($iCount); ?>"  name="phone[<?php p($iCount); ?>]" />
          <i class="ioc ioc-delete deleteTel" data-del="<?php p($iCount); ?>"></i>
   
     </span>
     <br style="clear:both;">
      <?php 
      $iCount++;
      } ?>

<?php
        $iECount=0;
		foreach($_['aEmail'] as  $VALUE){ ?>
			 <span id="email_container_<?php p($iECount); ?>">
			 <input type="hidden" class="isEmail" name="emailtype[<?php p($iECount); ?>]" id="emailtype_<?php p($iECount); ?>" value="<?php p($VALUE['type']); ?>" />
			<span class="labelLeft">
			<div id="sEmailTypeSelect_<?php p($iECount); ?>" class="combobox">
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
		  &nbsp;<input type="radio"  value="email_<?php p($iECount); ?>"  name="emailPref" <?php print_unescaped($sChecked); ?> />

          <input type="text" placeholder="<?php p($l->t('Email')); ?>" class="inputMobil" value="<?php p($VALUE['val']); ?>" maxlength="100" id="email_<?php p($iECount); ?>"  name="email[<?php p($iECount); ?>]" />
          <i class="ioc ioc-delete deleteEmail" data-del="<?php p($iCount); ?>"></i>
        </span>
        <br style="clear:both;">
      <?php 
      $iECount++;
      } ?>

<span class="labelLeft">
	<div id="sUrlTypeSelect" class="combobox">
		<div class="comboSelHolder">	
	    <div class="selector">select</div>
	    <div class="arrow-down"></div>
	    </div>
	    <ul>
	    	<?php
			    foreach($_['URLTYPE'] as $KEY => $VALUE){
			       print_unescaped('<li data-id="'.$KEY.'">'.$VALUE.'</li>');
				}
			?>
	    </ul>
	</div>
</span> 
<input type="text" placeholder="<?php p($l->t('Homepage')); ?>" value="<?php p($_['aUrl']['val']); ?>" maxlength="100" id="homepage"  name="homepage" />
 <br style="clear:both;">
<?php
        $iACount=0;
		foreach($_['aAddr'] as  $VALUE){ ?>
    <span id="addr_container_<?php p($iACount); ?>">
    <input type="hidden" class="isAddr" name="addrtype[<?php p($iACount); ?>]" id="addrtype_<?php p($iACount); ?>" value="<?php p($VALUE['type']); ?>" />
    <span class="labelLeft">
	<div id="sAddrTypeSelect_<?php p($iACount); ?>" class="combobox">
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
<input class="inputMobil marginLeft20" type="text" placeholder="<?php p($l->t('Street Address')); ?>" value="<?php p($VALUE['val']['street']); ?>" maxlength="100"  name="addr[<?php p($iACount); ?>][street]" />
<i class="ioc ioc-delete deleteAddr" data-del="<?php p($iCount); ?>"></i>
 <br style="clear:both;">
<span class="labelLeft">&nbsp;</span> <input type="text" style="width:74px;" placeholder="<?php p($l->t('Postal Code')); ?>" value="<?php p($VALUE['val']['postalcode']); ?>" maxlength="100"  name="addr[<?php p($iACount); ?>][postal]" />
 <input type="text" style="width:140px;" placeholder="<?php p($l->t('City')); ?>" value="<?php p($VALUE['val']['city']); ?>" maxlength="100" id="city"  name="addr[<?php p($iACount); ?>][city]" />
<span class="labelLeft">&nbsp;</span> <input type="text" placeholder="<?php p($l->t('Country')); ?>" value="<?php p($VALUE['val']['country']); ?>" maxlength="100"  name="addr[<?php p($iACount); ?>][country]" />
 </span>
 <br style="clear:both;">
 <?php 
      $iACount++;
      } ?>
<?php 
$isBdayActive='';
if($_['sBday']!='') $isBdayActive=' activeAddFieldEdit';
 ?>
<span class="additionalField<?php p($isBdayActive); ?>" data-addfield="bday">
	<span class="labelLeft"><?php p($l->t('Birthday')); ?></span><input type="text" placeholder="tt.mm.jjjj" value="<?php p($_['sBday']); ?>" maxlength="100" id="bday"  name="bday" />
</span>
<span class="labelLeft">&nbsp;</span> <textarea placeholder="<?php p($l->t('Notice')); ?>"  id="notice"  name="notice"><?php p($_['sNotice']); ?></textarea>

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