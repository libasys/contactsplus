<?php
		

		
		style('contactsplus', '3rdparty/leaflet');
		style('contactsplus', '3rdparty/MarkerCluster');
		style('contactsplus', '3rdparty/MarkerCluster.Default');
		style('contactsplus', '3rdparty/leaflet.awesome-markers');
		style('contactsplus', 'jquery.Jcrop');
		style('contactsplus', '3rdparty/fontello/css/animation');
		style('contactsplus', '3rdparty/fontello/css/fontello');
		style('contactsplus', '3rdparty/jquery.webui-popover');
		style('contactsplus','style');	
		
		script('core','tags');	
		script('contactsplus', '3rdparty/jquery-ui.drag-multiple');
		script('contactsplus', '3rdparty/leaflet');
		script('contactsplus', '3rdparty/Leaflet.EdgeMarker');
		script('contactsplus', '3rdparty/leaflet.markercluster-src');
		script('contactsplus', '3rdparty/leaflet.awesome-markers');
		script('contactsplus', '3rdparty/jquery.webui-popover');
		
		script('contactsplus','jquery.scrollTo.min');
		script('contactsplus','jquery.nicescroll.min');
		script('files', 'jquery.fileupload');
		script('contactsplus', 'jquery.Jcrop');
		script('contactsplus', 'app');
		//script('contactsplus','settings');
		script('contactsplus','loader');

?>

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


<div id="app-navigation">
<div id="leftsidebar">

	<h3><i id="allbooks" data-lastbook="<?php p($_['lastSelectedBook']) ?>" class="ioc ioc-book"></i> <?php p($l->t('Address books')); ?>
		<i id="importAddr" title="<?php p($l->t('Import addressbook per Drag & Drop')); ?>" class="toolTip ioc ioc-upload"></i>
		<i id="addAddr" title="<?php p($l->t('New Addressbook')) ?>" class="toolTip ioc ioc-add"></i>
		</h3>
	<div id="drop-area"><?php p($l->t('Import addressbook per Drag & Drop')); ?></div>
	<br style="clear:both;" />
	<ul id="cAddressbooks">
		
	</ul>
	<br style="clear:both;" /><br />
	<h3><i class="ioc ioc-users"></i> <?php p($l->t('Groups')); ?><i  id="addGroup" class="ioc ioc-add toolTip" title="<?php p($l->t('Add new group')); ?>"></i>  <i id="sortGroups" title="<?php p($l->t('Sort Groups')); ?>" class="toolTip ioc ioc-sort"></i><i id="refreshGroups" title="<?php p($l->t('Refresh Groups')); ?>" class="toolTip ioc ioc-refresh"></i></h3>
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

<input id="totalurl" type="hidden" value="<?php print_unescaped(OCP\Util::linkToRemote('contactsplus')); ?>" />
<input id="totalurlAbo" type="hidden" value="<?php print_unescaped(OCP\Util::linkToAbsolute('contactsplus','exportbday.php')); ?>" />
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
	
<br />
		</div>
	</div>

</div>
<div id="app-content">
	<div id="loading">
	<i style="margin-top:20%;" class=" ioc-spinner ioc-spin"></i>
</div>
	<div id="controls">
	<div id="first-group"  class="button-group" style="float:left;">	
		
		<button class="button" id="addContact"><i class="ioc ioc-add"></i> <?php p($l->t('Contact')); ?></button>

	</div>
	<div id="second-group" class="button-group"  style="float:left;">
		<?php 
			$selectedListView = '';
			$selectedCardView = 'isActiveListView';
			if($_['activeView'] == 'listview'){
				$selectedListView = 'isActiveListView';
				$selectedCardView = '';	
			}
		?>
		<button id="showList" class="button toolTip" title="<?php p($l->t('List View')); ?>"><i class="ioc ioc-th-list <?php p($selectedListView); ?>"></i></button> 
		<button id="showCards" class="button toolTip" title="<?php p($l->t('Card View')); ?>"><i class="ioc ioc-th-large <?php p($selectedCardView); ?>"></i></button> 
		<button class="button toolTip" id="showMap" title=" <?php p($l->t('Shows your contacts on world map')); ?>"><i class="ioc ioc-globe"></i></button>

		<input type="search" placeholder="&#xe802; <?php p($l->t(' in current addressbook')); ?>" name="contactsearch" id="contactsearch" style="font-family:Arial,fontello;font-size:18px;" />

	</div>	
	
</div>
<div class="listview-header">
	<span class="head-check"><input class="regular-checkbox" type="checkbox" id="chk-all" /><label  class="is-checkbox-all toolTip" for="chk-all" title="<?php p($l->t('Select/ unselect all cards')); ?>"></label></span>
	 <span class="fullname" id="sortName"><?php p($l->t('Displayname')); ?></span>
	 <span class="tel"><?php p($l->t('Phone')); ?></span>
	 <span class="email"><?php p($l->t('Email')); ?></span>
	 <span class="group"><?php p($l->t('Group')); ?></span>
	 <span class="opt">&nbsp;</span>
</div>
<div id="map" style="display:none;"></div>
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
