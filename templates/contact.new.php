<div id="new-contact">
 <div class="innerContactontent">
	<form name="contactForm" id="contactForm" action=" ">	
    <input type="hidden" name="hiddenfield" value="" />
    <input type="hidden" name="selectedContactgroup" id="selectedContactgroup" value="" />
    
    <input type="hidden" name="urltype" id="urltype" value="<?php p($_['URLTYPE_DEF']); ?>" />
 <span class="labelLeft"><?php p($l->t('Addressbook')); ?></span>
<select name="addressbooks">	
<?php foreach($_['addressbooks'] as $addressbook) {
	
	print_unescaped('<option value="'.$addressbook['id'].'">'.$addressbook['displayname'].'</option>');
}
?>
</select>
<br style="clear:both;">   
<span class="additionalField" data-addfield="gender">
  <span class="labelLeft">&nbsp;</span>	<input type="text" placeholder="<?php p($l->t('Title')); ?>" value="" maxlength="100" id="gender"  name="gender" />
 </span>  
<span class="labelLeft">&nbsp;</span>	<input type="text" placeholder="<?php p($l->t('First name')); ?>" value="" maxlength="100" id="fname"  name="fname" />
<span class="labelLeft">&nbsp;</span> 	<input type="text" placeholder="<?php p($l->t('Last name')); ?>" value="" maxlength="100" id="lname"  name="lname" />
<br style="clear:both;"><br>
<span class="additionalField" data-addfield="nickname">
	<span class="labelLeft">&nbsp;</span> 	<input type="text" placeholder="<?php p($l->t('Nickname')); ?>" value="" maxlength="100" id="nickname"  name="nickname" />
</span>
<span class="additionalField" data-addfield="position">
	<span class="labelLeft">&nbsp;</span> 	<input type="text" placeholder="<?php p($l->t('Position')); ?>" value="" maxlength="100" id="position"  name="position" />
</span>
<span class="additionalField" data-addfield="department">
	<span class="labelLeft">&nbsp;</span> 	<input type="text" placeholder="<?php p($l->t('Department')); ?>" value="" maxlength="100" id="department"  name="department" />
</span>
	<span class="labelLeft">&nbsp;</span> 	<input type="text" placeholder="<?php p($l->t('Organization')); ?>" value="" maxlength="100" id="firm"  name="firm" />

<br style="clear:both;"><br>
 <input type="hidden" name="phonetype[0]" id="phonetype_0" value="<?php p($_['TELTYPE_DEF']); ?>" />
<span class="labelLeft">
	<div id="sPhoneTypeSelect_0" class="combobox">
		<div class="comboSelHolder">	
	    <div class="selector">select</div>
	    <div class="arrow-down"></div>
	    </div>
	    <ul>
	    	<?php
			    foreach($_['TELTYPE'] as $KEY => $VALUE){
			       print_unescaped('<li data-id="'.$KEY.'">'.$VALUE.'</li>');
				}
			?>
	    </ul>
	</div>

		 		 	
</span> 
<input type="radio"  value="phone_0"  name="phonePref" checked="checked" />
<input type="text" placeholder="<?php p($l->t('Phone')); ?>" value="" style="width:210px;" maxlength="100" id="phone_0"  name="phone[0]" />
 <br style="clear:both;">
<input type="hidden" name="phonetype[1]" id="phonetype_1" value="<?php p($_['TELTYPE_DEF']); ?>" />
<span class="additionalField" data-addfield="phone">
<span class="labelLeft">
	<div id="sPhoneTypeSelect_1" class="combobox">
		<div class="comboSelHolder">	
	    <div class="selector">select</div>
	    <div class="arrow-down"></div>
	    </div>
	    <ul>
	    	<?php
			    foreach($_['TELTYPE'] as $KEY => $VALUE){
			       print_unescaped('<li data-id="'.$KEY.'">'.$VALUE.'</li>');
				}
			?>
	    </ul>
	</div>
