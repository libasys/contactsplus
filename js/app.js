/**
 * ownCloud - ContactsPlus Remastered
 *
 * @author Sebastian Doell
 * @copyright 2015 sebastian doell sebastian@libasys.de
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

L.PinMarker = L.Marker.extend({
	  options: {
	 	contactId:0,
	 	addrBkId:0,
	 	zoom:16
	 },
	 initialize: function (latlngs, options) {
		L.Marker.prototype.initialize.call(this, latlngs, options);
	},
	 getContactId:function(){
	 	  return this.options.contactId;
	 }	
});

L.pinMarker = function (latlngs, options) {	 
return new L.PinMarker(latlngs, options);
};

OC.ContactsPlus ={
	firstLoading : true,
	oldGroup:false,
	appName:'contactsplus',
	photoData:false,
	popOverElem:null,
	groups:[],
	mapObject:null,
	mapObjectMarker : {},
	aPinsMap:{},
	layerMarker : null,
	multiSelect:[],
	imgSrc :'',
	imgMimeType :'',
	init:function(){
		
		var lastBook = $('#allbooks').data('lastbook');
		OC.ContactsPlus.getAddressBooks(lastBook);
		OC.ContactsPlus.initSearch();
 		OC.ContactsPlus.Drop.init();
 		
		OC.ContactsPlus.loadContacts(lastBook,'',1,0);
		
		if (OC.ContactsPlus.firstLoading === true) {
			OC.ContactsPlus.checkShowEventHash();
			OC.ContactsPlus.calcDimension();
			 OC.ContactsPlus.getGroups();
			OC.ContactsPlus.firstLoading=false;
			
		}
		
		$('body').on('click',function(evt){
			if($('.app-navigation-entry-menu').hasClass('open') 
			&& !$(evt.target).parent().hasClass('app-navigation-entry-utils-menu-button')
			&& $(evt.target).parent().find('.app-navigation-entry-menu').hasClass('open')
			){
				$('.app-navigation-entry-menu').removeClass('open');
			}
			
		});
		$(document).on('keydown', '#adrbookname', function(event) {
			if (event.which == 13){
				OC.ContactsPlus.saveAddrBook($(event.target).parent().data('addr'));
			}
		});
		
		$('#chk-all').on('change',function(){
			if($(this).is(':checked')){
				$('.contactsrow.visible .contact-select').attr('checked','checked');
				$('.contactsrow.visible .is-checkbox').addClass('ui-selected');
			}else{
				$('.contactsrow.visible .contact-select').removeAttr('checked');
				$('.contactsrow.visible .is-checkbox').removeClass('ui-selected');
			}
		});
		
	},
	ContactPhoto:{
		loadPhoto:function(){
		
								
			var refreshstr = '&refresh=' + Math.random();
			$('#phototools li a').tipsy('hide');
			$('#contacts_details_photo').remove();
	
			var ImgSrc = '';
			if (OC.ContactsPlus.imgSrc != false) {
				ImgSrc = OC.ContactsPlus.imgSrc;
			}
			
			var newImg = $('<img>').attr('id', 'contacts_details_photo').css({'width':'auto'}).attr('src', 'data:' + OC.ContactsPlus.imgMimeType + ';base64,' + ImgSrc);
			newImg.prependTo($(' #contactPhoto'));
	
			$('#noimage').remove();
		
			$('#contact_details').removeClass('forceOpen');
			
		},
		deletePhoto:function(){
			
			$.getJSON(OC.generateUrl('apps/'+OC.ContactsPlus.appName+'/deletephoto'), {
				'id':$('#photoId').val()
			}, function(jsondata) {
				if(jsondata.status == 'success'){
                     $('#isphoto').val('0');
                     OC.ContactsPlus.imgSrc = false;
					$('#contacts_details_photo').remove();
					$('<div/>').attr({'id':'noimage','class':'ioc ioc-user'}).prependTo($(' #contactPhoto'));
					$('#imgsrc').val('');
                     OC.ContactsPlus.ContactPhoto.loadPhotoHandlers();
                     if($('#photo-id-'+$('#photoId').val()).length>0){
						$('#photo-id-'+$('#photoId').val()).remove();
						var newImg=$('<i/>').attr({'id':'photo-id-'+$('#photoId').val(),'class':'nopic-row ioc ioc-user'});
							$('span[data-contactid="'+$('#photoId').val()+'"] span.picture').append(newImg);
					}
				}
			});
			
			
		},
		loadActionPhotoHandlers: function() {
		   var phototools = $('#phototools');
		   
		   phototools.find('.delete').click(function(evt) {
					$(this).tipsy('hide');
						OC.ContactsPlus.ContactPhoto.deletePhoto();
					$(this).hide();
				}.bind(this));
	
				phototools.find('.edit').click(function() {
					$(this).tipsy('hide');
						OC.ContactsPlus.ContactPhoto.editCurrentPhoto();
				}.bind(this));
				
			phototools.find('.upload').click(function() {
				$(this).tipsy('hide');
				$('#contactphoto_fileupload').trigger('click');
			});
	
			phototools.find('.cloud').click(function() {
				$(this).tipsy('hide');
				
				var mimeparts = ['image/jpeg', 'httpd/unix-directory'];
				OC.dialogs.filepicker(t(OC.ContactsPlus.appName, 'Select photo'), OC.ContactsPlus.ContactPhoto.cloudPhotoSelected.bind(this), false, mimeparts, true);
			}.bind(this));
				
		},	
		
		loadPhotoHandlers:function() {
				var phototools = $('#phototools');
			phototools.find('li a').tipsy('hide');
			phototools.find('li a').tipsy();
				if ($('#isphoto').val() === '1') {
				phototools.find('.delete').show();
				phototools.find('.edit').show();
			} else {
				phototools.find('.delete').hide();
				phototools.find('.edit').hide();
			}
	
			phototools.find('.upload').show();
			phototools.find('.cloud').show();
			
			},
			cloudPhotoSelected:function(path){
				
				$.getJSON(OC.generateUrl('apps/'+OC.ContactsPlus.appName+'/getimagefromcloud'), {
					'path' : path,
					'id' : $('#photoId').val()
				}, function(jsondata) {
					if (jsondata) {
						OC.ContactsPlus.ContactPhoto.editPhoto(jsondata.id, jsondata.tmp);
						
						$('#tmpkey').val(jsondata.tmp);
						OC.ContactsPlus.imgSrc = jsondata.imgdata;
						OC.ContactsPlus.imgMimeType = jsondata.mimetype;
		
						$('#imgsrc').val(OC.ContactsPlus.imgSrc);
						$('#imgmimetype').val(OC.ContactsPlus.imgMimeType);
						$('#edit_photo_dialog_img').html(jsondata.page);
					} else {
						OC.dialogs.alert(jsondata.message, t(OC.ContactsPlus.appName, 'Error'));
					}
				}.bind(this));
					
				return false;
			},
			editCurrentPhoto:function(){
				
				OC.ContactsPlus.ContactPhoto.editPhoto($('#photoId').val(), $('#tmpkey').val());
			},
			showCoords: function (c) {
				$('#cropform input#x1').val(c.x);
				$('#cropform input#y1').val(c.y);
				$('#cropform input#x2').val(c.x2);
				$('#cropform input#y2').val(c.y2);
				$('#cropform input#w').val(c.w);
				$('#cropform input#h').val(c.h);
			},
			editPhoto:function(id, tmpkey){
				
				$.ajax({
				type : 'POST',
				url : OC.generateUrl('apps/'+OC.ContactsPlus.appName+'/cropphoto'),
				data : {
					'tmpkey' : tmpkey,
					'id' : id,
				},
				success : function(data) {
					 $('#edit_photo_dialog_img').html(data);
					
					$('#cropbox').attr('src', 'data:' +OC.ContactsPlus.imgMimeType + ';base64,' +OC.ContactsPlus.imgSrc).show();
	                //TODO SHOWCOORDS
	                
					$('#cropbox').Jcrop({
						onChange :OC.ContactsPlus.ContactPhoto.showCoords,
						onSelect : OC.ContactsPlus.ContactPhoto.showCoords,
						maxSize : [399, 399],
						bgColor : 'black',
						aspectRatio: 1,
						bgOpacity : .4,
						boxWidth : 400,
						boxHeight : 400,
						setSelect :[ 200, 200, 100, 100 ]//,
						//aspectRatio: 0.8
					});
				}
				});
			
				if ($('#edit_photo_dialog').dialog('isOpen') == true) {
					$('#edit_photo_dialog').dialog('moveToTop');
				} else {
					$('#edit_photo_dialog').dialog('open');
				}
				
			},
			savePhoto:function() {
		
				var target = $('#crop_target');
				var form = $('#cropform');
				var wrapper = $('#contacts_details_photo_wrapper');
				var self = this;
				wrapper.addClass('wait');
				form.submit();
				target.load(function(){
					
					var response=jQuery.parseJSON(target.contents().text());
					
					if(response != undefined && response.status == 'success'){
						$('#isphoto').val('1');
						
						OC.ContactsPlus.imgSrc = response.data.dataimg;
						OC.ContactsPlus.imgMimeType = response.data.mimetype;
						$('#imgsrc').val(OC.ContactsPlus.imgSrc);
						$('#imgmimetype').val(OC.ContactsPlus.imgMimeType);
						OC.ContactsPlus.ContactPhoto.loadPhoto();
						OC.ContactsPlus.ContactPhoto.loadPhotoHandlers();
						if($('#photo-id-'+response.data.id).length>0){
							$('#photo-id-'+response.data.id).remove();
							var newImg=$('<img/>').attr({'id':'photo-id-'+response.data.id,'src':'data:' + response.data.mimetype + ';base64,' + response.data.dataimg});
								$('span[data-contactid="'+response.data.id+'"] span.picture').append(newImg);
						}
						 if($('#showList i.ioc').hasClass('isActiveListView')){
						 		if($('#photo-small-id-'+response.data.id).length>0){
									$('#photo-small-id-'+response.data.id).remove();
						 			var newImgSmall=$('<img/>').attr({'id':'photo-small-id-'+response.data.id,'src':'data:' + response.data.mimetype + ';base64,' + response.data.dataimg});
									$('span[data-contactid="'+response.data.id+'"] span.head-picture').append(newImgSmall);
								}
						 }
						
					}else{
						OC.dialogs.alert(response.data.message, t(OC.ContactsPlus.appName, 'Error'));
						wrapper.removeClass('wait');
					}
				});
				//OC.ContactsPlus.refreshThumbnail(this.id);
			}
	},
	Drop : {
			init : function() {
				if ( typeof window.FileReader === 'undefined') {
					console.log('The drop-import feature is not supported in your browser :(');

					return false;
				}
				droparea = document.getElementById('drop-area');
				droparea.ondragover = function() {
					$('#drop-area').text('').addClass('loading');
					
					return false;
				};
				droparea.ondragend = function() {
					$('#drop-area').text(t(OC.ContactsPlus.appname,'Import addressbook per Drag & Drop')).removeClass('loading').slideUp(1500);
					return false;
				};
				droparea.ondragleave = function() {
					$('#drop-area').text(t(OC.ContactsPlus.appname,'Import addressbook per Drag & Drop')).removeClass('loading');
					return false;
				};
				
				
				droparea.ondrop = function(e) {
					e.preventDefault();
					e.stopPropagation();
					OC.ContactsPlus.Drop.drop(e);
				};
				console.log('Drop initialized successfully');

			},
			drop : function(e) {
				if (e.dataTransfer != undefined) {
					var files = e.dataTransfer.files;
					
					for (var i = 0; i < files.length; i++) {

						var file = files[i];
						
						if (!file.type.match('text/vcard') && !file.type.match('text/x-vcard') && !file.type.match('text/directory')){
							continue;
						}
					
						var reader = new FileReader();
						reader.onload = function(event) {
							
							OC.ContactsPlus.Import.Store.isDragged = true;
							OC.ContactsPlus.Import.Dialog.open(event.target.result);
							$('#drop-area').text(t(OC.ContactsPlus.appname,'Import addressbook per Drag & Drop')).removeClass('loading').slideUp(1500);
		
 							
						};
						reader.readAsDataURL(file);
					}
					
				}
			},
			
		},
	getAddressBooks:function(id){
			
			
			
			$.getJSON(OC.generateUrl('apps/'+OC.ContactsPlus.appName+'/getaddressbooks'), function(jsondata) {
				if(jsondata){
					$('#cAddressbooks').html(jsondata);
					
					if(id > 0){
						$('#cAddressbooks li').removeClass('isActiveABook');
					  	$('#cAddressbooks .dropcontainerAddressBook[data-adrbid="'+id+'"]').addClass('isActiveABook');
							
					}
					
					OC.ContactsPlus.addrBookEventHandler();
				  
				   
				   
				}
			});
	},
	addrBookEventHandler : function() {
			$('.isActiveAddressbook').on('change', function(event) {
				event.stopPropagation();

				OC.ContactsPlus.activateAddrBook($(this).data('id'), this );
			});
			
			$('.app-navigation-entry-utils-menu-button button').on('click',function(){
				if(!$(this).parent().find('.app-navigation-entry-menu').hasClass('open')){
				  $('.app-navigation-entry-menu').removeClass('open');
				  $(this).parent().find('.app-navigation-entry-menu').addClass('open');
				  $(this).parent().find('.app-navigation-entry-menu').css('right',$(window).width() - 252+'px');
				
				}else{
					 $(this).parent().find('.app-navigation-entry-menu').removeClass('open');
					  
				}
			
			});
			//Show Caldav url
			$('.app-navigation-entry-menu li i.ioc-globe').on('click',function(){
				if($('.app-navigation-entry-edit').length === 1){
					 $('.app-navigation-entry-menu').removeClass('open');
					var adrId =$(this).closest('.app-navigation-entry-menu').data('adrbid');
					var myClone = $('#addr-clone').clone();
					$('li.dropcontainerAddressBook[data-adrbid="'+adrId+'"]').after(myClone);
					myClone.attr('data-addr',adrId).show();
					$('li.dropcontainerAddressBook[data-adrbid="'+adrId+'"]').hide();
					
					myClone.find('input[name="adrbookname"]').hide();
					var cardDavUrl = OC.linkToRemote(OC.ContactsPlus.appname)+'/addressbooks/' +  oc_current_user + '/'+$('li.dropcontainerAddressBook[data-adrbid="'+adrId+'"]').data('url') ;
					myClone.find('input[name="carddavuri"]').val(cardDavUrl).show();
					
					myClone.find('button.icon-checkmark').on('click',function(){
						myClone.remove();
						$('li.dropcontainerAddressBook[data-adrbid="'+adrId+'"]').show();
					});
				}
			});
			
			$('#addAddr').on('click',function(){
				
				if($('.app-navigation-entry-edit').length === 1){
					
					var addrId = 'new';
					var myClone = $('#addr-clone').clone();
					
					$('#cAddressbooks').prepend(myClone);
					myClone.attr('data-addr',addrId).show();
					myClone.find('input[name="carddavuri"]').hide();
					myClone.find('input[name="adrbookname"]').focus();
					
					myClone.on('keyup',function(evt){
						if (evt.keyCode===27){
							myClone.remove();
							$('li.dropcontainerAddressBook[data-adrbid="'+addrId+'"]').show();
						}
					});
					myClone.find('button.icon-checkmark').on('click',function(){
						if(myClone.find('input[name="adrbookname"]').val()!==''){
							OC.ContactsPlus.saveAddrBook(addrId);
						}else{
							myClone.remove();
						}
					});
				}
			});
			
			//edit  Calendar		
			$('.app-navigation-entry-menu li.ioc-edit').on('click',function(){
				if($('.app-navigation-entry-edit').length === 1){
					 $('.app-navigation-entry-menu').removeClass('open');
					var adrId =$(this).closest('.app-navigation-entry-menu').data('adrbid');
					var name = $(this).closest('.app-navigation-entry-menu').data('name');
					var myClone = $('#addr-clone').clone();
					$('li.dropcontainerAddressBook[data-adrbid="'+adrId+'"]').after(myClone);
					myClone.attr('data-addr',adrId).show();
					$('li.dropcontainerAddressBook[data-adrbid="'+adrId+'"]').hide();
					myClone.find('input[name="carddavuri"]').hide();
					myClone.find('input[name="adrbookname"]').val($('li.dropcontainerAddressBook[data-adrbid="'+adrId+'"]').data('name')).focus();
					myClone.find('input[name="adrbookname"]');
					
					myClone.on('keyup',function(evt){
						if (evt.keyCode===27){
							myClone.remove();
							$('li.dropcontainerAddressBook[data-adrbid="'+adrId+'"]').show();
						}
					});
					myClone.find('button.icon-checkmark').on('click',function(){
						OC.ContactsPlus.saveAddrBook(adrId);
					});
				}
			});
			//deleteCalendar
			$('.app-navigation-entry-menu li.ioc-delete').on('click',function(){
				var id =$(this).closest('.app-navigation-entry-menu').data('adrbid');
				OC.ContactsPlus.deleteAddrBook(id);
			});
			
			$('#cAddressbooks li span.groupname').on('click',function(){
				  	  $('#cAddressbooks li').removeClass('isActiveABook');
				  	  $(this).parent('li').addClass('isActiveABook');
				  	  OC.ContactsPlus.loadContacts($(this).parent('li').attr('data-adrbid'),'',1,0);
			});
			
			 $('.toolTip').tipsy({
				html : true
			});
			
					
						  
					$(".dropcontainerAddressBook").droppable({
							activeClass: "activeHoverAddr",
							hoverClass: "dropHoverAddr",
							accept:'li.contactsrow .fullname',
							over: function( event, ui ) {
								
							},
							drop: function( event, ui ) {
								OC.ContactsPlus.showDialogAddressbook($(this).attr('data-adrbid'),ui.draggable.attr('data-id'));
								
							}
				   });
				   
			 OC.Share.loadIcons('cpladdrbook');
			
	},
	saveAddrBook:function(addrid){
		var saveForm = $('.app-navigation-entry-edit[data-addr="'+addrid+'"]');
		var displayname = saveForm.find('input[name="adrbookname"]').val();
		var url;
		if (addrid == 'new') {
			
			url = OC.generateUrl('apps/'+OC.ContactsPlus.appname+'/addaddrbook');
		
		} else {
			url = OC.generateUrl('apps/'+OC.ContactsPlus.appname+'/updateaddrbook');
		}
		self = this;
			$.post(url, { id: addrid, name: displayname, active:1, description: '' },
				function(jsondata){
					if(jsondata.status == 'success'){
						$('li.dropcontainerAddressBook[data-adrbid="'+addrid+'"]').find('.groupname').text(displayname);
						$('li.dropcontainerAddressBook[data-adrbid="'+addrid+'"]').data('name',displayname)
						$('li.dropcontainerAddressBook[data-adrbid="'+addrid+'"]').show();
						saveForm.remove();
					}
					if(addrid === 'new'){
							OC.ContactsPlus.getAddressBooks(0);
						}
				});
		
				
	},
	activateAddrBook:function(id, checkbox) {
			
			//console.log('doActivate: ', id, active);
			$.post(OC.generateUrl('apps/'+OC.ContactsPlus.appname+'/activateaddrbook'), {
				id: id, active: checkbox.checked ? 1 : 0}, function(jsondata) {
				if (jsondata.status == 'success') {
					OC.ContactsPlus.getAddressBooks(id);
				}
			});
		},
	deleteAddrBook:function(id) {
			 
			var handleDelete=function(YesNo){
			 
			 	if(YesNo){
					
					$.post(OC.generateUrl('apps/'+OC.ContactsPlus.appname+'/deleteaddrbook'), { id: id}, function(jsondata) {
						if (jsondata.status == 'success'){
						
							$('#cAddressbooks').find('.dropcontainerAddressBook[data-adrbid="'+id+'"]').remove();
							OC.ContactsPlus.getAddressBooks(0);
						} else {
							OC.dialogs.alert(jsondata.data.message, t(OC.ContactsPlus.appname, 'Error'));
						}
					});
				}
			 };
			 
			  OC.dialogs.confirm(t(OC.ContactsPlus.appname,'Do you really want to delete this address book?'),t(OC.ContactsPlus.appname,'Delete Addressbook'),handleDelete);
			
		},
	buildCounterGroups:function(addrbkId){
		
		var AllCount=$("li.contactsrow").length;
			
			$('#cAddressbooks li[data-adrbid="'+addrbkId+'"]').find('.groupcounter').text(AllCount);
			
			$('#cgroups li').removeClass('isActiveGroup');
            $('#cgroups li[data-grpid="all"]').addClass('isActiveGroup');
			
			$('#cgroups li').each(function(i,el){
				 //alert($(el).find('.groupcounter').text());
				 if($(el).attr('data-id') === 'all'){
				 	$(el).find('.groupcounter').text(AllCount);
				 }else if($(el).attr('data-id') === 'fav'){
				 	var counter = ($('.contactsrow .ioc-star.yellow').length / 2);
				 	if(counter < 0){
				 		counter = 0;
				 	}if(counter === 0.5){
				 		counter = 1;
				 	}
					 $(el).find('.groupcounter').text(counter);
				 }else{
					 var grpName=$(el).attr('data-id');
					 var counter=$('.contactsrow .colorgroup[data-category="'+grpName+'"]').length;
					 $(el).find('.groupcounter').text(counter);
				 }
			});
			var NonCount=$('.contactsrow .categories.hidden').length;
			$('#cgroups li[data-grpid="none"]').find('.groupcounter').text(NonCount);
	},
	
	addIosSupport : function(checkbox) {
				
				$.post(OC.generateUrl('apps/'+OC.ContactsPlus.appName+'/addiosgroupssupport'), {
					active : checkbox.checked ? 1 : 0
				}, function(data) {
					
					if (data.status == 'success') {
					  
                        $('#iossupport').val(data.active);
					}

				});
		},
		initActionhandlerSingleCard:function(cardId){
			//$('.container[data-contactid='+jsondata.data.id+']')
			var cardDiv='.container[data-contactid='+cardId+']';
			
			 		$(cardDiv+" .fullname" ).draggable({
						appendTo: "body",
						helper: OC.ContactsPlus.DragElement,
						cursor: "move",
						delay: 500,
						multiple: true,
						start: function(event, ui) {
							ui.helper.addClass('draggingContact');
						}
					});
						
					$(cardDiv+" .fullname a, "+cardDiv+" .rowBody").on('click',function(){
						$CardId=$(this).closest('.container').attr('data-contactid');
						OC.ContactsPlus.showContact($CardId,null);
					 });
					 
					
					 $(cardDiv+" .option a.delete").on('click',function(){
						 $CardId=$(this).closest('.container').attr('data-contactid');
						
						OC.ContactsPlus.deleteContact($CardId);
					 });
					 
					 $(cardDiv+" .option a.edit").on('click',function(){
						 $CardId=$(this).closest('.container').attr('data-contactid');
						
						OC.ContactsPlus.editContact($CardId);
					 });
					 
					 $(cardDiv+" a.favourite").on('click',function(){
						 $CardId=$(this).closest('.container').attr('data-contactid');
						  if($(this).find('i.ioc-star').hasClass('yellow')){
						  		$(this).find('i.ioc-star').removeClass('yellow');
						  		 OC.Tags.removeFromFavorites($CardId,OC.ContactsPlus.appName).then(function(){
						  		 	var iFavCounter=parseInt($('#cgroups li[data-id=fav]').find('.groupcounter').text());
									$('#cgroups li[data-id=fav]').find('.groupcounter').text((iFavCounter - 1));
									if($('#cgroups li[data-id="fav"]').hasClass('isActiveGroup')){
											$('.container[data-contactid='+$CardId+']').closest('li.contactsrow').remove();
									}
									iFavCounter=parseInt($('#cgroups li[data-id=fav]').find('.groupcounter').text());
									if(iFavCounter == 0){
										$('#cgroups li[data-id=fav]').remove();
										
									}
						  		 });
								
								
						  }else{
							 
							  $(this).find('i.ioc-star').addClass('yellow');
							   OC.Tags.addToFavorites($CardId,OC.ContactsPlus.appName).then(function(){
								   	 if($('#cgroups li[data-id="fav"]').length > 0){
										var iFavCounter=parseInt($('#cgroups li[data-id=fav]').find('.groupcounter').text());
										$('#cgroups li[data-id=fav]').find('.groupcounter').text((iFavCounter +1));
									}else{
										var aGroups=OC.ContactsPlus.getGroups();
										OC.ContactsPlus.buildGroupList(aGroups);
									}
							   });
							  
						  }
					});
		},
		loadContacts:function(addrbkId,grpId,bCount,id){
			  $('#loading').show();
			  
			   if($('.webui-popover').length>0){
					if(OC.ContactsPlus.popOverElem !== null){
						OC.ContactsPlus.popOverElem.webuiPopover('destroy');
						OC.ContactsPlus.popOverElem = null;
						$('#show-contact').remove();
					}
				}
				
				
				
				if(grpId === '' ){
					 $('#rightcontent').html('');
				   $.post(OC.generateUrl('apps/'+OC.ContactsPlus.appName+'/getcontactcards'),{ardbid:addrbkId,grpid:'all'},function(jsondata){
				   	   $("#rightcontent").niceScroll({
				   	   		railpadding:{top:0,right:0,left:0,bottom:100},
				   	   });
				   	    $('#loading').hide();
				   	     $('#rightcontent').html(jsondata);
				   	    //FIXME
				   	    $('#alphaScroll').html();
				   	    $('#chk-all').removeAttr('checked');
				   	    var NavLetter=[];
				   	    $('li.letter').each(function(i,el){
				   	    	NavLetter[i] = $('<li />').attr({'data-letter':$(el).attr('data-scroll')}).text($(el).attr('data-scroll'))
				   	    	.click(function(){  
							    if($(this).hasClass('bLetterActive')){
								    var liIndex = $(this).attr('data-letter');
								    $('#alphaScroll li').removeClass('isScrollTo');
									$(this).addClass('isScrollTo');
									$('#rightcontent').scrollTo('.letter[data-scroll="'+liIndex+'"]',800);
							    }
							}); 
							
				   	    });
				   	    $('#alphaScroll').html(NavLetter);
				   	    
								   	  
				   	      if($('#showList i.ioc').hasClass('isActiveListView')){
				   	     	
				   	     	$('#showCards i').removeClass('isActiveListView');
							$('#showList i').addClass('isActiveListView');
				   	     	$('.contactsrow').addClass('listview');
				   	     	$('.listview-header').show();
				   	     	$('.letter').addClass('listview');
				   	     }
				   	     
				   	     
				   	     
				   	      $( "li.contactsrow .fullname" ).draggable({
							appendTo: "body",
							helper: OC.ContactsPlus.DragElement,
							cursor: "move",
							delay: 500,
							start: function(event, ui) {
								ui.helper.addClass('draggingContact');
							},
							stop: function(event, ui){
								if($('.ui-selected').length>0){
									// $('.contactsrow .contact-select').removeAttr('checked').removeClass('ui-selected');	
								}
							}
						});
						
						$('li.contactsrow.visible .contact-select').on('change',function(){
				   	     	if($(this).is(':checked')){
				   	     		
				   	     		$('label[for="chk-'+$(this).val()+'"]').addClass('ui-selected');
				   	     	}else{
				   	     		
				   	     		$('label[for="chk-'+$(this).val()+'"]').removeClass('ui-selected');
				   	     	}
				   	     	
				   	     });
				   	     
						$(".contactsrow .fullname a, .contactsrow .rowBody").on('click',function(){
							$CardId=$(this).closest('.container').attr('data-contactid');
							OC.ContactsPlus.showContact($CardId,null);
						 });
						 
						 $(".contactsrow .option a.delete").on('click',function(){
							 $CardId=$(this).closest('.container').attr('data-contactid');
							
							OC.ContactsPlus.deleteContact($CardId);
						 });
						 
						 $(".contactsrow .option a.edit").on('click',function(){
							 $CardId=$(this).closest('.container').attr('data-contactid');
							
							OC.ContactsPlus.editContact($CardId);
						 });
						 
						 $(".contactsrow a.favourite").on('click',function(){
							 $CardId=$(this).closest('.container').attr('data-contactid');
							 $Letter = $(this).closest('.container').attr('data-letter');
							 
							  if($(this).find('i.ioc-star').hasClass('yellow')){
							  		 OC.Tags.removeFromFavorites($CardId,OC.ContactsPlus.appName).then(function(){
							  		 $('.container[data-contactid='+$CardId+']').find('i.ioc-star').removeClass('yellow');
							  		 	var iFavCounter=parseInt($('#cgroups li[data-id=fav]').find('.groupcounter').text());
										$('#cgroups li[data-id=fav]').find('.groupcounter').text((iFavCounter - 1));
										if($('#cgroups li[data-id="fav"]').hasClass('isActiveGroup')){
											$('.container[data-contactid='+$CardId+']').closest('li.contactsrow').addClass('hidden').removeClass('visible');
											if($('.container[data-letter="'+$Letter+'"]').closest('li.contactsrow.visible').length === 0){
								         		$('.letter[data-scroll="'+$Letter+'"]').addClass('hidden');
								         		$('#alphaScroll li[data-letter="'+$Letter+'"]').removeClass('bLetterActive');
								         	}
										}
										iFavCounter=parseInt($('#cgroups li[data-id=fav]').find('.groupcounter').text());
										if(iFavCounter == 0){
											$('#cgroups li[data-id="fav"]').remove();
											//OC.ContactsPlus.loadContacts(addrbkId,'all',1,0);
										}
							  		 });
							  }else{
								 
								  $(this).find('i.ioc-star').addClass('yellow');
								   OC.Tags.addToFavorites($CardId,OC.ContactsPlus.appName).then(function(){
									   	 if($('#cgroups li[data-id="fav"]').length > 0){
											var iFavCounter=parseInt($('#cgroups li[data-id=fav]').find('.groupcounter').text());
											$('#cgroups li[data-id=fav]').find('.groupcounter').text((iFavCounter +1));
										}else{
											var aGroups = OC.ContactsPlus.getGroups();
											OC.ContactsPlus.buildGroupList(aGroups);
										}
								   });
								  
							  }
							  
							  return false;
						});
						 
						 $('#rightcontentSideBar li').removeClass('bLetterActive');
						 $('.letter').each(function(i,el){
							if(!$(el).hasClass('hidden')){
								existLetter=$(el).attr('data-scroll');
								$('#rightcontentSideBar li[data-letter="'+existLetter+'"]').addClass('bLetterActive');
							}
						});
						if(bCount === 1){
							if(addrbkId === 0){
								addrbkId=$('.isActiveABook').attr('data-adrbid');
							}
							
						  OC.ContactsPlus.buildCounterGroups(addrbkId);
						}
						
						if(bCount === 2){
							var iCounterGroup = parseInt($('#cgroups li[data-id="'+grpId+'"]').find('.groupcounter').text());
							$('#cgroups li[data-id="'+grpId+'"]').find('.groupcounter').text((iCounterGroup+1));
							var iCounterAddrBook = parseInt($('#cAddressbooks li[data-adrbid="'+addrbkId+'"]').find('.groupcounter').text());
							$('#cAddressbooks li[data-adrbid="'+addrbkId+'"]').find('.groupcounter').text((iCounterAddrBook + 1));
						}
						
						if(id > 0){
						  $('#rightcontent').scrollTo('.container[data-contactid='+id+']',800);
						 }
						 
						 if($('#showMap').hasClass('isMap')){
					   	  	if(OC.ContactsPlus.mapObject !== null){
					   	  		OC.ContactsPlus.aPinsMap = null;
					   	 	 	OC.ContactsPlus.aPinsMap = {};
					   	 	 	OC.ContactsPlus.mapObject.removeLayer(OC.ContactsPlus.layerMarker);
					   	 	 	OC.ContactsPlus.initMapList();
					   	  	}
					   	  	
						   	  	
					   	  	
					   	  	 
					   	  }	
						 	
				   });
			   }else{
			   	 
			   	 $('#loading').hide();
			   	 if(grpId !== 'all' ){
				   	 $('li.letter').addClass('hidden');
				   	 $('.contactsrow').addClass('hidden').removeClass('visible');
				   	 $('#alphaScroll li').removeClass('bLetterActive');
			   		
				   	 var sSelector = '';
				   	 if(grpId === 'none'){
				   		sSelector = '.contactsrow .categories.hidden';
				   	 }else if(grpId === 'fav'){
				   	 	sSelector = '.contactsrow .ioc-star.yellow';
				   	 }else{
				   	 	sSelector = '.contactsrow .colorgroup[data-category="'+grpId+'"]';
				   	 }
				   	
				   	 if($(sSelector).length > 0){
						 $('li span.noitem').addClass('hidden');
						
						 $(sSelector).each(function(i,el){
				   	 		$(el).closest('li').addClass('visible').removeClass('hidden');
				   	 		var letter = $(el).closest('.container').data('letter');
				   	 		
				   	 		if($('li.letter[ data-scroll="'+letter+'"]').hasClass('hidden')){
					   	 		$('li.letter[ data-scroll="'+letter+'"]').removeClass('hidden');
					   	 		$('#alphaScroll li[data-letter="'+letter+'"]').addClass('bLetterActive');
					   	 	}
				   	 	});
			   	 	}else{
			   	 		$('li span.noitem').removeClass('hidden');
			   	 	}
		   	 	}
		   	 	 if(grpId === 'all' ){
		   	 	 	  $('.contactsrow').addClass('visible').removeClass('hidden');
		   	 	 	  $('#alphaScroll li').removeClass('bLetterActive');
		   	 	 	  $('li.letter').addClass('hidden');
		   	 	 	  $('li span.noitem').addClass('hidden');
		   	 	 	    
		   	 	 	  $('.contactsrow .container').each(function(i,el){
		   	 	 	  		var letter = $(el).data('letter');
		   	 	 	  		if($('li.letter[ data-scroll="'+letter+'"]').hasClass('hidden')){
				   	 		$('li.letter[ data-scroll="'+letter+'"]').removeClass('hidden');
				   	 		$('#alphaScroll li[data-letter="'+letter+'"]').addClass('bLetterActive');
				   	 	}
		   	 	 	  });
		   	 	 }
		   	 	 if($('#showMap').hasClass('isMap')){
		   	 	 	OC.ContactsPlus.aPinsMap = null;
		   	 	 	OC.ContactsPlus.aPinsMap = {};
		   	 	 	OC.ContactsPlus.mapObject.removeLayer(OC.ContactsPlus.layerMarker);
		   	 	 	OC.ContactsPlus.initMapList();
		   	 	 }
		   	 	 
			   }
		},		
	newContact:function(){
			 $('#loading').show();
			 
			OC.ContactsPlus.destroyExisitingPopover();
			
			$.ajax({
						type : 'POST',
						url : OC.generateUrl('apps/'+OC.ContactsPlus.appName+'/getnewformcontact'),
						data:{},
						success : function(data) {
							 $('#loading').hide();
							$("#contact_details").html(data);
							$("#contact_details").addClass('isOpenDialog');
							
							$('#contact_details').dialog({
								height:'auto',
								width:450,
								modal:true,
								'title': "Neuer Kontakt",
							});
							var winWidth=$(window).width();
							var winHeight=550;
							if(winWidth <= 480){
								 winHeight=$(window).height();
									$('#contact_details').dialog('option',{"width":winWidth,'height':winHeight});
									$('#new-contact .innerContactontent').height((winHeight-100));
							}
							
							var aktAddrBookId=$('#cAddressbooks li.isActiveABook').attr('data-adrbid');
							$('select[name="addressbooks"]').val(aktAddrBookId);
							 $(".innerContactontent").niceScroll();
							 $('.additionalField').hide();
							 $('#showAdditionalFieds').hide();
							 $('#selectedContactgroup').val($('#cgroups li.isActiveGroup').attr('data-id'));
							  
							 $('.additionalFieldsRow').on('click',function(){
							 	   if( $(this).hasClass('activeAddField') ){
							 	      $(this).removeClass('activeAddField');
							 	      $('.additionalField[data-addfield="'+$(this).attr('data-id')+'"] ').hide();
							 	   }else{
							 	   	  $(this).addClass('activeAddField');
							 	      $('.additionalField[data-addfield="'+$(this).attr('data-id')+'"] ').show();
							 	   }
							 });
							 
							OC.ContactsPlus.generateSelectList('#phone-typeselect-0','#phonetype-0');
							OC.ContactsPlus.deletePropertyHandler('.delete-phone','#phone-container-');
							OC.ContactsPlus.addPropertyHandler('.add-phone','phone');
							
							OC.ContactsPlus.generateSelectList('#email-typeselect-0','#emailtype-0');
							OC.ContactsPlus.deletePropertyHandler('.delete-email','#email-container-');
							OC.ContactsPlus.addPropertyHandler('.add-email','email');
							
							OC.ContactsPlus.generateSelectList('#url-typeselect-0','#urltype-0');
							OC.ContactsPlus.deletePropertyHandler('.delete-url','#url-container-');
							OC.ContactsPlus.addPropertyHandler('.add-url','url');
							
							OC.ContactsPlus.generateSelectList('#addr-typeselect-0','#addrtype-0');
							OC.ContactsPlus.deletePropertyHandler('.delete-addr','#addr-container-');
							OC.ContactsPlus.addPropertyHandler('.add-addr','addr');
							
							OC.ContactsPlus.generateSelectList('#im-typeselect-0','#imtype-0');
							OC.ContactsPlus.deletePropertyHandler('.delete-im','#im-container-');
							OC.ContactsPlus.addPropertyHandler('.add-im','im');
							
							OC.ContactsPlus.generateSelectList('#cloud-typeselect-0','#cloudtype-0');
							OC.ContactsPlus.deletePropertyHandler('.delete-cloud','#cloud-container-');
							OC.ContactsPlus.addPropertyHandler('.add-cloud','cloud');
							
							
							$('#newContact-morefields').on('click',function(){
							    $("#showAdditionalFieds").toggle('fast');
							 });
							  
							$('#newContact-submit').on('click',function(){
							if($('#lname').val() !== '' || $('#firm').val() !== ''){
								      OC.ContactsPlus.SubmitForm('newitContact', '#contactForm', '#contact_details');
								}else{
									OC.ContactsPlus.showMeldung(t(OC.ContactsPlus.appName,'Name is missing'));
								}
							
						});
						
					   $('#newContact-cancel').on('click',function(){
							$("#contact_details").html('');
							$("#contact_details").removeClass('isOpenDialog');
						   $("#contact_details").dialog('close');
					  });
					  
					 
					}
			});
			return false;
		
	},
	deletePropertyHandler:function(buttonClass,containerId){
		$(buttonClass).on('click',function(){
		 	var iDelVal=$(this).attr('data-del');
		 	$(containerId+iDelVal).remove();
		 });
	},
	addPropertyHandler:function(buttonClass,propType){
		
		
		$(buttonClass).on('click',function(){
		 	var iAddNew = -1;
			$('.'+propType+'-container').each(function(i,el){
					var elVal = parseInt($(this).attr('data-id'));
					if(elVal > iAddNew){
						iAddNew = elVal;
					}
			});
			iAddNew = iAddNew +1;
		
		 	var iAddVal=parseInt($(this).attr('data-add'));
		 	
		 	var myClone = $('#'+propType+'-container-'+iAddVal).clone();
			 	
			 	myClone.find('.'+propType+'-type').attr({
			 		'name':propType+'type['+iAddNew+']',
			 		'id':propType+'type-'+iAddNew,
			 	});
			 	
			 	myClone.find('.'+propType+'-select').attr({
			 		'id':propType+'-typeselect-'+iAddNew,
			 	});
			 	
			 	if(propType !== 'addr'){
				 	myClone.find('.'+propType+'-val').attr({
				 		'name':propType+'['+iAddNew+']',
				 		'id':propType+'-'+iAddNew,
				 	}).val('');
			 	}else{
			 		myClone.find('.'+propType+'-val-street').attr({
				 		'name':propType+'['+iAddNew+'][street]',
				 	}).val('');
				 	myClone.find('.'+propType+'-val-city').attr({
				 		'name':propType+'['+iAddNew+'][city]',
				 	}).val('');
				 	myClone.find('.'+propType+'-val-postal').attr({
				 		'name':propType+'['+iAddNew+'][postal]',
				 	}).val('');
				 	myClone.find('.'+propType+'-val-state').attr({
				 		'name':propType+'['+iAddNew+'][state]',
				 	}).val('');
				 	myClone.find('.'+propType+'-val-country').attr({
				 		'name':propType+'['+iAddNew+'][country]',
				 	}).val('');
			 	}
			 	
			 	myClone.find('.'+propType+'-pref').removeAttr('checked').attr({'id':propType+'Pref-'+iAddNew}).val('phone_'+iAddNew);
			 	myClone.find('.'+propType+'-labelpref').removeAttr('for').attr({'for':propType+'Pref-'+iAddNew}); 
			 	myClone.attr({'id':propType+'-container-'+iAddNew,'data-id':iAddNew});
			 	myClone.find('.add-'+propType).remove();
			 	myClone.find('.delete-'+propType).attr('data-del',iAddNew).on('click',function(){
				 	var iDelVal=$(this).attr('data-del');
				 	
				 	$('#'+propType+'-container-'+iDelVal).remove();
				 });
			 	
		 		$('#'+propType+'-container-'+iAddVal).after(myClone);
		 		
		 		OC.ContactsPlus.generateSelectList('#'+propType+'-typeselect-'+iAddNew,'#'+propType+'type-'+iAddNew);
		 });
							 
		
	},
	editContact:function(iCard){
			
			 
			OC.ContactsPlus.destroyExisitingPopover();
			
			var sPlacement = 'auto';
			var defaultWidth = 480;
			if($(window).width() < 480){
				sPlacement = 'bottom';
				 defaultWidth = $(window).width() - 10;
			}
			var sConstrain = 'vertical';
			
			
			OC.ContactsPlus.popOverElem = $('.fullname[data-id="'+iCard+'"] a');
			
			OC.ContactsPlus.popOverElem.webuiPopover({
				url : OC.generateUrl('apps/'+OC.ContactsPlus.appName+'/geteditformcontact'),
				multi:false,
				closeable:false,
				animation:'pop',
				placement:sPlacement,
				constrain:sConstrain,
				cache:false,
				width:defaultWidth,
				type:'async',
				trigger:'manual',
				async:{
					type:'POST',
					data:{'id':iCard},
					success:function(that,data){
					  
					   that.displayContent();
					   $(".innerContactontent").niceScroll();
							if ($('#imgsrc').val() != '') {
								OC.ContactsPlus.imgSrc = $('#imgsrc').val();
								OC.ContactsPlus.imgMimeType = $('#imgmimetype').val();
								OC.ContactsPlus.ContactPhoto.loadPhoto();
							}
							
							 $('.additionalField').hide();
							 $('#showAdditionalFieds').hide();
							 $('#selectedContactgroup').val($('#cgroups li.isActiveGroup').attr('data-id'));
							
							 $('.activeAddFieldEdit').show();
							 
							 OC.ContactsPlus.deletePropertyHandler('.delete-phone','#phone-container-');
							 OC.ContactsPlus.deletePropertyHandler('.delete-email','#email-container-');
							 OC.ContactsPlus.deletePropertyHandler('.delete-url','#url-container-');
							 OC.ContactsPlus.deletePropertyHandler('.delete-addr','#addr-container-');
							 OC.ContactsPlus.deletePropertyHandler('.delete-im','#im-container-');
							 OC.ContactsPlus.deletePropertyHandler('.delete-cloud','#cloud-container-');
							 
							 OC.ContactsPlus.addPropertyHandler('.add-phone','phone');
							 OC.ContactsPlus.addPropertyHandler('.add-email','email');
							 OC.ContactsPlus.addPropertyHandler('.add-url','url');
							 OC.ContactsPlus.addPropertyHandler('.add-addr','addr');
							 OC.ContactsPlus.addPropertyHandler('.add-im','im');
							 OC.ContactsPlus.addPropertyHandler('.add-cloud','cloud');
							 
							 $('.additionalFieldsRow').on('click',function(){
							 	   if( $(this).hasClass('activeAddField') ){
							 	      $(this).removeClass('activeAddField');
							 	      $('.additionalField[data-addfield="'+$(this).attr('data-id')+'"] ').hide();
							 	   }else{
							 	   	  $(this).addClass('activeAddField');
							 	      $('.additionalField[data-addfield="'+$(this).attr('data-id')+'"] ').show();
							 	   }
							 });
							 //new
							 var iPCount=$('.phone-container').length;
							 for(var i=0;i<iPCount; i++){
							 	OC.ContactsPlus.generateSelectList('#phone-typeselect-'+i,'#phonetype-'+i);
							 }
							 
							
							 var iECount=$('.email-container').length;
							 for(var i=0;i<iECount; i++){
							 	OC.ContactsPlus.generateSelectList('#email-typeselect-'+i,'#emailtype-'+i);
							 }
							 
							 var iUrlCount=$('.url-container').length;
							 for(var i=0;i<iUrlCount; i++){
							 	OC.ContactsPlus.generateSelectList('#url-typeselect-'+i,'#urltype-'+i);
							 }
							 
							 var iACount=$('.addr-container').length;
							 for(var i=0;i<iACount; i++){
							 	OC.ContactsPlus.generateSelectList('#addr-typeselect-'+i,'#addrtype-'+i);
							 }
							 
							 var iMCount=$('.im-container').length;
							 for(var i=0;i<iMCount; i++){
							 	OC.ContactsPlus.generateSelectList('#im-typeselect-'+i,'#imtype-'+i);
							 }
							 
							 var iClCount=$('.cloud-container').length;
							 for(var i=0;i<iClCount; i++){
							 	OC.ContactsPlus.generateSelectList('#cloud-typeselect-'+i,'#cloudtype-'+i);
							 }
							 
							
							
							
							
							$('#editContact-morefields').on('click',function(){
							    $("#showAdditionalFieds").toggle('fast');
							 });
							  
							$('#editContact-submit').on('click',function(){
							if($('#lname').val() !== '' || $('#firm').val() !== ''){
								      OC.ContactsPlus.SubmitForm('editContact', '#contactForm', '#contact_details');
								}else{
									OC.ContactsPlus.showMeldung(t(OC.ContactsPlus.appName,'Name is missing'));
								}
						});
					   $('#editContact-cancel').on('click',function(){
							OC.ContactsPlus.destroyExisitingPopover();
					  });
					  
					  		OC.ContactsPlus.ContactPhoto.loadActionPhotoHandlers();
							OC.ContactsPlus.ContactPhoto.loadPhotoHandlers();
							
							
							$('#phototools li a').click(function() {
								$(this).tipsy('hide');
							});
							
							$('#contactPhoto').on('mouseenter',function(){
								$('#phototools').slideDown(200);
							});
							$('#contactPhoto').on('mouseleave',function(){
								$('#phototools').slideUp(200);
							});
							
								$('#phototools').hover(
									function () {
										$(this).removeClass('transparent');
									},
									function () {
										$(this).addClass('transparent');
									}
								);
								
							 that.reCalcPos();	
					   
					  }
					 }
			}
				
			).webuiPopover('show');
		
	},
	deleteContact:function(iCardId){
			
			var iCount = 1;
		 	if($('.visible .ui-selected').length>0){
		 		iCount = $('.visible .ui-selected').length;
		 	}	
		 $( "#dialogSmall" ).html( t(OC.ContactsPlus.appName, 'Please choose: contact delete or remove all groups from contact'));
	  	 
	  	 
	  	  $( "#dialogSmall" ).dialog({
			resizable: false,
			title : t(OC.ContactsPlus.appName, 'Delete Contact or From Groups'),
			width:500,
			height:200,
			modal: true,
			buttons: [
						 { text:t(OC.ContactsPlus.appName, 'Delete Contact')+' ('+iCount+')','class':'delButton', click: function() {
						 	
						 	var oDialog=$(this);
						 	var delId= 0;
						  	 	if($('.visible .ui-selected').length>0){
									$('.visible .ui-selected').each(function(i,el){
										if(delId === 0){
											delId = $(el).data('conid');
										}else{
											delId += ','+$(el).data('conid');
										}
									});
								 $('.contactsrow .contact-select').removeAttr('checked');	
								 $('.contactsrow .is-checkbox').removeClass('ui-selected');
								 $('#chk-all').removeAttr('checked');	
								}else{
									delId = iCardId;
								}
								
								 $.post(OC.generateUrl('apps/'+OC.ContactsPlus.appName+'/deletecontact'),{'id':delId},function(jsondata){
										if(jsondata.status == 'success'){
											oDialog.dialog( "close" );
											if(jsondata.data.count == 1){
												if(jsondata.data.groups!=''){
													temp=jsondata.data.groups.split(',');
													$.each(temp,function(i,el){
														var iCounter=parseInt($('#cgroups li[data-id="'+el+'"]').find('.groupcounter').text());
					                                    $('#cgroups li[data-id="'+el+'"]').find('.groupcounter').text((iCounter - 1));
													});
												}else{
													//not in group
													var iCounterNone=parseInt($('#cgroups li[data-id="none"]').find('.groupcounter').text());
					                            	$('#cgroups li[data-id="none"]').find('.groupcounter').text((iCounterNone - 1));
												}
												var iCounterAll=parseInt($('#cgroups li[data-id="all"]').find('.groupcounter').text());
												$('#cgroups li[data-id="all"]').find('.groupcounter').text((iCounterAll - 1));
												var iCounterAddrBook=parseInt($('#cAddressbooks li.isActiveABook').find('.groupcounter').text());
												$('#cAddressbooks li.isActiveABook').find('.groupcounter').text((iCounterAddrBook - 1));
												if($('.container[data-contactid='+iCardId+'] a.favourite i.yellow').length > 0){
													OC.Tags.removeFromFavorites(iCardId,OC.ContactsPlus.appName).then(function(){
										  		 	var iFavCounter=parseInt($('#cgroups li[data-id=fav]').find('.groupcounter').text());
													$('#cgroups li[data-id=fav]').find('.groupcounter').text((iFavCounter - 1));
													if($('#cgroups li[data-id="fav"]').hasClass('isActiveGroup')){
															$('.container[data-contactid='+$CardId+']').closest('li.contactsrow').remove();
													}
													iFavCounter=parseInt($('#cgroups li[data-id=fav]').find('.groupcounter').text());
													if(iFavCounter == 0){
														$('#cgroups li[data-id=fav]').remove();
														
													}
													$('.container[data-contactid='+iCardId+']').closest('li.contactsrow').remove();
										  		 });
												}else{
													$('.container[data-contactid='+iCardId+']').closest('li.contactsrow').remove();
												}
												
												if($('.container[data-letter="'+jsondata.data.letter+'"]').closest('li.contactsrow.visible').length === 0){
									         		$('.letter[data-scroll="'+jsondata.data.letter+'"]').addClass('hidden');
									         		$('#alphaScroll li[data-letter="'+jsondata.data.letter+'"]').removeClass('bLetterActive');
									         	}
									         	
												 OC.ContactsPlus.showMeldung(t(OC.ContactsPlus.appName,'Contacts delete success!'));
										}else{
											OC.ContactsPlus.loadContacts(jsondata.data.addrId,'',1,0);
											OC.ContactsPlus.showMeldung(t(OC.ContactsPlus.appName,'Contacts delete success!'));
										}
										}
										else{
											alert(jsondata.data.message);
										}
							        });
						  	 }
						 },
						  { text:t(OC.ContactsPlus.appName, 'Delete From All Groups')+' ('+iCount+')', click: function() { 
						  	  var oDialog=$(this);
						  	 	var delId= 0;
						  	 	if($('.visible .ui-selected').length>0){
									$('.visible .ui-selected').each(function(i,el){
										if(delId === 0){
											delId = $(el).data('conid');
										}else{
											delId += ','+$(el).data('conid');
										}
									});
								  $('.contactsrow .contact-select').removeAttr('checked');
								  $('.contactsrow .is-checkbox').removeClass('ui-selected');	
								   $('#chk-all').removeAttr('checked');	
								}else{
									delId = iCardId;
								}
								
								 $.post(OC.generateUrl('apps/'+OC.ContactsPlus.appName+'/deletecontactfromgroup'),{'id':delId},function(jsondata){
										if(jsondata.status == 'success'){
											oDialog.dialog( "close" );
											if(jsondata.data.count == 1){
												if(jsondata.data.scat !== ''){
													temp=jsondata.data.scat.split(',');
												
													$.each(temp,function(i,el){
														var iCounter=parseInt($('#cgroups li[data-id="'+el+'"]').find('.groupcounter').text());
					                                    $('#cgroups li[data-id="'+el+'"]').find('.groupcounter').text((iCounter - 1));
													});
													var iCounter=parseInt($('#cgroups li[data-id="none"]').find('.groupcounter').text());
					                                 $('#cgroups li[data-id="none"]').find('.groupcounter').text((iCounter + 1));
												}
												$('.container[data-contactid='+iCardId+']').find('.categories').html('');
												$('.container[data-contactid='+iCardId+']').find('.categories').addClass('hidden');
												if(!$('#cgroups li[data-id="all"]').hasClass('isActiveGroup')){
													$('.container[data-contactid='+iCardId+']').closest('li.contactsrow').removeClass('visible').addClass('hidden');
												}
												
												if($('.container[data-letter="'+jsondata.data.letter+'"]').closest('li.contactsrow.visible').length === 0){
									         		$('.letter[data-scroll="'+jsondata.data.letter+'"]').addClass('hidden');
									         		$('#alphaScroll li[data-letter="'+jsondata.data.letter+'"]').removeClass('bLetterActive');
									         	}
								         }else{
								         	OC.ContactsPlus.loadContacts(jsondata.data.addrId,'',1,0);
								         }
											
										}
										else{
											alert(jsondata.data.message);
										}
							        });
						  	 }
						},
						{ text:t(OC.ContactsPlus.appName, 'Cancel'), click: function() { 
							$( this ).dialog( "close" );
							if($('.visible .ui-selected').length>0){
								 $('.contactsrow .contact-select').removeAttr('checked');	
								 $('.contactsrow .is-checkbox').removeClass('ui-selected');
								 $('#chk-all').removeAttr('checked');	
							}
						 } } 
						 ],
		});
  	 
		return false;
	},
	SubmitForm: function(VALUE, FormId, UPDATEAREA) {
		
		         actionFile='newcontactsave';
		         if (VALUE == 'newitContact') {
		         	 actionFile='newcontactsave';
		         }
		          if (VALUE == 'editContact') {
		         	 actionFile='editcontactsave';
		         }
		       
				$(FormId + ' input[name=hiddenfield]').attr('value', VALUE);
				$.ajax({
					type : 'POST',
					url : OC.generateUrl('apps/'+OC.ContactsPlus.appName+'/'+actionFile),
					data : $(FormId).serialize(),
					success : function(jsondata) {
		               
				          var activeGroup = $('#cgroups li.isActiveGroup').attr('data-id');
				        
				         if(activeGroup === 'fav'){
				         	activeGroup = 'all';
				         }
				         
						if (VALUE == 'newitContact') {
							OC.ContactsPlus.showMeldung(t(OC.ContactsPlus.appName,'Contact creating success!'));
							$("#contact_details").dialog('close');
							$("#contact_details").removeClass('isOpenDialog');
							
							if($('#cAddressbooks li.isActiveABook').data('adrbid') !== jsondata.data.addrBookId){
								$('#cAddressbooks li').removeClass('isActiveABook');
						  	    $('#cAddressbooks li[data-adrbid="'+jsondata.data.addrBookId+'"]').addClass('isActiveABook');
								OC.ContactsPlus.loadContacts(jsondata.data.addrBookId,'',2,jsondata.data.id);
							}else{
								
								if($('.letter[data-scroll="'+jsondata.data.letter+'"]').length === 0){
									
									
									OC.ContactsPlus.loadContacts($('#cAddressbooks li.isActiveABook').data('adrbid'),'',1,jsondata.data.id);
									//var letter = $('<li/>').attr({'data-scroll':jsondata.data.letter}).html('<span>'+jsondata.data.letter+'</span>').addClass('letter bLetterActive');
									//$('#rightcontent ul').append(letter);
									//$('.letter[data-scroll='+jsondata.data.letter+']').removeClass('hidden');
									//$('#alphaScroll li[data-letter="'+jsondata.data.letter+'"]').addClass('bLetterActive');
								}
								if($('.letter[data-scroll="'+jsondata.data.letter+'"]').hasClass('hidden')){
									$('.letter[data-scroll="'+jsondata.data.letter+'"]').removeClass('hidden');
									$('#alphaScroll li[data-letter="'+jsondata.data.letter+'"]').addClass('bLetterActive');
								}
								var listView = '';
								if($('#showList i').hasClass('isActiveListView')){
									var listView = 'listview';
								}
								if($('li span.noitem').length === 1){
									$('li span.noitem').addClass('hidden');
								}
			
								$('.letter[data-scroll="'+jsondata.data.letter+'"]').after($('<li class="contactsrow '+listView+'">'+jsondata.data.card+'</li>'));
								OC.ContactsPlus.initActionhandlerSingleCard(jsondata.data.id);
								$('#rightcontent').scrollTo('.letter[data-scroll="'+jsondata.data.letter+'"]',800);
								$('#alphaScroll li').removeClass('isScrollTo');
								$('#alphaScroll li[data-letter="'+jsondata.data.letter+'"]').addClass('isScrollTo');
								var counter=$('.contactsrow').length - $('.contactsrow.hidden').length;
						 		$('#cgroups li[data-id="'+activeGroup+'"]').find('.groupcounter').text(counter);
								var iCount =parseInt($('#cAddressbooks li.isActiveABook').find('.groupcounter').text());
								$('#cAddressbooks li.isActiveABook').find('.groupcounter').text(iCount +1);
								$('#cgroups li[data-id="all"]').find('.groupcounter').text(iCount +1);
							}
							//isScrollTo
							//OC.ContactsPlus.loadContacts($('#cAddressbooks li.isActiveABook').attr('data-adrbid'),activeGroup,2,jsondata.data.id);
							$("#contact_details").dialog('close');
							$("#contact_details").html('');
							$("#contact_details").removeClass('isOpenDialog');
							OC.ContactsPlus.destroyExisitingPopover();
							
							return false;	
						}
						if (VALUE == 'editContact') {
					         OC.ContactsPlus.showMeldung(t(OC.ContactsPlus.appName,'Contacts update success!'));
					         if(jsondata.data.newAddrBookId ===''){
					         $('.container[data-contactid='+jsondata.data.id+']').addClass('toremove').hide();
					         
					         var oldLetter = $('.toremove').data('letter');
					         
					         if(oldLetter !== jsondata.data.letter){
					         	if($('.letter[data-scroll="'+jsondata.data.letter+'"]').length === 0){
									
									OC.ContactsPlus.loadContacts($('#cAddressbooks li.isActiveABook').data('adrbid'),'',1,jsondata.data.id);
								}
					         	var listView = '';
								if($('#showList i').hasClass('isActiveListView')){
									var listView = 'listview';
								}
					         	 $('.letter[data-scroll="'+jsondata.data.letter+'"]').after($('<li class="contactsrow '+listView+'">'+jsondata.data.card+'</li>'));
					         	 $('.toremove[data-contactid='+jsondata.data.id+']').closest('.contactsrow').remove();
					         	
					         	 if($('.container[data-letter="'+oldLetter+'"]').length === 0){
					         		$('.letter[data-scroll="'+oldLetter+'"]').addClass('hidden');
					         		$('#alphaScroll li[data-letter="'+oldLetter+'"]').removeClass('bLetterActive');
					         	}
					         	
					         	$('#rightcontent').scrollTo('.letter[data-scroll="'+jsondata.data.letter+'"]',800);
					         	$('#alphaScroll li').removeClass('isScrollTo');
					         	$('#alphaScroll li[data-letter="'+jsondata.data.letter+'"]').addClass('isScrollTo');
					         }else{
					         	$('.container[data-contactid='+jsondata.data.id+']').after($(jsondata.data.card));
					         	 $('.toremove[data-contactid='+jsondata.data.id+']').remove();
					         }
					        
					        
					         OC.ContactsPlus.initActionhandlerSingleCard(jsondata.data.id);
					         
					        
							}else{
					       	  $('#cAddressbooks li').removeClass('isActiveABook');
						  	  $('#cAddressbooks li[data-adrbid="'+jsondata.data.newAddrBookId+'"]').addClass('isActiveABook');
					       	 OC.ContactsPlus.loadContacts(jsondata.data.newAddrBookId,'',1,jsondata.data.id);
					       	}
					       	OC.ContactsPlus.destroyExisitingPopover();
					       	
				         }
				         
				        return false;	
				         
						
					}
				});
				
			
		
		},
	showContact:function(iCard,evt){
			
			OC.ContactsPlus.destroyExisitingPopover();
			
			var sPlacement = 'auto';
			var defaultWidth = 440;
			if($(window).width() < 480){
				sPlacement = 'bottom';
				 defaultWidth = $(window).width() - 10;
			}
			var sConstrain = 'vertical';
			
			if(evt === null){
				OC.ContactsPlus.popOverElem = $('.fullname[data-id="'+iCard+'"] a');
			}else{
				sPlacement = 'auto';
				OC.ContactsPlus.popOverElem = $(evt.target);
				defaultWidth = 440;
				if($(window).width() < 768){
					defaultWidth = 380;
				}
				if($(window).width() < 480){
					defaultWidth = $('#searchresults').width()-40;
				
				}
			}
			
			OC.ContactsPlus.popOverElem.webuiPopover({
				url: OC.generateUrl('apps/'+OC.ContactsPlus.appName+'/showcontact/{id}',{id:iCard}),
				async:{
					type:'GET',
					success:function(that,data){
					  
					   that.displayContent();
					  
						$(".innerContactontent").niceScroll();
						
						
						 $('#selectedContactgroup').val($('#cgroups li.isActiveGroup').attr('data-id'));
						 
						OC.ContactsPlus.ContactPhoto.loadActionPhotoHandlers();
						OC.ContactsPlus.ContactPhoto.loadPhotoHandlers();
						
						if ($('#imgsrc').val() != '') {
							
							OC.ContactsPlus.imgSrc = $('#imgsrc').val();
							OC.ContactsPlus.imgMimeType = $('#imgmimetype').val();
						
							OC.ContactsPlus.ContactPhoto.loadPhoto();
						}
						
						$('#phototools li a').click(function() {
							$(this).tipsy('hide');
						});
							
						$('#contactPhoto').on('mouseenter',function(){
							$('#phototools').slideDown(200);
						});
						$('#contactPhoto').on('mouseleave',function(){
							$('#phototools').slideUp(200);
						});
						
						$('#phototools').hover(
							function () {
								$(this).removeClass('transparent');
							},
							function () {
								$(this).addClass('transparent');
							}
						);
						if(evt === null){
							$('#showContact-edit').on('click',function(){
								OC.ContactsPlus.popOverElem.webuiPopover('destroy');
								OC.ContactsPlus.popOverElem = null;
								$('#show-contact').remove();
						   		OC.ContactsPlus.editContact(iCard);
						  	});
						  	$('#showContact-delete').on('click',function(){
								OC.ContactsPlus.deleteContact(iCard);
								
						  	});
							$('#showContact-cancel').on('click',function(){
								OC.ContactsPlus.destroyExisitingPopover();
						  	});
							$('#showContact-export').on('click',function(){
								document.location.href = OC.generateUrl('apps/'+OC.ContactsPlus.appName+'/exportcontacts') + '?contactid=' + $('#photoId').val();
						  	});
						}else{
							$('#show-contact').find('#actions').remove();
						}
						
						 that.reCalcPos();
						
						return false;
					}
				},
				multi:false,
				closeable:true,
				animation:'pop',
				placement:sPlacement,
				constrain:sConstrain,
				cache:false,
				width:defaultWidth,
				type:'async',
				trigger:'manual',
			}).webuiPopover('show');
			
			
	},
	destroyExisitingPopover : function() {
			if($('.webui-popover').length>0){
				if(OC.ContactsPlus.popOverElem !== null){
					OC.ContactsPlus.popOverElem.webuiPopover('destroy');
					OC.ContactsPlus.popOverElem = null;
					if($('#show-contact').length > 0){
						$('#show-contact').remove();
					}
					if($('#edit-contact').length > 0){
						$('#edit-contact').remove();
					}
					if($('#new-contact').length > 0){
						$('#new-contact').remove();
					}
					$('.webui-popover').each(function(i,el){
						var id = $(el).attr('id');
						$('[data-target="'+id+'"]').removeAttr('data-target');
						$(el).remove();
					});
				}
			}
		},
	listView:function(){
		if(!$('#showMap').hasClass('isMap')){
			$('#showCards i').removeClass('isActiveListView');
			$('#showList i').addClass('isActiveListView');
			$('.contactsrow').addClass('listview');
			$('.letter').addClass('listview');
			$('.listview-header').show();
			
			$.post(OC.generateUrl('apps/'+OC.ContactsPlus.appname+'/changeviewcontacts'), {
				v : 'listview'
			});
				
		}
	},
	cardsView:function(){
		if(!$('#showMap').hasClass('isMap')){
			$('#showList i').removeClass('isActiveListView');
			$('#showCards i').addClass('isActiveListView');
			$('.contactsrow').removeClass('listview');
			$('.letter').removeClass('listview');
			$('.listview-header').hide();
			$.post(OC.generateUrl('apps/'+OC.ContactsPlus.appname+'/changeviewcontacts'), {
				v : 'cardview'
			});
		}
	},
	generateSelectList:function(iSelectId,iReturnId){
		 $(iSelectId+'.combobox ul').removeClass('isOpen').hide();
		 //INIT DEFAULT
		  $(iSelectId+'.combobox li').removeClass('isSelected');
		  $(iSelectId+'.combobox li').removeClass('isSelectedCheckbox');
		  var defaultVal=$(iReturnId).val();
		 
		  $(iSelectId+'.combobox li[data-id="'+defaultVal+'"]').addClass('isSelected');
		   $(iSelectId+'.combobox li[data-id="'+defaultVal+'"]').addClass('isSelectedCheckbox');
		  $(iSelectId+'.combobox').find('.selector').html($(iSelectId+'.combobox li[data-id="'+defaultVal+'"]').text());
		
		 $(iSelectId+'.combobox .comboSelHolder').on('click', function() {
			 
			if($(iSelectId+'.combobox ul').is(':visible')){
				$(iSelectId+'.combobox ul').hide();
				
			}else{
				$('.combobox ul').removeClass('isOpen').hide();
				$(iSelectId+'.combobox ul').addClass('isOpen');
				$(iSelectId+'.combobox ul').show();
				
			}
			
		 });
		 
		 $(iSelectId+'.combobox li').click(function() {
		 	 $(iSelectId+'.combobox li').removeClass('isSelected');
		 	 $(iSelectId+'.combobox li').removeClass('isSelectedCheckbox');
		 	 $(iReturnId).val($(this).data('id'));
		 	 $(this).addClass('isSelected');
		 	 $(this).addClass('isSelectedCheckbox');
			 $(this).parents(iSelectId+'.combobox').find('.selector').html($(this).text());
			 $(iSelectId+'.combobox ul').removeClass('isOpen').hide();
		 });
	},
	
	addCardToGroup:function(sCat,iCardId){
		 
		 if(sCat !== 'fav' && sCat !== 'all' && sCat !== 'none'){
			
			 $.post(OC.generateUrl('apps/'+OC.ContactsPlus.appName+'/addprobertytocontact'),{'param':'CATEGORIES','value':sCat,'cardId':iCardId},function(jsondata){
				if(jsondata.status === 'success'){
					//jsondata.data.iCounter
					var aCategory = jsondata.data.newcat;
					
					var iCounter=parseInt($('#cgroups li[data-id="'+aCategory.name+'"]').find('.groupcounter').text());
					
					$('#cgroups li[data-id="'+aCategory.name+'"]').find('.groupcounter').text((iCounter+parseInt(jsondata.data.iCounter)));
					if(parseInt(jsondata.data.iCounter) === 1){
					  
					   var newCat = $('<span class="colorgroup" data-category="'+aCategory.name+'" style="background-color:'+aCategory.bgcolor+';color:'+aCategory.color+';" title="'+aCategory.name+'">').text(aCategory.name.substring(0,1));
					   newCat.appendTo($('.container[data-contactid='+iCardId+']').find('.categories'));
					   var iNoneCounter=parseInt($('#cgroups li[data-id=none]').find('.groupcounter').text());
					   
					   if(iNoneCounter > 0 &&  $('.container[data-contactid='+iCardId+']').find('.categories').hasClass('hidden')){
					   	$('#cgroups li[data-id=none]').find('.groupcounter').text((iNoneCounter - parseInt(jsondata.data.iCounter)));
					   }
					   
					    $('.container[data-contactid='+iCardId+']').find('.categories').removeClass('hidden');
				
						if($('#cgroups li[data-id="none"]').hasClass('isActiveGroup')){
							$('.container[data-contactid='+iCardId+']').closest('li.contactsrow').addClass('hidden').removeClass('visible');
							
							var letter = $('.container[data-contactid='+iCardId+']').data('letter');
				   	 		if($('.container[data-letter="'+letter+'"]').closest('li.contactsrow.visible').length === 0){
				         		$('.letter[data-scroll="'+letter+'"]').addClass('hidden');
				         		$('#alphaScroll li[data-letter="'+letter+'"]').removeClass('bLetterActive');
				         	}
						}
					
					 OC.ContactsPlus.showMeldung(t(OC.ContactsPlus.appName,'Contact added to group ')+sCat);
					}
					if(parseInt(jsondata.data.iCounter) === 0){
						OC.ContactsPlus.showMeldung(t(OC.ContactsPlus.appName,'Contact exist on group ')+sCat);
					}
				}
				else{
					alert(jsondata.data.message);
				}
      	 });
      	}else{
      		 OC.ContactsPlus.showMeldung(t(OC.ContactsPlus.appName,'Operation not permitted'));
      	}
	},
	categoriesChanged:function(newcategories){
			
			 OC.ContactsPlus.getGroups();
		},
		getGroups:function(){
			$.post(OC.generateUrl('apps/'+OC.ContactsPlus.appName+'/getcategoriesaddrbook'),function(jsondata){
					if(jsondata.status === 'success'){
						
						OC.ContactsPlus.buildGroupList(jsondata.data.groups);
						
					}
			});
		},
	  buildGroupList:function(aGroups){
			//	var htmlCat='<li data-id="all" data-grpid="all" data-id="all"><span class="colCal">&nbsp;</span><span class="groupname">&nbsp;Alle</span><span class="groupcounter">0</span></li>';
				OC.ContactsPlus.groups = aGroups;
				var htmlCat='';
				
				$.each(aGroups,function(i, elem) {
					
					var sName=elem.name;
					if(elem.id === 'all' || elem.id === 'none' || elem.id === 'fav'){
						sName=elem.id;
					}
					
					var sIcon='<div class="colCal" style="cursor:pointer;background-color:'+elem.bgcolor+';color:'+elem.color+';">'+elem.name.substring(0,1)+'</div>';
					if(elem.id === 'fav'){
						sIcon='<i class="ioc ioc-star" style="float:left;margin-left:5px;margin-top:-3px; font-size:22px;color:#D8C101;"></i>';
					}
					if(elem.id === 'all'){
						sIcon='<i class="ioc ioc-users" style="float:left;margin-left:5px;margin-top:-4px;margin-right:5px;font-size:16px;"></i> ';
					}
					
					htmlCat+='<li class="dropcontainer" data-grpid="'+elem.id+'" data-id="'+sName+'">'+sIcon+' <span class="groupname">&nbsp;'+elem.name+'</span><span class="groupcounter">'+elem.icount+'</span></li>';
				});
			//	htmlCat+='<li data-id="none" data-grpid="none" data-id="none"><span class="colCal">&nbsp;</span><span class="groupname">&nbsp;Nicht gruppiert</span><span class="groupcounter">0</span></li>';
				$('#cgroups').html(htmlCat);
				
				$(".dropcontainer").droppable({
						activeClass: "activeHover",
						hoverClass: "dropHover",
						accept:'li.contactsrow .fullname',
						over: function( event, ui ) {
							
						},
						drop: function( event, ui ) {
							if($('.visible .ui-selected').length>0){
								$('.visible .ui-selected').each(function(i,el){
									OC.ContactsPlus.addCardToGroup($(this).attr('data-id'),$(el).data('conid'));
								}.bind(this));
							   $('.contactsrow .contact-select').removeAttr('checked');	
							   $('.contactsrow .is-checkbox').removeClass('ui-selected');
							   $('#chk-all').removeAttr('checked');	
							}else{
							OC.ContactsPlus.addCardToGroup($(this).attr('data-id'),ui.draggable.attr('data-id'));
							}
						}
			   });
				 
				 $("#cgroups").sortable({
						items: "li",
						axis: "y",
						disabled: true,
						placeholder: "ui-state-highlight"
				});
				
			$('#cgroups li').on('click',function(){
			
			var disabled = $("#cgroups").sortable( "option", "disabled" );
				if(disabled === true){	
			  	  $('#cgroups li').removeClass('isActiveGroup');
			  	  $(this).addClass('isActiveGroup');
			  	  OC.ContactsPlus.loadContacts($('#cAddressbooks li.isActiveABook').attr('data-adrbid'),$(this).attr('data-id'),0,0);
		  	 }
		  });
		 
		 OC.ContactsPlus.buildCounterGroups($('#cAddressbooks li.isActiveABook').attr('data-adrbid'));
		 
		},
		DragElement :  function(evt) {
			var moreItems='';
			if($('.visible .ui-selected').length > 0){
				moreItems= ' + '+($('.visible .ui-selected').length - 1)+' '+t(OC.ContactsPlus.appName, 'more Contacts');
			}
			return $(this).clone().text($(evt.target).text()+moreItems);
		},
		
		checkIosGroup:function(aGroups){
			var aid=$('#cAddressbooks li.isActiveABook').attr('data-adrbid');
			
			$.post(OC.generateUrl('apps/'+OC.ContactsPlus.appName+'/prepareiosgroups'),{'agroups':aGroups,'aid':aid},function(jsondata){
				if(jsondata.status == 'success'){
				}
				else{
					alert(jsondata.data.message);
				}
           });
		},
		saveSortOrderGroups:function(aSort){
			
			$.post(OC.generateUrl('apps/'+OC.ContactsPlus.appName+'/savesortordergroups'),{'jsortorder':aSort},function(jsondata){
				if(jsondata.status == 'success'){
				}
				else{
					alert(jsondata.data.message);
				}
           });
		},
		showDialogAddressbook:function(addrId,CardId){
			 $( "#dialogSmall" ).html(t(OC.ContactsPlus.appName, 'Would you like copy or move the contact to the addressbook?'));
			 
			 var iCount = 1;
			 if($('.visible .ui-selected').length>0){
			 		iCount = $('.visible .ui-selected').length;
			 }
			 
			 $( "#dialogSmall" ).dialog({
			resizable: false,
			title : t(OC.ContactsPlus.appName, 'Copy or Move Contact to Addressbook')+' ('+iCount+')',
			width:520,
			height:200,
			modal: true,
			buttons: [
						 { text:t(OC.ContactsPlus.appName, 'Copy Contact')+' ('+iCount+')', click: function() {
						 	
						 	 var oDialog=$(this);
						 	 var moveId= 0;
						  	 	if($('.visible .ui-selected').length>0){
									$('.visible .ui-selected').each(function(i,el){
										if(moveId === 0){
											moveId = $(el).data('conid');
										}else{
											moveId += ','+$(el).data('conid');
										}
									});
								 $('.contactsrow .contact-select').removeAttr('checked');	
								 $('.contactsrow .is-checkbox').removeClass('ui-selected');
								 $('#chk-all').removeAttr('checked');	
							}else{
								moveId = CardId;
							}
						 	 
						 	 
								 $.post(OC.generateUrl('apps/'+OC.ContactsPlus.appName+'/copycontact'),{'addrid':addrId,'id':moveId},function(jsondata){
										if(jsondata.status === 'success'){
											oDialog.dialog( "close" );
											var iCounterAll = parseInt($('#cAddressbooks li[data-adrbid="'+addrId+'"]').find('.groupcounter').text());
											$('#cAddressbooks li[data-adrbid="'+addrId+'"]').find('.groupcounter').text((iCounterAll + jsondata.data.count));
											OC.ContactsPlus.showMeldung(t(OC.ContactsPlus.appName,'Contact copied success!'));
										}
										if(jsondata.status === 'error'){
											oDialog.dialog( "close" );
											OC.ContactsPlus.showMeldung(jsondata.data.msg);
										}
							        });
						  	 }
						 },
						  { text:t(OC.ContactsPlus.appName, 'Move Contact')+' ('+iCount+')', click: function() { 
						  	  var oDialog=$(this);
						  	  	 var moveId= 0;
						  	 	if($('.visible .ui-selected').length>0){
									$('.visible .ui-selected').each(function(i,el){
										if(moveId === 0){
											moveId = $(el).data('conid');
										}else{
											moveId += ','+$(el).data('conid');
										}
									});
								 $('.contactsrow .contact-select').removeAttr('checked');	
								 $('.contactsrow .is-checkbox').removeClass('ui-selected');
								 $('#chk-all').removeAttr('checked');	
							}else{
								moveId = CardId;
							}
								 $.post(OC.generateUrl('apps/'+OC.ContactsPlus.appName+'/movecontact'),{'addrid':addrId,'id':moveId},function(jsondata){
										if(jsondata.status === 'success'){
											oDialog.dialog( "close" );
											if(jsondata.data.count == 1){
												 $('.container[data-contactid='+CardId+']').closest('li.contactsrow').remove();
												 var iCounterAll=parseInt($('#cAddressbooks li[data-adrbid="'+addrId+'"]').find('.groupcounter').text());
												$('#cAddressbooks li[data-adrbid="'+addrId+'"]').find('.groupcounter').text((iCounterAll + 1));
													
												OC.ContactsPlus.buildCounterGroups($('#cAddressbooks li.isActiveABook').data('adrbid'));
												OC.ContactsPlus.showMeldung(t(OC.ContactsPlus.appName,'Contact moving success!'));
											}else{
												OC.ContactsPlus.loadContacts(addrId,'',1,0);
												OC.ContactsPlus.showMeldung(t(OC.ContactsPlus.appName,'Contact moving success!'));
	
											}
										}
										if(jsondata.status === 'error'){
											oDialog.dialog( "close" );
											OC.ContactsPlus.showMeldung(jsondata.data.msg);
										}
							        });
						  	 }
						},
						{ text:t(OC.ContactsPlus.appName, 'Cancel'), click: function() { 
							$( this ).dialog( "close" ); 
							if($('.visible .ui-selected').length>0){
								 $('.contactsrow .contact-select').removeAttr('checked');	
								 $('.contactsrow .is-checkbox').removeClass('ui-selected');
								 $('#chk-all').removeAttr('checked');	
							}
							
							} } 
						 ],
		});
  	    
		return false;
			 
		},
		
	showMeldung: function(TXT) {

			var leftMove = ($(window).width() / 2) - 150;
			var myMeldungDiv = $('<div id="iMeldung" style="left:' + leftMove + 'px"></div>');
			$('#content').append(myMeldungDiv);
			$('#iMeldung').html(TXT);
		
			$('#iMeldung').animate({
				top : 00
			}).delay(3000).animate({
				top : '-100'
			}, function() {
				$('#iMeldung').remove();
			});
		
		},
		checkShowEventHash : function() {
			var id = parseInt(window.location.hash.substr(1));
			if (id) {
				OC.ContactsPlus.showContact(id,null);
			}
		},
		calcDimension:function(){
			
			var winWidth=$(window).width();
		    var winHeight=$(window).height();
		   
		    
			 $('#rightcontent').height(winHeight-148);
			 //
			 var sum=winHeight-122;
			 $('#rightcontentSideBar').height(sum);
			 var fontSize=Math.round((sum/26)-2);
			 var lineHeight=Math.round((sum/26)-1);
			 if(fontSize >= 18){
			 	fontSize=14;
			 	//lineHeight=16;
			 }
			 if(fontSize <= 12){
			 	fontSize=11;
			 	lineHeight=13;
			 }
			  $('#rightcontentSideBar li').css({'height':lineHeight+'px','line-height':lineHeight+'px','font-size':fontSize+'px'});
			 
			if(winWidth > 768) {
			
		     $('#rightcontent').width(winWidth-330);
		    // $('#contact_details').height(winHeight-90);
		     $('#rightcontentTop').width(winWidth-300);
		     $('#first-group').css({'margin-left':'19px'});
		   //  $('.contactsrow').removeClass('listCardview');
		}else{
			 if($("#contact_details").is(':visible')){ 
		    	if(winWidth < 480){
		    		$('#contact_details').dialog('option',"width",(winWidth-40));
		    	}else{
		    		$('#contact_details').dialog('option',"width",450);
		    	}
		    }
		    $('#rightcontentTop').width(winWidth-60);
			$('#rightcontent').width(winWidth-60);
			
			//$('.contactsrow').addClass('listCardview');
			//$('#first-group').css({'margin-left':'30px'});
		}
		
		},
		initSearch:function(){
				jQuery.expr[':'].Contains = function(a,i,m){
				      return (a.textContent || a.innerText || "").toUpperCase().indexOf(m[3].toUpperCase())>=0;
				  };
				  
				$('#contactsearch').change( function () {
		        var filterValue = $(this).val();
		        var aFilterField=['.telsearch','.emailsearch','.name','.fullname','.address','.hidden-category'];
		        var saveVal = '';
		         if(filterValue.length > 2){	
			        $('.letter').addClass('hidden');
				        $(aFilterField).each(function(i,el){
					         if($('.contactsrow').find(el+":Contains(" + filterValue + ")").length > 0){
					          	saveVal = el;
					        }
				      });
				       if(saveVal !==''){ 	
				      		$('.contactsrow').find(saveVal+":not(:Contains(" + filterValue + "))").parent().parent().parent().addClass('hidden');

				      		$('.contactsrow').find(saveVal+":Contains(" + filterValue + ")").parent().parent().parent().removeClass('hidden');
				      }
				         
		          } else {
			          $('.contactsrow').find(".rowHeader").parent().parent().removeClass('hidden');
			           $('.letter').removeClass('hidden');
			        }
		        return false;
		      })
		    .keyup( function (event) {
		        if (event.which == 27){
		        	$(this).val('');
		        }
		         $(this).change();
		    });
  },
	showMap : function() {
	if(!$('#showMap').hasClass('isMap')){
   	  	if(OC.ContactsPlus.mapObject !== null){
   	  		OC.ContactsPlus.mapObject.removeLayer(OC.ContactsPlus.layerMarker);
   	  	}
   	  	$('#showMap').addClass('isMap');
   	  	$('#map').height($('#app-content').height()-10);
   	  	 $('#alphaScroll').hide();
   	  	 $('#rightcontent').hide();
   	  	 $('#contactsearch').hide();
   	  	if($('#showList i.ioc').hasClass('isActiveListView')){
   	  	 	 $('.listview-header').hide();
   	  	 }
   	  	 $('#map').show();
   	  	
   	  	  OC.ContactsPlus.initMapList();
   	  	 
   	  }else{
   	  	$('#showMap').removeClass('isMap');
   	  	OC.ContactsPlus.aPinsMap = null;
		OC.ContactsPlus.aPinsMap = {};
		OC.ContactsPlus.mapObject.removeLayer(OC.ContactsPlus.layerMarker);
   	  	
   	  	$('#map').hide();
   	  	$('#rightcontent').show();
   	  	$('#alphaScroll').show();
      	$('#contactsearch').show();
      	if($('#showList i.ioc').hasClass('isActiveListView')){
   	  	 	 $('.listview-header').show();
   	  	 }
   	  }
	},
	initMapList : function() {
	var attribution = '&copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>';
	    if( OC.ContactsPlus.mapObject == null){
		   OC.ContactsPlus.mapObject = L.map('map').setView([51.505, -0.09], 2);
				L.tileLayer('http://otile{s}.mqcdn.com/tiles/1.0.0/map/{z}/{x}/{y}.png', {
			    attribution:attribution,
			    maxZoom: 18,
			    subdomains : "1234"
		   }).addTo(OC.ContactsPlus.mapObject);
	       
	  }else{
	  	  OC.ContactsPlus.mapObject.setView([51.505, -0.09], 2);
	  }
	  
	  	
	  	
	  	$('.contactsrow.visible .container').each(function(i,el){
	  		var id= $(el).data('contactid');
	  		var lat = $(el).data('lat');
	  		var lon = $(el).data('lon');
	  		var company = $(el).data('company');
	  		var imgSrc = '';
	  		if($(el).find('.picture img').length > 0){
	  			imgSrc = $(el).find('.picture img').attr('src');
	  		}
	  		if(lat !== ''){
	  			var sMarkerColor = 'red';
	  			var sIcon = 'ioc ioc-user-1';
	  			if(company ===  1){
	  				sMarkerColor = 'blue';
	  				sIcon = 'ioc ioc-money';
	  			}
	  			OC.ContactsPlus.aPinsMap[id] = {
		  			'id':id,
		  			'lat':lat,
		  			'lon':lon,
		  			'title':$(el).find('.fullname').text(),
		  			 icon: sIcon,
					 markerColor:sMarkerColor,
					image: imgSrc,
	  			};
	  		}
	  	});
	  
	 
	
	   OC.ContactsPlus.layerMarker = L.markerClusterGroup();
	    //showMapPin
	   
	   $('#mappinsInner').empty();
	
	   $.each(OC.ContactsPlus.aPinsMap,function(i,element){
	   	      var redMarker = L.AwesomeMarkers.icon({
				    icon: element.icon,
				    markerColor: element.markerColor
				  });
				  
				
				 var popupContent='<span class="pinmap-title">'+element.title+'</span>'; 
			      if(element.image!=''){
   	               popupContent+='<br /><span class="pinmap-image" style="display:inline-block;width:100%;"><img width="150" src="'+ element.image+'"  /></span>';
   	               }else{
   	               	    popupContent+='<br /><span class="pinmap-image" style="text-align:center;width:100%;display:block;"><i style="font-size:60px;" class="ioc ioc-user"></i></span>';
   	 
   	               }
   	            popupContent+='<br /><span class="pinmap-link"><a data-contactid="'+element.id+'" href="#'+element.id+'">Details</a></span>';
		   	   	var popup = L.popup({
					minWidth:150
				}).setContent(popupContent);  
		   	    OC.ContactsPlus.mapObjectMarker[i] = L.pinMarker([element.lat, element.lon],{'title':element.title,contactId:element.id,icon:redMarker}).bindPopup(popup);
	            OC.ContactsPlus.layerMarker.addLayer( OC.ContactsPlus.mapObjectMarker[i]);
      	   
	   });
	       
	     OC.ContactsPlus.mapObject.addLayer(OC.ContactsPlus.layerMarker);
        /*
          L.edgeMarker({
		      icon: L.icon({ // style markers
		          iconUrl: OC.imagePath('contactsplus','edge-arrow-marker-black.png'),
		          clickable: true,
		          iconSize: [48, 48],
		          iconAnchor: [24, 24]
		      }),
		      rotateIcons: true, // rotate EdgeMarkers depending on their relative position
		      layerGroup: null // you can specify a certain L.layerGroup to create the edge markers from.
    }).addTo(OC.ContactsPlus.mapObject);
         */
	},
	showMapPin:  function(lat,lon,zoom,oMarker) {
		   OC.ContactsPlus.mapObject.setView([lat, lon], zoom);
		  oMarker.openPopup();
		  //OC.ContactsPlus.showMapPin(element.lat,element.lon,16, OC.ContactsPlus.mapObjectMarker[element.id]);
		  //alert(oMarker.getPinId());
	},
};


