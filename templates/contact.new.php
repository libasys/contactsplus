<div id="new-contact">
 <div class="innerContactontent">
	<form name="contactForm" id="contactForm" action=" ">	
    <input type="hidden" name="hiddenfield" value="" />
    <input type="hidden" name="selectedContactgroup" id="selectedContactgroup" value="" />
    
    <input type="hidden" name="urltype" id="urltype" value="<?php p($_['URLTYPE_DEF']); ?>" />
 <span class="labelLeft" style="text-align:right;padding-right:30px;"><?php p($l->t('Addressbook')); ?></span>
<select name="addressbooks">	
<?php foreach($_['addressbooks'] as $addressbook) {
	
	print_unescaped('<option value="'.$addressbook['id'].'">'.$addressbook['displayname'].'</option>');
}
?>
</select>
<br style="clear:both;">   

<span class="labelLeft" style="text-align:right;padding-right:30px;"><?php p($l->t('First name')); ?></span>	<input style="width:190px;" type="text" placeholder="<?php p($l->t('First name')); ?>" value="" maxlength="100" id="fname"  name="fname" />
<span class="labelLeft" style="text-align:right;padding-right:30px;"><?php p($l->t('Last name')); ?></span> 	<input style="width:190px;" type="text" placeholder="<?php p($l->t('Last name')); ?>" value="" maxlength="100" id="lname"  name="lname" />
<br style="clear:both;"><br>
<span class="additionalField" data-addfield="gender">
  <span class="labelLeft" style="text-align:right;padding-right:30px;"><?php p($l->t('Title')); ?></span>	<input style="width:190px;" type="text" placeholder="<?php p($l->t('Title')); ?>" value="" maxlength="100" id="gender"  name="gender" />
 </span>  
<span class="additionalField" data-addfield="nickname">
	<span class="labelLeft" style="text-align:right;padding-right:30px;"><?php p($l->t('Nickname')); ?></span> 	<input style="width:190px;" type="text" placeholder="<?php p($l->t('Nickname')); ?>" value="" maxlength="100" id="nickname"  name="nickname" />
</span>
<span class="additionalField" data-addfield="position">
	<span class="labelLeft" style="text-align:right;padding-right:30px;"><?php p($l->t('Position')); ?></span> 	<input style="width:190px;" type="text" placeholder="<?php p($l->t('Position')); ?>" value="" maxlength="100" id="position"  name="position" />
</span>
<span class="additionalField" data-addfield="department">
	<span class="labelLeft" style="text-align:right;padding-right:30px;"><?php p($l->t('Department')); ?></span> 	<input style="width:190px;"  type="text" placeholder="<?php p($l->t('Department')); ?>" value="" maxlength="100" id="department"  name="department" />
</span>
	<span class="labelLeft" style="text-align:right;padding-right:30px;"><?php p($l->t('Organization')); ?></span> 	<input style="width:190px;" type="text" placeholder="<?php p($l->t('Organization')); ?>" value="" maxlength="100" id="firm"  name="firm" />

<br style="clear:both;"><br>
<span class="fullWidth phone-container" data-id="0" id="phone-container-0">
			  
			 <input type="hidden" class="phone-type" name="phonetype[0]" id="phonetype-0" value="<?php p($_['TELTYPE_DEF']); ?>" />
			<span class="labelLeft">
			<i class="ioc ioc-phone icon-descr"></i>	
			<div id="phone-typeselect-0" class="phone-select combobox">
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
		
		  &nbsp;<input type="radio" class="phone-pref regular-radio"  value="phone_0" id="phonePref-0" name="phonePref" checked="checked" />
		  <label style="float:left;margin-top:5px;" class="phone-labelpref" for="phonePref-0"></label>

          <input id="phone-0"  name="phone[0]" type="text" class="inputMobil phone-val" style="width:190px;" placeholder="<?php p($l->t('Phone')); ?>" value="<?php p($VALUE['val']); ?>"  />
          <i class="ioc ioc-add add-phone" data-add="0"></i>
          <i class="ioc ioc-delete delete-phone" data-del="0"></i>
   		
</span>
<span class="fullWidth email-container" data-id="0" id="email-container-0">
		 <input type="hidden" class="email-type" name="emailtype[0]" id="emailtype-0" value="<?php p($_['EMAILTYPE_DEF']); ?>" />
		 
		<span class="labelLeft">
		<i class="ioc ioc-mail icon-descr"></i>	
		<div id="email-typeselect-0" class="combobox email-select">
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
		
	  &nbsp;<input type="radio" class="email-pref regular-radio"  value="email_0" id="emailPref-0"  name="emailPref" checked="checked" />
	 <label style="float:left;margin-top:5px;" class="email-labelpref" for="emailPref-0"></label>
      <input class="inputMobil email-val" style="width:190px;" id="email-0"  name="email[0]" type="text" placeholder="<?php p($l->t('Email')); ?>"  value="" maxlength="100"  />
     
      <i class="ioc ioc-add add-email" data-add="0"></i>
      <i class="ioc ioc-delete delete-email" data-del="0"></i>
