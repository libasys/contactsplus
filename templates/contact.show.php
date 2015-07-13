<div id="show-contact">
 <div class="innerContactontent">
 	<textarea id="imgsrc" name="imgsrc" style="display:none;"><?php p($_['imgsrc']); ?></textarea>
 	 <input type="hidden" name="id" id="photoId" value="<?php p($_['id']); ?>" />
   <input type="hidden" name="imgmimetype" id="imgmimetype" value="<?php p($_['imgMimeType']); ?>" />
	<input type="hidden" name="isphoto" id="isphoto" value="<?php p($_['isPhoto']); ?>" />
   <input type="hidden" name="tmpkey" id="tmpkey" value="<?php p($_['tmpkey']); ?>" />
 <input type="hidden" name="selectedContactgroup" id="selectedContactgroup" value="" />
 
	<span class="labelPhoto" id="contactPhoto"><?php print_unescaped($_['thumbnail']); ?>
  <div class="tip" id="contacts_details_photo_wrapper" title="<?php p($l->t('Drop photo to upload')); ?> (max <?php p($_['uploadMaxHumanFilesize']); ?>)" data-element="PHOTO">
	<ul id="phototools" class="transparent hidden">
		<li><a class="delete" title="<?php p($l->t('Delete current photo')); ?>"><i class="ioc ioc-delete "></i></a></li>
		<li><a class="edit" title="<?php p($l->t('Edit current photo')); ?>"><i class="ioc ioc-edit"></i></a></li>
		<li><a class="upload" title="<?php p($l->t('Upload new photo')); ?>"><i class="ioc ioc-upload "></i></a></li>
		<li><a class="cloud" title="<?php p($l->t('Select photo from ownCloud')); ?>"><i class="ioc ioc-upload-cloud"></i></a></li>
	</ul>
	</div>
	<iframe name="file_upload_target" id="file_upload_target" src=""></iframe>	
 </span>
	
 	

<span class="labelFullname"><?php p($_['anrede']); ?> <?php p($_['fname']); ?> <?php p($_['lname']); ?></span>
<?php if($_['nickname']!=''){ ?>  
	"<?php p($_['nickname']); ?>"
<?php } ?>
<span class="labelFirm">
	<?php 
	if($_['position']!='')	{
		p($_['position'].' - ');
	}
	if($_['department']!='')	{
		p($_['department']);
		?>
		<br>
		<span>
		<?php
	}
	p($_['firm']);
	 ?>
	 </span>
</span>
<br style="clear:both;">
<span class="spacer">&nbsp;</span>


<?php
        if(is_array($_['aTel']))	{    
		   foreach($_['aTel'] as  $VALUE){
		   	$iPref='';	 
		   	if(array_key_exists('pref', $VALUE) && $VALUE['pref']=='1'){
		   			$iPref='<i class="ioc ioc-checkmark"></i>';	
		   	}
		   	?>
			<span class="labelLeft">&nbsp;
				<i class="ioc ioc-phone"></i> <?php p($VALUE['type']); ?>
			</span>
	      <?php p($VALUE['val']); ?>  <?php print_unescaped($iPref); ?>
          <br style="clear:both;">
    <?php } ?>
 <span class="spacer">&nbsp;</span>
 <?php } ?>
 
<?php
     if(is_array($_['aEmail']))	{    
	     foreach($_['aEmail'] as  $VALUE){ 
	     	$iPref='';	 
		   	if(array_key_exists('pref', $VALUE) && $VALUE['pref']=='1'){
		   			$iPref='<i class="ioc ioc-checkmark"></i>';	
		   	}
		   	?>
	     	
			<span class="labelLeft">&nbsp;
				<i class="ioc ioc-mail"></i> <?php p($VALUE['type']); ?>
			</span>
	      <?php p($VALUE['val']); ?> <?php print_unescaped($iPref); ?>
	      <br style="clear:both;">
   <?php } ?>
 <span class="spacer">&nbsp;</span>
 <?php } ?>
  
  
<?php
		if(is_array($_['aUrl']))	{
	?>
 <span class="labelLeft">&nbsp;<i class="ioc ioc-publiclink"></i> <?php p($_['aUrl']['type']); ?></span><?php p($_['aUrl']['val']); ?>
<br style="clear:both;">
<span class="spacer">&nbsp;</span>
<?php } ?>
  
<?php
   if(is_array($_['aAddr']))	{ 
	foreach($_['aAddr'] as  $VALUE){ ?>
			<span class="labelLeft" style="min-height:60px;">
				<i class="ioc ioc-address"></i> <?php p($VALUE['type']); ?>
			</span>
	      <?php p($VALUE['val']['street']); ?><br />
	      <?php p($VALUE['val']['postalcode']); ?>  <?php p($VALUE['val']['city']); ?></br>
	      <?php p($VALUE['val']['country']); ?>
          <br style="clear:both;">
  <?php } ?>
 <span class="spacer">&nbsp;</span>
 <?php } ?>


<?php
		if($_['sBday']!='')	{
	?>
	<span class="labelLeft"><?php p($l->t("Birthday"));?></span><?php p($_['sBday']); ?>
	<br style="clear:both;">
	<span class="spacer">&nbsp;</span>
<?php } ?>	
	

<?php if($_['sNotice']!='') {  ?>
	<br style="clear:both;">
	<span class="labelLeft"><i class="ioc ioc-info"></i> <?php p($l->t("Notice"));?></span> <?php p($_['sNotice']); ?>
	<br style="clear:both;">
<?php } ?>
  <br />
  </div>

<div id="actions" style="border-top:1px solid #bbb;width:100%;">
<div  class="button-group" style="margin: 5px 0px;float:left;width:48%;">
<?php if($_['addressbooksPerm']['permissions'] & OCP\PERMISSION_DELETE) { ?>	
	<button id="showContact-delete" class="button" title="<?php p($l->t("Delete"));?>">
		  <i class="ioc ioc-delete"></i>
		</button> 
	<?php } ?>
	<button id="showContact-export" class="button"  title="<?php p($l->t("Export"));?>">
		 <i class="ioc ioc-export"></i>
	</button> 
</div>	
<div  class="button-group" style="margin: 5px 0px;float:right;">
	    
		<button id="showContact-cancel" class="button" title="<?php p($l->t("Cancel"));?>"><i class="ioc ioc-close" ></i></button> 
<?php if($_['addressbooksPerm']['permissions'] & OCP\PERMISSION_UPDATE) { ?>	
	    
		<button id="showContact-edit" class="button" title="<?php p($l->t("Edit"));?>"> <i class="ioc ioc-edit" ></i></button>
	  <?php } ?>
	   </div>
	</div>
</div>