var resizeTimeout = null;

$(window).resize(function(){
	if (resizeTimeout) clearTimeout(resizeTimeout);
	resizeTimeout = setTimeout(function(){
		
		OC.ContactsPlus.calcDimension();
		
	}, 500);
});

$(document).ready(function(){
   
  		OC.ContactsPlus.init();
		
		$(document).on('click', '#dropdown #dropClose', function(event) {
			event.preventDefault();
			event.stopPropagation();
			OC.Share.hideDropDown();
			return false;
		});
		
		$(document).on('click','#searchresults .info a, #map .pinmap-link a',function(event){
			event.preventDefault();
			event.stopPropagation();
		
			var tmp = $(this).attr('href').split('#');
			var id = parseInt(tmp[1]);
			
			OC.ContactsPlus.showContact(id,event);
			if($('#showMap').hasClass('isMap')){
				if(OC.ContactsPlus.mapObject.getZoom()<16){
					OC.ContactsPlus.mapObject.setView([OC.ContactsPlus.aPinsMap[id].lat,OC.ContactsPlus.aPinsMap[id].lon], 16);
				}
			}
			return false;
		});
		
		$(document).on('click', 'a.share', function(event) {
	//	if (!OC.Share.droppedDown) {
		event.preventDefault();
		event.stopPropagation();
		var itemType = $(this).data('item-type');
		var AddDescr =t(OC.ContactsPlus.appName,'Addressbook')+' ';
		var sService ='cpladdrbook';
		
		var itemSource = $(this).data('title');
			  itemSource = '<div>'+AddDescr+itemSource+'</div><div id="dropClose"><i class="ioc ioc-close" style="font-size:22px;"></i></div>';
			  
		if (!$(this).hasClass('shareIsOpen') && $('a.share.shareIsOpen').length === 0) {
			$('#infoShare').remove();
			$( '<div id="infoShare">'+itemSource+'</div>').prependTo('#dropdown');
				
		}else{
			$('a.share').removeClass('shareIsOpen');
			$(this).addClass('shareIsOpen');
			//OC.Share.hideDropDown();
		}
		//if (!OC.Share.droppedDown) {
			$('#dropdown').css('opacity',0);
			$('#dropdown').animate({
				'opacity': 1,
			},500);
		//}
    
		(function() {
			
			var targetShow = OC.Share.showDropDown;
			
			OC.Share.showDropDown = function() {
				var r = targetShow.apply(this, arguments);
				$('#infoShare').remove();
				$( '<div id="infoShare">'+itemSource+'</div>').prependTo('#dropdown');
				
				return r;
			};
			/*
			if($('#linkText').length > 0){
				$('#linkText').val($('#linkText').val().replace('public.php?service='+sService+'&t=','index.php/apps/contactsplus/s/'));
	
				var target = OC.Share.showLink;
				OC.Share.showLink = function() {
					var r = target.apply(this, arguments);
					
					$('#linkText').val($('#linkText').val().replace('public.php?service='+sService+'&t=','index.php/apps/contactsplus/s/'));
					
					return r;
				};
			}*/
		})();
		if (!$('#linkCheckbox').is(':checked')) {
				$('#linkText').hide();
		}
		return false;
		//}
	});
	
		
		$(document).on('click', '#importAddr, #importAddrStart', function(evt) {
			$('#drop-area').slideToggle(500);
		});
		
		$('#refreshGroups').on('click',function(){
			OC.ContactsPlus.getGroups();
		});
		
		$('#sortGroups').on('click',function(){
			 if(!$(this).hasClass('sortActive')){
			  $("#cgroups").sortable("enable");
			   $(this).addClass('sortActive');
			   $('#notification').text(t(OC.ContactsPlus.appName,'Sort modus groups activated'));
			   $('#notification').slideDown();
				window.setTimeout(function(){$('#notification').slideUp();}, 3000);
			 }else{
			 	 var idsInOrder = $("#cgroups").sortable('toArray', {attribute: 'data-grpid'});
			 	 OC.ContactsPlus.saveSortOrderGroups(idsInOrder);
			   $(this).removeClass('sortActive');
			    $("#cgroups").sortable("disable");
			     $('#notification').text(t(OC.ContactsPlus.appName,'Sort modus groups deactivated! Groups sortorder saved!'));
			   $('#notification').slideDown();
				window.setTimeout(function(){$('#notification').slideUp();}, 3000);
			 }
		});
		
		$("#leftsidebar").niceScroll();
		  	
     
     
     $('#addGroup').on('click', function () {
			//OC.ContactsPlus.oldGroup=groupsSel;
			OC.Tags.edit(OC.ContactsPlus.appName);
			 return false;
	 });
    $('#addContact').on('click', function () {
			var addrPerm=$('#cAddressbooks li.isActiveABook').attr('data-perm');
			if(addrPerm & OC.PERMISSION_CREATE){
				OC.ContactsPlus.newContact();
			 	return false;
			}else{
				OC.ContactsPlus.showMeldung(t(OC.ContactsPlus.appName,'Missing Permissions!'));
			}
	 });
	 
	 $('#showList').on('click', function () {
			OC.ContactsPlus.listView();
			 
	 });
	 
    $('#showCards').on('click', function () {
			OC.ContactsPlus.cardsView();
			
	 });
	 $('#showMap').on('click', function() {
		OC.ContactsPlus.showMap();
		return false;
	});
 
 	
	  $('#cgroups li').on('click',function(){
	  	  $('#cgroups li').removeClass('isActiveGroup');
	  	  $(this).addClass('isActiveGroup');
	  	  var addrBookId=$('#cAddressbooks li.isActiveABook').attr('data-adrbid');
	  	 
	  	  OC.ContactsPlus.loadContacts(addrBookId,$(this).attr('data-id'),0,0);
	  });
		  
	
	
	
	$('.letter').each(function(i,el){
		existLetter=$(el).attr('data-scroll');
		$('#rightcontentSideBar li[data-letter="'+existLetter+'"]').addClass('bLetterActive');
	});
	
     $(OC.Tags).on('change', function(event, data) {
		if(data.type === OC.ContactsPlus.appName) {
		   OC.ContactsPlus.categoriesChanged(data.tags);
		}
	});	
		
     $('input#contactphoto_fileupload').fileupload({
		dataType : 'json',
		url : OC.generateUrl('apps/'+OC.ContactsPlus.appName+'/uploadphoto'),
		done : function(e, data) {
			
			OC.ContactsPlus.imgSrc = data.result.imgdata;
			OC.ContactsPlus.imgMimeType = data.result.mimetype;
			$('#imgsrc').val(OC.ContactsPlus.imgSrc);
			$('#imgmimetype').val(OC.ContactsPlus.imgMimeType);
			$('#tmpkey').val(data.result.tmp);
			 OC.ContactsPlus.ContactPhoto.editPhoto($('#photoId').val(), data.result.tmp);
		}
	});
    
     /* Initialize the photo edit dialog */
		$('#edit_photo_dialog').dialog({
			autoOpen: false, modal: true, height:'auto', width: 'auto'
		});
		
		$('#edit_photo_dialog' ).dialog( 'option', 'buttons', [
			{
				text: "Ok",
				click: function() {
					OC.ContactsPlus.ContactPhoto.savePhoto(this);
					$('#coords input').val('');
					$('#cropbox').attr('src','');
					$(this).dialog('close');
				}
			},
			{
				text: "Cancel",
				click: function() { $(this).dialog('close'); }
			}
		] );
		//FIXME
		$(document).on('click', '#new-contact, #edit-contact', function(evt) {
			
			if(!$(evt.target).parent().hasClass('comboSelHolder')){
				$('.combobox ul').removeClass('isOpen').hide();
			}
			
			if(!$(evt.target).parent().hasClass('button-group')){
				$('#showAdditionalFieds').hide();
			}
			
		});
	//FIXME
	
			
});
$(window).bind('hashchange', function() {
	OC.ContactsPlus.checkShowEventHash();
});