</span>
 <span class="fullWidth url-container" data-id="0" id="url-container-0">
			 <input type="hidden" class="url-type" name="urltype[0]" id="urltype-0" value="INTERNET" />
			 
			<span class="labelLeft">
				<i class="ioc ioc-publiclink icon-descr"></i>
			<div id="url-typeselect-0" class="combobox url-select">
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
			&nbsp;<input type="radio" class="url-pref regular-radio"  value="url_0" id="urlPref-0"  name="urlPref"  />
			<label style="float:left;margin-top:5px;" class="url-labelpref" for="urlPref-0"></label> 
          <input class="inputMobil url-val" id="url-0" style="width:190px;" name="url[0]" type="text" placeholder="<?php p($l->t('Homepage')); ?>"  value="" maxlength="100"  />
         
          <i class="ioc ioc-add add-url" data-add="0"></i>
          <i class="ioc ioc-delete delete-url" data-del="0"></i>
 </span>
 <span class="fullWidth addr-container" data-id="0" id="addr-container-0">
    <input type="hidden" class="addr-type" name="addrtype[0]" id="addrtype-0" value="<?php p($_['ADRTYPE_DEF']); ?>" />
   
    <span class="labelLeft">
    	 <i class="ioc ioc-address icon-descr"></i>
	<div id="addr-typeselect-0" class="combobox addr-select">
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
&nbsp;<input type="radio" class="addr-pref regular-radio" style="margin-left:-13px;" value="addr_0" id="addrPref-0" name="addrPref" checked="checked" />
<label style="float:left;margin-top:5px;" class="addr-labelpref" for="addrPref-0"></label>  
<input name="addr[0][street]" class="addr-val-street inputMobil marginLeft20" style="width:190px;" type="text" placeholder="<?php p($l->t('Street Address')); ?>" value="" maxlength="100" />

 <i class="ioc ioc-add add-addr" data-add="0"></i>
<i class="ioc ioc-delete delete-addr" data-del="0"></i>
 <br style="clear:both;">
<span class="labelLeft" style="width:170px;">&nbsp;</span> <input class="addr-val-postal" name="addr[0][postal]" type="text" style="width:68px;" placeholder="<?php p($l->t('Postal Code')); ?>" value="" maxlength="100"   />
 <input class="addr-val-city" name="addr[0][city]" type="text" style="width:100px;" placeholder="<?php p($l->t('City')); ?>" value="<?php p($VALUE['val']['city']); ?>" maxlength="100"  />
<span class="labelLeft" style="width:170px;">&nbsp;</span> <input style="width:190px;" class="addr-val-state" name="addr[0][state]" type="text" placeholder="<?php p($l->t('State')); ?>" value="" maxlength="100"   />
<span class="labelLeft" style="width:170px;">&nbsp;</span> <input style="width:190px;" class="addr-val-country" name="addr[0][country]" type="text" placeholder="<?php p($l->t('Country')); ?>" value="" maxlength="100"   />
 </span>
 
 <span class="fullWidth im-container"  data-id="0" id="im-container-0">		
	 <input type="hidden" class="im-type" name="imtype[0]" id="imtype-0" value="<?php p($_['MESSENGERTYPE_DEF']); ?>" />
	
	<span class="labelLeft">
	<i class="ioc ioc-users icon-descr"></i>	
	<div id="im-typeselect-0" class="combobox im-select">
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
	&nbsp;<input type="radio" class="im-pref regular-radio" id="imPref-0" value="im_0"  name="imPref" checked="checked" />
	<label style="float:left;margin-top:5px;" class="im-labelpref" for="imPref-0"></label>   
	<input type="text" style="width:190px;" placeholder="<?php p($l->t('Messenger')); ?>" class="inputMobil im-val" value="" maxlength="100" id="im-0"  name="im[0]" />
    <i class="ioc ioc-add add-im" data-add="0"></i>
     <i class="ioc ioc-delete delete-im" data-del="0"></i>
</span>
<span class="fullWidth cloud-container"  data-id="0" id="cloud-container-0">		
	 <input type="hidden" class="cloud-type" name="cloudtype[0]" id="cloudtype-0" value="<?php p($_['ADRTYPE_DEF']); ?>" />
	
	<span class="labelLeft">
	<i class="ioc ioc-upload-cloud icon-descr"></i>	
	<div id="cloud-typeselect-0" class="combobox cloud-select">
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
	 &nbsp;<input type="radio" class="cloud-pref regular-radio"  value="cloud_0"  id="cloudPref-0" name="cloudPref" checked="checked" />
	 <label style="float:left;margin-top:5px;" class="cloud-labelpref" for="cloudPref-0"></label> 
  <input id="cloud-0"  name="cloud[0]" type="text" style="width:190px;" placeholder="<?php p($l->t('Federated-Cloud-ID')); ?>" class="inputMobil cloud-val" value="" maxlength="100"  />
 
  <i class="ioc ioc-add add-cloud" data-add="0"></i>
  <i class="ioc ioc-delete delete-cloud" data-del="0"></i>
</span>

<span class="labelLeft" style="text-align:right;padding-right:30px;"><?php p($l->t('Notice')); ?></span> <textarea style="width:190px;" placeholder="<?php p($l->t('Notice')); ?>"  id="notice"  name="notice"></textarea>

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
	<div  class="button-group" style="margin: 7px 5px;float:left;width:30%;">
	<button id="newContact-morefields" class="button"><?php p($l->t('Add Field')); ?></button> 
    </div>
 <div  class="button-group" style="margin: 7px 5px;float:right;">	
		<button id="newContact-cancel" class="button"><?php p($l->t("Cancel"));?></button> 
		<button id="newContact-submit" class="button"  style="min-width:60px;"><?php p($l->t("OK"));?></button>
	   </div>
	</div>
</div>