</span> 	
<input type="text" placeholder="<?php p($l->t('Phone')); ?>" value="" maxlength="100" id="phone_1"  name="phone[1]" />
 <br style="clear:both;">
</span>

 <input type="hidden" name="emailtype[0]" id="emailtype_0" value="<?php p($_['EMAILTYPE_DEF']); ?>" />
<span class="labelLeft">
	<div id="sEmailTypeSelect_0" class="combobox">
		<div class="comboSelHolder">	
	    <div class="selector">select</div>
	    <div class="arrow-down"></div>
	    </div>
	    <ul>
	    	<?php
			    foreach($_['EMAILTYPE'] as $KEY => $VALUE){
			       print_unescaped('<li data-id="'.$KEY.'">'.$VALUE.'</li>');
				}
			?>
	    </ul>
	</div>
</span>
<input type="radio"  value="email_0"  name="emailPref" checked="checked" />

<input type="text" placeholder="<?php p($l->t('Email')); ?>" value="" style="width:210px;" maxlength="100" id="email_0"  name="email[0]" />
 <br style="clear:both;">
<input type="hidden" name="emailtype[1]" id="emailtype_1" value="<?php p($_['TELTYPE_DEF']); ?>" />
<span class="additionalField" data-addfield="email">
<span class="labelLeft">
	<div id="sEmailTypeSelect_1" class="combobox">
		<div class="comboSelHolder">	
	    <div class="selector">select</div>
	    <div class="arrow-down"></div>
	    </div>
	    <ul>
	    	<?php
			    foreach($_['EMAILTYPE'] as $KEY => $VALUE){
			       print_unescaped('<li data-id="'.$KEY.'">'.$VALUE.'</li>');
				}
			?>
	    </ul>
	</div>

</span> 	
<input type="text" placeholder="<?php p($l->t('Email')); ?>" value="" maxlength="100" id="email_1"  name="email[1]" />
 <br style="clear:both;">
</span>
 
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
<input type="text" placeholder="<?php p($l->t('Homepage')); ?>" value="" maxlength="100" id="homepage"  name="homepage" />
 <br style="clear:both;">
<input type="hidden" name="addrtype[0]" id="addrtype_0" value="<?php p($_['ADRTYPE_DEF']); ?>" />
<span class="labelLeft">
	<div id="sAddrTypeSelect_0" class="combobox">
		<div class="comboSelHolder">	
	    <div class="selector">select</div>
	    <div class="arrow-down"></div>
	    </div>
	    <ul>
	    	<?php
			    foreach($_['ADRTYPE'] as $KEY => $VALUE){
			       print_unescaped('<li data-id="'.$KEY.'">'.$VALUE.'</li>');
				}
			?>
	    </ul>
	</div>
</span>
<input type="text" placeholder="<?php p($l->t('Street Address')); ?>" value="" maxlength="100" id="street"  name="addr[0][street]" />
 <br style="clear:both;">
<span class="labelLeft">&nbsp;</span> <input type="text"style="width:74px;" placeholder="<?php p($l->t('Postal code')); ?>" value="" maxlength="100" id="postal"  name="addr[0][postal]" />
 <input type="text" style="width:140px;" placeholder="<?php p($l->t('City')); ?>" value="" maxlength="100" id="city"  name="addr[0][city]" />
<span class="labelLeft">&nbsp;</span> <input type="text" placeholder="<?php p($l->t('Country')); ?>" value="" maxlength="100" id="country"  name="addr[0][country]" />
<span class="additionalField" data-addfield="bday">
	<span class="labelLeft"><?php p($l->t('Birthday')); ?></span><input type="text" placeholder="tt.mm.jjjj" value="" maxlength="100" id="bday"  name="bday" />
</span>
<span class="labelLeft">&nbsp;</span> <textarea placeholder="<?php p($l->t('Notice')); ?>"  id="notice"  name="notice"></textarea>

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