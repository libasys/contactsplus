<form class="float" id="file_upload_form" action=" " method="post" enctype="multipart/form-data" target="file_upload_target">
	<input type="hidden" name="id" value="">
	<input type="hidden" name="requesttoken" value="<?php p($_['requesttoken']) ?>">
	<input type="hidden" name="MAX_FILE_SIZE" value="<?php p($_['uploadMaxFilesize']) ?>" id="max_upload">
	<input type="hidden" class="max_human_file_size" value="(max <?php p($_['uploadMaxHumanFilesize']); ?>)">
	<input type="hidden" id="iossupport" value="<?php p($_['iossupport']); ?>" />
	<input id="contactphoto_fileupload" type="file" accept="image/*" name="imagefile" style="display:none;" />
</form>
<iframe name="file_upload_target" id='file_upload_target' src=""></iframe>

<div id="searchresults" data-appfilter="contactsplus"></div>
<div id="notification" style="display:none;"></div>
<div id="loading">
	<i style="margin-top:20%;" class=" ioc-spinner ioc-spin"></i>
</div>

<div id="app-navigation">
<div id="leftsidebar">

	<h3><i class="ioc ioc-book"></i> <?php p($l->t('Address books')); ?></h3>
	<ul id="cAddressbooks">
		
	</ul>
	<br style="clear:both;" /><br />
	<h3><i class="ioc ioc-users"></i> <?php p($l->t('Groups')); ?> <i id="sortGroups" title="<?php p($l->t('Sort Groups')); ?>" class="toolTip ioc ioc-sort"></i><i id="refreshGroups" title="<?php p($l->t('Refresh Groups')); ?>" class="toolTip ioc ioc-refresh"></i></h3>
	<ul id="cgroups">

</ul>
</div>
<div id="app-settings">
		<div id="app-settings-header">
			<button class="settings-button" data-apps-slide-toggle="#app-settings-content">
				<?php p($l->t('Settings'));?>
			</button>
		</div>
		<div id="app-settings-content">
		<form id="contacts-settings">
