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

OC.ContactsPlus ={
	firstLoading : true,
	oldGroup:false,
	appName:'contactsplus',
	photoData:false,
	popOverElem:null,
	groups:[],
	imgSrc :'',
	imgMimeType :'',
	init:function(){
		OC.ContactsPlus.getAddressBooks();
		OC.ContactsPlus.initSearch();
 		OC.ContactsPlus.Drop.init();
		OC.ContactsPlus.loadContacts(0,'all',1,0);
		OC.ContactsPlus.Settings.init();
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
						bgOpacity : .4,
						boxWidth : 400,
						boxHeight : 400,
						setSelect :[ 100, 130, 50, 50 ]//,
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

				droparea = document.getElementById('app-content');
				droparea.ondragover = function() {
					return false;
				};
				droparea.ondragend = function() {
					
					return false;
				};
				droparea.ondrop = function(e) {
					$('#loading').show();
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
 							$('#loading').hide();
						};
						reader.readAsDataURL(file);
					}
				}
			},
			
		},
	getAddressBooks:function(){
			
			
			
			$.getJSON(OC.generateUrl('apps/'+OC.ContactsPlus.appName+'/getaddressbooks'), function(jsondata) {
				if(jsondata){
					$('#cAddressbooks').html(jsondata);
					
					$('#cAddressbooks li span.groupname').on('click',function(){
						  	  $('#cAddressbooks li').removeClass('isActiveABook');
						  	  $(this).parent('li').addClass('isActiveABook');
						  	  OC.ContactsPlus.loadContacts($(this).parent('li').attr('data-adrbid'),'all',1,0);
					});
					
					 $('.toolTip').tipsy({
						html : true
					});
					
					$('#cAddressbooks input.isActiveAddressbook').on('change', function(event) {
						//event.stopPropagation();
						var tgt = $(event.target);
						
						if($(this).parent('li').data('perm') & OC.PERMISSION_UPDATE){
				       		OC.ContactsPlus.Settings.Addressbook.doActivate($(this).data('id'), tgt);
				      }else{
					      	$('#notification').html(t(OC.ContactsPlus.appName,'Permission denied'));
							$('#notification').slideDown();
							window.setTimeout(function(){$('#notification').slideUp();}, 3000);
				      }
					});
						  
					$(".dropcontainerAddressBook").droppable({
							activeClass: "activeHover",
							hoverClass: "dropHover",
							accept:'li.contactsrow .fullname',
							over: function( event, ui ) {
								
							},
							drop: function( event, ui ) {
								OC.ContactsPlus.showDialogAddressbook($(this).attr('data-adrbid'),ui.draggable.attr('data-id'));
								
							}
				   });
				   OC.Share.loadIcons('cpladdrbook');
				   
				   
				}
			});
	},
	buildCounterGroups:function(addrbkId){
		var AllCount=$(".contactsrow").length;
			$('#cAddressbooks li[data-adrbid="'+addrbkId+'"]').find('.groupcounter').text(AllCount);
			
			$('#cgroups li').removeClass('isActiveGroup');
            $('#cgroups li[data-grpid="all"]').addClass('isActiveGroup');
			
			$('#cgroups li').each(function(i,el){
				 //alert($(el).find('.groupcounter').text());
				 if($(el).attr('data-id')=='all'){
				 	$(el).find('.groupcounter').text(AllCount);
				 }else if($(el).attr('data-id')=='fav'){
				 	
				 }else{
					 var grpName=$(el).attr('data-id');
					 var counter=$('.contactsrow .colorgroup[data-category="'+grpName+'"]').length;
					 $(el).find('.groupcounter').text(counter);
				 }
			});
			var NonCount=$('.contactsrow .categories:empty').length;
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
						start: function(event, ui) {
							ui.helper.addClass('draggingContact');
						}
					});
						
					$(cardDiv+" .fullname a").on('click',function(){
						$CardId=$(this).closest('.container').attr('data-contactid');
						OC.ContactsPlus.showContact($CardId);
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
				 $('#rightcontent').html('');
			   $.post(OC.generateUrl('apps/'+OC.ContactsPlus.appName+'/getcontactcards'),{ardbid:addrbkId,grpid:grpId},function(jsondata){
			   	   $("#rightcontent").niceScroll();
			   	    $('#loading').hide();
			   	     $('#rightcontent').html(jsondata);
			   	    
			   	      if($('#showList i.ioc').hasClass('isActiveListView')){
			   	     	
			   	     	$('#showCards i').removeClass('isActiveListView');
						$('#showList i').addClass('isActiveListView');
			   	     	$('.contactsrow').addClass('listview');
			   	     	$('.letter').addClass('listview');
			   	     }
			   	     
			   	      $( "li.contactsrow .fullname" ).draggable({
						appendTo: "body",
						helper: OC.ContactsPlus.DragElement,
						cursor: "move",
						delay: 500,
						start: function(event, ui) {
							ui.helper.addClass('draggingContact');
						}
					});
						
					$(".contactsrow .fullname a").on('click',function(){
						$CardId=$(this).closest('.container').attr('data-contactid');
						OC.ContactsPlus.showContact($CardId);
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
						  		$(this).find('i.ioc-star').removeClass('yellow');
						  		 OC.Tags.removeFromFavorites($CardId,OC.ContactsPlus.appName).then(function(){
						  		 
						  		 	var iFavCounter=parseInt($('#cgroups li[data-id=fav]').find('.groupcounter').text());
									$('#cgroups li[data-id=fav]').find('.groupcounter').text((iFavCounter - 1));
									if($('#cgroups li[data-id="fav"]').hasClass('isActiveGroup')){
										
										$('.container[data-contactid='+$CardId+']').closest('li.contactsrow').remove();
										if($('.container[data-letter="'+$Letter+'"]').length === 0){
							         		$('.letter[data-scroll='+$Letter+']').addClass('hidden');
							         		$('#alphaScroll li[data-letter="'+$Letter+'"]').removeClass('bLetterActive');
							         	}
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
					 
					 $('#rightcontentSideBar li').removeClass('bLetterActive');
					 $('.letter').each(function(i,el){
						if(!$(el).hasClass('hidden')){
							existLetter=$(el).attr('data-scroll');
							$('#rightcontentSideBar li[data-letter='+existLetter+']').addClass('bLetterActive');
						}
					});
					if(bCount === 1){
						if(addrbkId === 0){
							addrbkId=$('.isActiveABook').attr('data-adrbid');
						}
						
					  OC.ContactsPlus.buildCounterGroups(addrbkId);
					}
					
					if(bCount === 2){
						var iCounterGroup=parseInt($('#cgroups li[data-id="'+grpId+'"]').find('.groupcounter').text());
						$('#cgroups li[data-id="'+grpId+'"]').find('.groupcounter').text((iCounterGroup+1));
						var iCounterAddrBook=parseInt($('#cAddressbooks li[data-adrbid="'+addrbkId+'"]').find('.groupcounter').text());
						$('#cAddressbooks li[data-adrbid="'+addrbkId+'"]').find('.groupcounter').text((iCounterAddrBook + 1));
					}
					
					if(id > 0){
					  $('#rightcontent').scrollTo('.container[data-contactid='+id+']',800);
					 }	
					 	
			   });
		},		
	newContact:function(){
			 $('#loading').show();
			 
			 if($('.webui-popover').length>0){
				if(OC.ContactsPlus.popOverElem !== null){
					OC.ContactsPlus.popOverElem.webuiPopover('destroy');
					OC.ContactsPlus.popOverElem = null;
					$('#show-contact').remove();
				}
			}
			
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
							 
							OC.ContactsPlus.generateSelectList('#sPhoneTypeSelect_0','#phonetype_0');
							OC.ContactsPlus.generateSelectList('#sPhoneTypeSelect_1','#phonetype_1');
							OC.ContactsPlus.generateSelectList('#sEmailTypeSelect_0','#emailtype_0');
							OC.ContactsPlus.generateSelectList('#sEmailTypeSelect_1','#emailtype_1');
							OC.ContactsPlus.generateSelectList('#sUrlTypeSelect','#urltype');
							OC.ContactsPlus.generateSelectList('#sAddrTypeSelect_0','#addrtype_0');
							
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
		
	},
	editContact:function(iCard){
			 $('#loading').show();
			 
			 if($('.webui-popover').length>0){
				if(OC.ContactsPlus.popOverElem !== null){
					OC.ContactsPlus.popOverElem.webuiPopover('destroy');
					OC.ContactsPlus.popOverElem = null;
					$('#show-contact').remove();
				}
			}
			
			$.ajax({
						type : 'POST',
						data:{'id':iCard},
						url : OC.generateUrl('apps/'+OC.ContactsPlus.appName+'/geteditformcontact'),
						success : function(data) {
							 $('#loading').hide();
							$("#contact_details").html(data);
								$("#contact_details").addClass('isOpenDialog');
								$('#contact_details').dialog({
								height:'auto',
								width:450,
								modal:true,
								'title': "Kontakt bearbeiten",
							});
							
							var winWidth=$(window).width();
							
							if(winWidth <= 480){
								 winHeight=$(window).height();
									$('#contact_details').dialog('option',{"width":(winWidth-40)});
									
							}
							
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
							 
							 $('.deleteEmail').on('click',function(){
							 	var iDelVal=$(this).attr('data-del');
							 	$('#email_container_'+iDelVal).remove();
							 });
							 $('.deleteTel').on('click',function(){
							 	var iDelVal=$(this).attr('data-del');
							 	$('#tel_container_'+iDelVal).remove();
							 });
							 $('.deleteAddr').on('click',function(){
							 	var iDelVal=$(this).attr('data-del');
							 	$('#addr_container_'+iDelVal).remove();
							 });
							 
							 $('.additionalFieldsRow').on('click',function(){
							 	   if( $(this).hasClass('activeAddField') ){
							 	      $(this).removeClass('activeAddField');
							 	      $('.additionalField[data-addfield="'+$(this).attr('data-id')+'"] ').hide();
							 	   }else{
							 	   	  $(this).addClass('activeAddField');
							 	      $('.additionalField[data-addfield="'+$(this).attr('data-id')+'"] ').show();
							 	   }
							 });
							 var iPCount=$('.isPhone').length;
							 for(var i=0;i<iPCount; i++){
							 	OC.ContactsPlus.generateSelectList('#sPhoneTypeSelect_'+i,'#phonetype_'+i);
							 }
							 var iECount=$('.isEmail').length;
							 for(var i=0;i<iECount; i++){
							 	OC.ContactsPlus.generateSelectList('#sEmailTypeSelect_'+i,'#emailtype_'+i);
							 }
							var iACount=$('.isAddr').length;
							 for(var i=0;i<iACount; i++){
							 	OC.ContactsPlus.generateSelectList('#sAddrTypeSelect_'+i,'#addrtype_'+i);
							 }
							OC.ContactsPlus.generateSelectList('#sUrlTypeSelect','#urltype');
							
							
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
						$("#contact_details").html('');
						$("#contact_details").removeClass('isOpenDialog');
					   $('#contact_details').dialog('close');
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
					  
					}
			});
		
	},
	deleteContact:function(iCardId){
			
		 $( "#dialogSmall" ).html( t(OC.ContactsPlus.appName, 'Please choose: contact delete or remove all groups from contact'));
	  	 
	  	  $( "#dialogSmall" ).dialog({
			resizable: false,
			title : t(OC.ContactsPlus.appName, 'Delete Contact or From Groups'),
			width:460,
			height:200,
			modal: true,
			buttons: [
						 { text:t(OC.ContactsPlus.appName, 'Delete Contact'),'class':'delButton', click: function() {
						 	
						 	 var oDialog=$(this);
						 	
								 $.post(OC.generateUrl('apps/'+OC.ContactsPlus.appName+'/deletecontact'),{'id':iCardId},function(jsondata){
										if(jsondata.status == 'success'){
											oDialog.dialog( "close" );
											
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
											
											if($('.container[data-letter="'+jsondata.data.letter+'"]').length === 0){
								         		$('.letter[data-scroll='+jsondata.data.letter+']').addClass('hidden');
								         		$('#alphaScroll li[data-letter="'+jsondata.data.letter+'"]').removeClass('bLetterActive');
								         	}
											 OC.ContactsPlus.showMeldung(t(OC.ContactsPlus.appName,'Contacts delete success!'));
										}
										else{
											alert(jsondata.data.message);
										}
							        });
						  	 }
						 },
						  { text:t(OC.ContactsPlus.appName, 'Delete From All Groups'), click: function() { 
						  	  var oDialog=$(this);
						  	 
								 $.post(OC.generateUrl('apps/'+OC.ContactsPlus.appName+'/deletecontactfromgroup'),{'id':iCardId},function(jsondata){
										if(jsondata.status == 'success'){
											oDialog.dialog( "close" );
											if(jsondata.data.scat!=''){
												temp=jsondata.data.scat.split(',');
											
												$.each(temp,function(i,el){
													var iCounter=parseInt($('#cgroups li[data-id="'+el+'"]').find('.groupcounter').text());
				                                    $('#cgroups li[data-id="'+el+'"]').find('.groupcounter').text((iCounter - 1));
												});
												var iCounter=parseInt($('#cgroups li[data-id="none"]').find('.groupcounter').text());
				                                 $('#cgroups li[data-id="none"]').find('.groupcounter').text((iCounter + 1));
											}
											if(!$('#cgroups li[data-id="all"]').hasClass('isActiveGroup')){
												$('.container[data-contactid='+iCardId+']').closest('li.contactsrow').remove();
											}else{
												$('.container[data-contactid='+iCardId+']').find('.categories').html('&nbsp;');
											}
											if($('.container[data-letter="'+jsondata.data.letter+'"]').length === 0){
								         		$('.letter[data-scroll='+jsondata.data.letter+']').addClass('hidden');
								         		$('#alphaScroll li[data-letter="'+jsondata.data.letter+'"]').removeClass('bLetterActive');
								         	}
										}
										else{
											alert(jsondata.data.message);
										}
							        });
						  	 }
						},
						{ text:t(OC.ContactsPlus.appName, 'Cancel'), click: function() { $( this ).dialog( "close" ); } } 
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
								OC.ContactsPlus.loadContacts(jsondata.data.addrBookId,activeGroup,2,jsondata.data.id);
							}else{
								if($('.letter[data-scroll='+jsondata.data.letter+']').hasClass('hidden')){
									$('.letter[data-scroll='+jsondata.data.letter+']').removeClass('hidden');
									$('#alphaScroll li[data-letter="'+jsondata.data.letter+'"]').addClass('bLetterActive');
								}
								var listView = '';
								if($('#showList i').hasClass('isActiveListView')){
									var listView = 'listview';
								}
								if($('li span.noitem').length === 1){
									$('li span.noitem').remove();
								}
			
								$('.letter[data-scroll='+jsondata.data.letter+']').after($('<li class="contactsrow '+listView+'">'+jsondata.data.card+'</li>'));
								OC.ContactsPlus.initActionhandlerSingleCard(jsondata.data.id);
								$('#rightcontent').scrollTo('.letter[data-scroll='+jsondata.data.letter+']',800);
								$('#alphaScroll li').removeClass('isScrollTo');
								$('#alphaScroll li[data-letter="'+jsondata.data.letter+'"]').addClass('isScrollTo');
								var counter=$('.contactsrow').length;
						 		$('#cgroups li[data-id="'+activeGroup+'"]').find('.groupcounter').text(counter);
								var iCount =parseInt($('#cAddressbooks li.isActiveABook').find('.groupcounter').text());
								$('#cAddressbooks li.isActiveABook').find('.groupcounter').text(iCount +1);
								$('#cgroups li[data-id="all"]').find('.groupcounter').text(iCount +1);
							}
							//isScrollTo
							//OC.ContactsPlus.loadContacts($('#cAddressbooks li.isActiveABook').attr('data-adrbid'),activeGroup,2,jsondata.data.id);
						}
						if (VALUE == 'editContact') {
					         OC.ContactsPlus.showMeldung(t(OC.ContactsPlus.appName,'Contacts update success!'));
					         if(jsondata.data.newAddrBookId ===''){
					         $('.container[data-contactid='+jsondata.data.id+']').addClass('toremove').hide();
					         
					         var oldLetter = $('.toremove').data('letter');
					         
					         if(oldLetter !== jsondata.data.letter){
					         	if($('.letter[data-scroll='+jsondata.data.letter+']').hasClass('hidden')){
					         		$('.letter[data-scroll='+jsondata.data.letter+']').removeClass('hidden');
					         		$('#alphaScroll li[data-letter="'+jsondata.data.letter+'"]').addClass('bLetterActive');
					         	}
					         	var listView = '';
								if($('#showList i').hasClass('isActiveListView')){
									var listView = 'listview';
								}
					         	 $('.letter[data-scroll='+jsondata.data.letter+']').after($('<li class="contactsrow '+listView+'">'+jsondata.data.card+'</li>'));
					         	 $('.toremove[data-contactid='+jsondata.data.id+']').closest('.contactsrow').remove();
					         	
					         	 if($('.container[data-letter="'+oldLetter+'"]').length === 0){
					         		$('.letter[data-scroll='+oldLetter+']').addClass('hidden');
					         		$('#alphaScroll li[data-letter="'+oldLetter+'"]').removeClass('bLetterActive');
					         	}
					         	
					         	$('#rightcontent').scrollTo('.letter[data-scroll='+jsondata.data.letter+']',800);
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
					       	 OC.ContactsPlus.loadContacts(jsondata.data.newAddrBookId,activeGroup,1,jsondata.data.id);
					       	}
				         }
				         
				        $("#contact_details").dialog('close');
						$("#contact_details").removeClass('isOpenDialog');
				         
						
					}
				});
		
			
		
		},
	showContact:function(iCard){
			
			// $('#loading').show();
			
			if($('.webui-popover').length>0){
				if(OC.ContactsPlus.popOverElem !== null){
					OC.ContactsPlus.popOverElem.webuiPopover('destroy');
					OC.ContactsPlus.popOverElem = null;
					$('#show-contact').remove();
				}
			}
			
			OC.ContactsPlus.popOverElem = $('.fullname[data-id="'+iCard+'"] a');
			
			OC.ContactsPlus.popOverElem.webuiPopover({
				url: OC.generateUrl('apps/'+OC.ContactsPlus.appName+'/showcontact'),
				async:{
					type:'POST',
					data:{'id':iCard},
					success:function(that,data){
					  
					   that.displayContent();
					  // that.getContentElement().css('height','auto');
					   
						$(".innerContactontent").niceScroll();
						if ($('#imgsrc').val() != '') {
							OC.ContactsPlus.imgSrc = $('#imgsrc').val();
							OC.ContactsPlus.imgMimeType = $('#imgmimetype').val();
						
							OC.ContactsPlus.ContactPhoto.loadPhoto();
						}
						
						 $('#selectedContactgroup').val($('#cgroups li.isActiveGroup').attr('data-id'));
						 
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
							OC.ContactsPlus.popOverElem.webuiPopover('destroy');
							OC.ContactsPlus.popOverElem = null;
							$('#show-contact').remove();
					  	});
						$('#showContact-export').on('click',function(){
							document.location.href = OC.generateUrl('apps/'+OC.ContactsPlus.appName+'/exportcontacts') + '?contactid=' + $('#photoId').val();
					  	});
						  	
						return false;
					}
				},
				multi:false,
				closeable:false,
				animation:'pop',
				placement:'auto',
				cache:false,
				width:440,
				type:'async',
				trigger:'manual',
			}).webuiPopover('show');
			
			
	},
	listView:function(){
		$('#showCards i').removeClass('isActiveListView');
		$('#showList i').addClass('isActiveListView');
		$('.contactsrow').addClass('listview');
		$('.letter').addClass('listview');
	},
	cardsView:function(){
		$('#showList i').removeClass('isActiveListView');
		$('#showCards i').addClass('isActiveListView');
		$('.contactsrow').removeClass('listview');
		$('.letter').removeClass('listview');
	},
	generateSelectList:function(iSelectId,iReturnId){
		 $(iSelectId+'.combobox ul').hide();
		 //INIT DEFAULT
		  $(iSelectId+'.combobox li').removeClass('isSelected');
		  $(iSelectId+'.combobox li').removeClass('isSelectedCheckbox');
		  var defaultVal=$(iReturnId).val();
		  $(iSelectId+'.combobox li[data-id="'+defaultVal+'"]').addClass('isSelected');
		   $(iSelectId+'.combobox li[data-id="'+defaultVal+'"]').addClass('isSelectedCheckbox');
		  $(iSelectId+'.combobox').find('.selector').html($(iSelectId+'.combobox li[data-id="'+defaultVal+'"]').text());
		 
		 $(iSelectId+'.combobox .comboSelHolder').on('click', function() {
			$(iSelectId+'.combobox ul').toggle();
		 });
		 $(iSelectId+'.combobox li').click(function() {
		 	 $(iSelectId+'.combobox li').removeClass('isSelected');
		 	 $(iSelectId+'.combobox li').removeClass('isSelectedCheckbox');
		 	 $(iReturnId).val($(this).data('id'));
		 	 $(this).addClass('isSelected');
		 	 $(this).addClass('isSelectedCheckbox');
			 $(this).parents(iSelectId+'.combobox').find('.selector').html($(this).text());
			 $(iSelectId+'.combobox ul').hide();
		 });
	},
	
	addCardToGroup:function(sCat,iCardId){
		 //addprobertytocontact
		 $.post(OC.generateUrl('apps/'+OC.ContactsPlus.appName+'/addprobertytocontact'),{'param':'CATEGORIES','value':sCat,'cardId':iCardId},function(jsondata){
			if(jsondata.status === 'success'){
				//jsondata.data.iCounter
				var iCounter=parseInt($('#cgroups li[data-id="'+sCat+'"]').find('.groupcounter').text());
				
				$('#cgroups li[data-id="'+sCat+'"]').find('.groupcounter').text((iCounter+parseInt(jsondata.data.iCounter)));
				if(parseInt(jsondata.data.iCounter)==1){
				   //New Cat TODO
				   newCat=$('<span class="colorgroup" style="background-color:#ccc;" title="'+sCat+'">').text(sCat.substring(0,1));
				   newCat.appendTo($('.container[data-contactid='+iCardId+']').find('.categories'));
				   var iNoneCounter=parseInt($('#cgroups li[data-id=none]').find('.groupcounter').text());
				   if(iNoneCounter>0)$('#cgroups li[data-id=none]').find('.groupcounter').text((iNoneCounter - parseInt(jsondata.data.iCounter)));
				}
				if($('#cgroups li[data-id="none"]').hasClass('isActiveGroup')){
						$('.container[data-contactid='+iCardId+']').closest('li.contactsrow').remove();
				}
				 OC.ContactsPlus.showMeldung(t(OC.ContactsPlus.appName,'Contact added to group ')+sCat);
			}
			else{
				alert(jsondata.data.message);
			}
        });
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
					
					var sName=elem['name'];
					if(elem['id']=='all' || elem['id']=='none' || elem['id']=='fav'){
						sName=elem['id'];
					}
					var sIcon='<div class="colCal" style="cursor:pointer;background-color:'+elem['bgcolor']+';color:'+elem['color']+';">'+elem['name'].substring(0,1)+'</div>';
					if(elem['id'] === 'fav'){
						sIcon='<i class="ioc ioc-star" style="float:left;margin-left:5px;margin-top:-3px; font-size:22px;color:#D8C101;"></i>';
					}
					if(elem['id']=='all'){
						sIcon='<i class="ioc ioc-users" style="float:left;margin-left:5px;margin-top:-4px;margin-right:5px;font-size:16px;"></i> ';
					}
					
					htmlCat+='<li class="dropcontainer" data-grpid="'+elem['id']+'" data-id="'+sName+'">'+sIcon+' <span class="groupname">&nbsp;'+elem['name']+'</span><span class="groupcounter">'+elem['icount']+'</span></li>';
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
						
							OC.ContactsPlus.addCardToGroup($(this).attr('data-id'),ui.draggable.attr('data-id'));
							
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
			return $(this).clone().text($(evt.target).text());
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
			 $( "#dialogSmall" ).dialog({
			resizable: false,
			title : t(OC.ContactsPlus.appName, 'Copy or Move Contact to Addressbook'),
			width:460,
			height:200,
			modal: true,
			buttons: [
						 { text:t(OC.ContactsPlus.appName, 'Copy Contact'), click: function() {
						 	
						 	 var oDialog=$(this);
								 $.post(OC.generateUrl('apps/'+OC.ContactsPlus.appName+'/copycontact'),{'addrid':addrId,'id':CardId},function(jsondata){
										if(jsondata.status == 'success'){
											oDialog.dialog( "close" );
											var iCounterAll=parseInt($('#cAddressbooks li[data-adrbid="'+addrId+'"]').find('.groupcounter').text());
											$('#cAddressbooks li[data-adrbid="'+addrId+'"]').find('.groupcounter').text((iCounterAll + 1));
										}
										if(jsondata.status == 'error'){
											oDialog.dialog( "close" );
											OC.ContactsPlus.showMeldung(jsondata.data.msg);
										}
							        });
						  	 }
						 },
						  { text:t(OC.ContactsPlus.appName, 'Move Contact'), click: function() { 
						  	  var oDialog=$(this);
						  	  
								 $.post(OC.generateUrl('apps/'+OC.ContactsPlus.appName+'/movecontact'),{'addrid':addrId,'id':CardId},function(jsondata){
										if(jsondata.status == 'success'){
											oDialog.dialog( "close" );
											 $('.container[data-contactid='+CardId+']').closest('li.contactsrow').remove();
											 var iCounterAll=parseInt($('#cAddressbooks li[data-adrbid="'+addrId+'"]').find('.groupcounter').text());
											$('#cAddressbooks li[data-adrbid="'+addrId+'"]').find('.groupcounter').text((iCounterAll + 1));
											var iCounterOld=parseInt($('#cAddressbooks li.isActiveABook').find('.groupcounter').text());
											$('#cAddressbooks li.isActiveABook').find('.groupcounter').text((iCounterOld-1));
											
										}
										if(jsondata.status == 'error'){
											oDialog.dialog( "close" );
											OC.ContactsPlus.showMeldung(jsondata.data.msg);
										}
							        });
						  	 }
						},
						{ text:t(OC.ContactsPlus.appName, 'Cancel'), click: function() { $( this ).dialog( "close" ); } } 
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
				OC.ContactsPlus.showContact(id);
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
		        var filterField='.'+$('#searchOpt option:selected').val();
		        if(filterValue){	
		          $('.letter').hide();	
		          $('.contactsrow').find(filterField+":not(:Contains(" + filterValue + "))").parent().parent().parent().hide();
		          $('.contactsrow').find(filterField+":Contains(" + filterValue + ")").parent().parent().parent().show();
		        } else {
		          $('.contactsrow').find(".rowHeader").parent().parent().show();
		           $('.letter').show();
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
	 
 
 	
	  $('#cgroups li').on('click',function(){
	  	  $('#cgroups li').removeClass('isActiveGroup');
	  	  $(this).addClass('isActiveGroup');
	  	  var addrBookId=$('#cAddressbooks li.isActiveABook').attr('data-adrbid');
	  	 
	  	  OC.ContactsPlus.loadContacts(addrBookId,$(this).attr('data-id'),0,0);
	  });
		  
		  
	
	

	 $('#rightcontentSideBar li').click(function(){  
	    if($(this).hasClass('bLetterActive')){
		    var liIndex = $(this).attr('data-letter');
		    $('#alphaScroll li').removeClass('isScrollTo');
			$(this).addClass('isScrollTo');
			$('#rightcontent').scrollTo('.letter[data-scroll='+liIndex+']',800);
	    }
	}); 
	
	$('.letter').each(function(i,el){
		existLetter=$(el).attr('data-scroll');
		$('#rightcontentSideBar li[data-letter='+existLetter+']').addClass('bLetterActive');
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
					$(this).dialog('close');
				}
			},
			{
				text: "Cancel",
				click: function() { $(this).dialog('close'); }
			}
		] );
		
		if (OC.ContactsPlus.firstLoading === true) {
			OC.ContactsPlus.checkShowEventHash();
			OC.ContactsPlus.calcDimension();
			 OC.ContactsPlus.getGroups();
			OC.ContactsPlus.firstLoading=false;
			
		}
		 $(document).on('click', function(event) {
			event.stopPropagation();
			/*
			
			if($("#contact_details").is(':visible') && !$("#contact_details").hasClass('forceOpen') && $('#contact_details').has(event.target).length === 0){
				$("#contact_details").hide();
				$("#contact_details").html('');
				$("#contact_details").removeClass('isOpenDialog');
				
			}*/
	});
		
			
});
$(window).bind('hashchange', function() {
	OC.ContactsPlus.checkShowEventHash();
});
