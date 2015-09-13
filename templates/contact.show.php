<div id="show-contact">
 <div class="innerContactontent">
 	<textarea id="imgsrc" name="imgsrc" style="display:none;"><?php p($_['imgsrc']); ?></textarea>
 	 <input type="hidden" name="id" id="photoId" value="<?php p($_['id']); ?>" />
   <input type="hidden" name="imgmimetype" id="imgmimetype" value="<?php p($_['imgMimeType']); ?>" />
	<input type="hidden" name="isphoto" id="isphoto" value="<?php p($_['isPhoto']); ?>" />
   <input type="hidden" name="tmpkey" id="tmpkey" value="<?php p($_['tmpkey']); ?>" />
 <input type="hidden" name="selectedContactgroup" id="selectedContactgroup" value="" />
 
	<span style="padding-top:20px;" class="labelPhoto" id="contactPhoto"><?php print_unescaped($_['thumbnail']); ?>
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

<?php 
$firm = $_['firm'];
$name = $_['anrede'].' '.$_['fname'].' '.$_['lname'];
if($_['bShowCompany']){
	$firm = $_['anrede'].' '.$_['fname'].' '.$_['lname'];
	$name = $_['firm'];
}
?>	
 	
<div class="categories">
	<i class="ioc ioc-book toolTip" title="<?php p($_['addressbookname']); ?>"></i>
	<?php if($_['categories']!==''){   print_unescaped($_['categories']);  }?>
</div>

<span class="labelFullname"><?php p($name); ?></span>
<span class="labelFirm" style="line-height:20px;"><?php  p($firm); ?></span>
<?php if($_['nickname']!=''){ ?>  
	<span style="padding-left:10px;line-height:20px;">
	<?php p('"'.$_['nickname'].'"'); ?>
	</span>
<?php } ?>
<span class="labelFirm" style="padding-top:0px;">
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
				<a href="mailto:<?php p($VALUE['val']); ?>"><i class="ioc ioc-mail"></i></a> <?php p($VALUE['type']); ?>
			</span>
	      <a href="mailto:<?php p($VALUE['val']); ?>"><?php p($VALUE['val']); ?></a> <?php print_unescaped($iPref); ?>
	      <br style="clear:both;">
   <?php } ?>
 <span class="spacer">&nbsp;</span>
 <?php } ?>
  
  
<?php
		 if(is_array($_['aUrl']))	{
		 	foreach($_['aUrl'] as  $VALUE){ 
				$iPref='';	 
			   	if(array_key_exists('pref', $VALUE) && $VALUE['pref']=='1'){
			   			$iPref='<i class="ioc ioc-checkmark"></i>';	
			   	} 
		   	?>
			 <span class="labelLeft">&nbsp;<a href="<?php p($VALUE['val']); ?>" target="_blank"><i class="ioc ioc-publiclink"></i></a> <?php p($VALUE['type']); ?></span><a class="show-link" href="<?php p($VALUE['val']); ?>" target="_blank"><?php p($VALUE['val']); ?></a> <?php print_unescaped($iPref); ?>
			<br style="clear:both;">
		 <?php } ?>
<span class="spacer">&nbsp;</span>
<?php } ?>
 <?php
		 if(is_array($_['aImpp']))	{
		 	foreach($_['aImpp'] as  $VALUE){ 
		 		$iPref='';	 
			   	if(array_key_exists('pref', $VALUE) && $VALUE['pref']=='1'){
			   			$iPref='<i class="ioc ioc-checkmark"></i>';	
			   	} 
		 ?> 
			 <span class="labelLeft">&nbsp;<i class="ioc ioc-users"></i> <?php p($VALUE['type']); ?></span><?php p($VALUE['val']); ?> <?php print_unescaped($iPref); ?>
			<br style="clear:both;">
		 <?php } ?>
<span class="spacer">&nbsp;</span>
<?php } ?>
 <?php
		 if(is_array($_['aCloud']))	{
		 	foreach($_['aCloud'] as  $VALUE){
		 		$iPref='';	 
			   	if(array_key_exists('pref', $VALUE) && $VALUE['pref']=='1'){
			   			$iPref='<i class="ioc ioc-checkmark"></i>';	
			   	} 
			 ?> 
			 <span class="labelLeft">&nbsp;<i class="ioc ioc-upload-cloud"></i> <?php p($VALUE['type']); ?></span><?php p($VALUE['val']); ?> <?php print_unescaped($iPref); ?>
			<br style="clear:both;">
		 <?php } ?>
<span class="spacer">&nbsp;</span>
<?php } ?>  
<?php
   if(is_array($_['aAddr']))	{ 
	foreach($_['aAddr'] as  $VALUE){ 
		$iPref='';	 
	   	if(array_key_exists('pref', $VALUE) && $VALUE['pref']=='1'){
	   			$iPref='<i class="ioc ioc-checkmark"></i>';	
	   	} 
		
	?>
			<span class="labelLeft" style="min-height:60px;">
				<a title="<?php p($l->t('View location on open mapquest')); ?>" target="_blank" href="http://open.mapquest.com/?q=<?php p($VALUE['val']['street']); ?><?php p(','.$VALUE['val']['postalcode']); ?><?php p(','.$VALUE['val']['city']); ?><?php p(','.$VALUE['val']['state']); ?><?php p(','.$VALUE['val']['country']); ?>"><i style="font-size:18px;color:#999;" class="ioc ioc-search"></i></a>
				<i class="ioc ioc-address"></i> <?php p($VALUE['type']); ?>
			</span>
	      <?php p($VALUE['val']['street']); ?> <?php print_unescaped($iPref); ?><br />
	      <?php p($VALUE['val']['postalcode'].' '); ?><?php p($VALUE['val']['city']); ?></br>
	      <?php p($VALUE['val']['state'].' '); ?><?php p($VALUE['val']['country']); ?>
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
	<span class="labelLeft"><i class="ioc ioc-info"></i> <?php p($l->t("Notice"));?></span><br /> <?php print_unescaped($_['sNotice']); ?>
	<br style="clear:both;">
<?php } ?>
  <br />
  </div>

<div id="actions" style="border-top:1px solid #ddd;width:100%;">
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
	    
		<button id="showContact-edit" class="button primary-button" title="<?php p($l->t("Edit"));?>"> <i class="ioc ioc-edit" ></i></button>
	  <?php } ?>
	   </div>
	</div>
</div>