<input id="totalurl" type="hidden" value="<?php print_unescaped(OCP\Util::linkToRemote('contactsplus')); ?>" />
<input id="totalurlAbo" type="hidden" value="<?php print_unescaped(OCP\Util::linkToAbsolute('contactsplus','exportbday.php')); ?>" />

		  <dt><?php p($l->t('CardDAV syncing addresses')); ?> (<a href="http://owncloud.org/synchronisation/" target="_blank"><?php p($l->t('more info')); ?></a>)</dt>
		<dl>
		<dt><?php p($l->t('Primary address (for Contacts or similar)')); ?></dt>
		<dd><input type="text" style="width:95%;" readonly="readonly" value="<?php print_unescaped(OCP\Util::linkToRemote('contactsplus')); ?>" /></dd>
		<dt><?php p($l->t('iOS/OS X')); ?></dt>
		<dd><input type="text" style="width:95%;" readonly="readonly" value="<?php print_unescaped(OCP\Util::linkToRemote('contactsplus')); ?>principals/<?php p(OCP\USER::getUser()); ?>/" /></dd>
				<dt><b><?php p($l->t('iOS/OS X Support Groups (experimentel)')); ?></b></dt>
		      <dd>
		      	<?php 
		      	$isAktiv='';
		      	if(OCP\Config::getUserValue(OCP\USER::getUser(), 'contactsplus', 'iossupport')==true){
		      		$isAktiv='checked="checked"';
		      	}
				?>
		      	<input type="checkbox" id="toggleIosSupport"  <?php print_unescaped($isAktiv); ?>/> <?php p($l->t('Activition IOS Support for Groups')); ?> 
		     
		      	</dd>

		</dl>
		<div class="addressbooks-settings">
			<dt><?php p($l->t('Addressbooks')); ?></dt>
			<table id="allAddressbooks">
			<?php foreach($_['addressbooks'] as $addressbook) { ?>
			<tr class="addressbook" data-id="<?php p($addressbook['id']) ?>"
				data-uri="<?php p($addressbook['uri']) ?>"
				data-owner="<?php p($addressbook['userid']) ?>"
				>
				<?php
						$checkBox = '';
						if($addressbook['userid'] === OCP\USER::getUser()){
							if($addressbook['active']){
								 $checked = 'checked="checked"';
							}
							$checkBox='<input class="regular-checkbox isActiveAddressbook" data-id="'.$addressbook['id'].'" style="float:left;" id="active_aid_'.$addressbook['id'].'" type="checkbox" '.$checked.' /><label style="float:left;margin-top:4px;margin-right:5px;" for="active_aid_'.$addressbook['id'].'"></label>';
						}
				?>
				<td class="activate" title=""><?php print_unescaped($checkBox); ?></td>
				<td class="name" title="<?php p($addressbook['description']) ?>"><?php p($addressbook['displayname']) ?></td>
				<td class="action">
					<a title="<?php p($l->t('Show CardDav link')); ?>">
						<i class="ioc ioc-publiclink globe"></i>
					</a>
				</td>
				
				<?php if($addressbook['userid'] === OCP\USER::getUser()) { ?>
				<td class="action">
					<a title="<?php p($l->t('Export Addressbook')); ?>">
						 <i class="ioc ioc-export export"></i>
					</a>
				</td>
				<?php } ?>
				<td class="action">
					<?php if($addressbook['userid'] === OCP\USER::getUser() && $addressbook['permissions'] & OCP\PERMISSION_UPDATE) { ?>
					<a title="<?php p($l->t("Edit")); ?>">
						<i class="ioc ioc-edit edit"></i>
						
					</a>
					<?php } ?>
				</td>
				<td class="action">
					<?php if($addressbook['userid'] === OCP\USER::getUser() && $addressbook['permissions'] & OCP\PERMISSION_DELETE) { ?>
					<a title="<?php p($l->t("Delete")); ?>">
						<i class="ioc ioc-delete delete"></i>
					</a>
					<?php } ?>
				</td>
			</tr>
			<?php } ?>
			</table>
			<div class="actions" style="width: 100%;">
				
				<button class="button new"><?php p($l->t('New Address Book')) ?></button>
				<input class="name hidden" type="text" autofocus="autofocus" placeholder="<?php p($l->t('Name')); ?>" />
				<input class="description hidden" type="text" placeholder="<?php p($l->t('Description')); ?>" />
				<button class="button save hidden" style="margin-right:5px;"><?php p($l->t('Save')) ?></button> <button class="button cancel hidden"><?php p($l->t('Cancel')) ?></button>
			</div>
		</div>
	
</form><br /><br />
		</div>
	</div>

</div>
<div id="app-content">
	<div id="controls">
	<div id="first-group"  class="button-group" style="float:left;">	
		<button class="button" id="addGroup"><i class="ioc ioc-add"></i> <?php p($l->t('Group')); ?></button>
		<button class="button" id="addContact"><i class="ioc ioc-add"></i> <?php p($l->t('Contact')); ?></button>
	</div>
	<div id="second-group" class="button-group"  style="float:left;">
		<button id="showList" class="button" title="<?php p($l->t('List View')); ?>"><i class="ioc ioc-th-list"></i></button> 
		<button id="showCards" class="button" title="<?php p($l->t('Card View')); ?>"><i class="ioc ioc-th-large isActiveListView"></i></button> 
	<select id="searchOpt" style="font-size:14px;padding:0;">
 		<option value="fullname" selected><?php p($l->t('Organization')); ?></option>
 		<option value="name"><?php p($l->t('Name')); ?></option>
 		<option value="address"><?php p($l->t('Address')); ?></option>
 		<option value="email"><?php p($l->t('E-Mail')); ?></option>
 		</select>
 		<input type="search" placeholder="&#xe815;" name="contactsearch" id="contactsearch" style="font-family:Arial,fontello;font-size:18px;" />

	</div>	
	
</div>
	<div id="rightcontent">
	
	
	</div>
	<div id="rightcontentSideBar">
			<ul id="alphaScroll">

	</ul>
	</div>	
</div>
<div id="contact_details" style="display:none;"></div>
<div id="dialogSmall" title="Basic dialog" style="top:0;left:0;display:none;"></div>
<div id="dialogmore" title="Basic dialog" style="top:0;left:0;display:none;"></div>
<div id="edit_photo_dialog" title="Edit photo">
		<div id="edit_photo_dialog_img"></div>
</div>
