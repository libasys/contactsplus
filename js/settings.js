OC.ContactsPlus = OC.ContactsPlus || {};
OC.ContactsPlus.appname='contactsplus';

OC.ContactsPlus.Settings = {
	init:function() {
		this.Addressbook.adrsettings = $('.addressbooks-settings').first();
		this.Addressbook.adractions = $('#contacts-settings').find('.actions');
		
		//console.log('actions: ' + this.Addressbook.adractions.length);
		//var ABooksList=$('.addressbook');
		$('#toggleIosSupport').on('change',function(){
         	   OC.ContactsPlus.addIosSupport(this);
         });
         
		OC.ContactsPlus.Settings.Addressbook.adrsettings.keydown(function(event) {
			if(event.which == 13 || event.which == 32) {
				OC.ContactsPlus.Settings.Addressbook.adrsettings.click();
			}
		});
		
		OC.ContactsPlus.Settings.Addressbook.adractions.find('button.hidden').hide();
		OC.ContactsPlus.Settings.Addressbook.adrsettings.on('click', function(event){
			$('.tipsy').remove();
			var tgt = $(event.target);
			
			if(tgt.is('i.ioc') || tgt.is(':checkbox')) {
				var id = tgt.parents('tr').first().data('id');
				if(!id) {
					return;
				}
				
				if(tgt.is('#active_aid_'+id+':checkbox')) {
					
					OC.ContactsPlus.Settings.Addressbook.doActivate(id, tgt);
					
				} else if(tgt.is('i.ioc')) {
					
					if(tgt.hasClass('edit')) {
						OC.ContactsPlus.Settings.Addressbook.doEdit(id);
					} else if(tgt.hasClass('delete')) {
						OC.ContactsPlus.Settings.Addressbook.doDelete(id);
					} else if(tgt.hasClass('globe')) {
						OC.ContactsPlus.Settings.Addressbook.showCardDAV(id);
					} else if(tgt.hasClass('cloud')) {
						OC.ContactsPlus.Settings.Addressbook.showVCF(id);
					}
					 else if(tgt.hasClass('abo')) {
						OC.ContactsPlus.Settings.Addressbook.showAboBirthday(id);
					}
					else if(tgt.hasClass('export')) {
						OC.ContactsPlus.Settings.Addressbook.VCFExport(id);
					}
				}
			} else if(tgt.is('button')) {
				event.preventDefault();
				if(tgt.hasClass('save')) {
					OC.ContactsPlus.Settings.Addressbook.doSave();
				} else if(tgt.hasClass('cancel')) {
					OC.ContactsPlus.Settings.Addressbook.showActions(['new']);
				} else if(tgt.hasClass('new')) {
					OC.ContactsPlus.Settings.Addressbook.doEdit('new');
				}
			}
		});
		
		
	},
	Addressbook:{
		showActions:function(act) {
			this.adractions.children().hide();
			this.adractions.children('.'+act.join(',.')).show();
		},
		doActivate:function(id, tgt) {
			var active = tgt.is(':checked');
			
			//console.log('doActivate: ', id, active);
			$.post(OC.generateUrl('apps/'+OC.ContactsPlus.appname+'/activateaddrbook'), {id: id, active: Number(active)}, function(jsondata) {
				if (jsondata.status == 'success') {
					$(document).trigger('request.addressbook.activate', {
						id: id,
						activate: active,
					});
				
				if(active === true){
					OC.ContactsPlus.getAddressBooks(id);
				}else{
					$('#cAddressbooks .dropcontainerAddressBook[data-adrbid="'+id+'"]').remove();	
				}	
				
				} else {
					//console.log('Error:', jsondata.data.message);
					OC.ContactsPlus.notify(t(OC.ContactsPlus.appname, 'Error') + ': ' + jsondata.data.message);
					tgt.checked = !active;
				}
			});
		},
		
		doDelete:function(id) {
			//console.log('doDelete: ', id);
			
			 
			var handleDelete=function(YesNo){
			 
			 	if(YesNo){
					var row = $('.addressbooks-settings tr[data-id="'+id+'"]');
					
					$.post(OC.generateUrl('apps/'+OC.ContactsPlus.appname+'/deleteaddrbook'), { id: id}, function(jsondata) {
						if (jsondata.status == 'success'){
							
							row.remove();
							OC.ContactsPlus.Settings.Addressbook.showActions(['new',]);
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
		doEdit:function(id) {
			//console.log('doEdit: ', id);
			
			var owner = this.adrsettings.find('[data-id="'+id+'"]').data('owner');
			var actions = ['description', 'save', 'cancel'];
			if(owner == OC.currentUser || id === 'new') {
				actions.push('active', 'name');
			}
			this.showActions(actions);
			var name = this.adrsettings.find('[data-id="'+id+'"]').find('.name').text();
			var description = this.adrsettings.find('[data-id="'+id+'"]').find('.description').text();
			var active = true;
			//console.log('name, desc', name, description);
			this.adractions.find('.active').prop('checked', active);
			this.adractions.find('.name').val(name);
			this.adractions.find('.description').val(description);
			this.adractions.data('id', id);
		},
		doSave:function() {
			var name = this.adractions.find('.name').val();
			var description = this.adractions.find('.description').val();
			var active = this.adractions.find('.active').is(':checked');
			var id = this.adractions.data('id');
			//console.log('doSave:', id, name, description, active);

			if(name.length == 0) {
				OC.dialogs.alert(t(OC.ContactsPlus.appname, 'Displayname cannot be empty.'), t(OC.ContactsPlus.appname, 'Error'));
				return false;
			}
			var url;
			if (id == 'new'){
				url = OC.generateUrl('apps/'+OC.ContactsPlus.appname+'/addaddrbook');
			}else{
				url = OC.generateUrl('apps/'+OC.ContactsPlus.appname+'/updateaddrbook');
			}
			self = this;
			$.post(url, { id: id, name: name, active: Number(active), description: description },
				function(jsondata){
					if(jsondata.status == 'success'){
						self.showActions(['new',]);
						self.adractions.removeData('id');
						active = Boolean(Number(jsondata.data.addressbook.active));
						if(id == 'new') {
							
							
							
							self.adrsettings.find('table')
								.append('<tr class="addressbook" data-id="'+jsondata.data.addressbook.id+'" data-uri="'+jsondata.data.addressbook.uri+'" data-owner="'+jsondata.data.addressbook.userid+'">'
									+ '<td class="activate"><input class="regular-checkbox isActiveAddressbook" data-id="'+jsondata.data.addressbook.id+'" style="float:left;" id="active_aid_'+jsondata.data.addressbook.id+'" type="checkbox" checked="checked" /><label style="float:left;margin-top:4px;margin-right:5px;" for="active_aid_'+jsondata.data.addressbook.id+'"></label></td>'
									+ '<td class="name" title="'+jsondata.data.addressbook.description+'">'+jsondata.data.addressbook.displayname+'</td>'
									+ '<td class="action"><a title="'+t(OC.ContactsPlus.appname,'Show CardDav link')+'"><i class="ioc ioc-publiclink globe"></i></a></td>'
									+ '<td class="action"><a title="'+t(OC.ContactsPlus.appname,'Export Addressbook')+'"><i class="ioc ioc-export export"></i></a></td>'
									+ '<td class="action"><a title="'+t(OC.ContactsPlus.appname,'Edit')+'"><i class="ioc ioc-edit edit"></i></td>'
									+ '<td class="action"><a title="'+t(OC.ContactsPlus.appname,'Delete')+'"><i class="ioc ioc-delete delete"></i></td>'
									+ '</tr>');
								
							OC.ContactsPlus.loadContacts(jsondata.data.addressbook.id,'',0,0);		
							OC.ContactsPlus.getAddressBooks(jsondata.data.addressbook.id);
							
								
						} else {
						var row = self.adrsettings.find('tr[data-id="'+id+'"]');
							row.find('td.name').text(jsondata.data.addressbook.displayname);
							row.find('td.description').text(jsondata.data.addressbook.description);
							$('#cAddressbooks').find('.dropcontainerAddressBook[data-adrbid="'+id+'"] .groupname').text(jsondata.data.addressbook.displayname);
						}
						
					} else {
						OC.dialogs.alert(jsondata.data.message, t(OC.ContactsPlus.appname, 'Error'));
					}
			});
		},
		showLink:function(id, row, link) {
			//console.log('row:', row.length);
			row.next('tr.link').remove();
			var linkrow = $('<tr class="link"><td colspan="4"><input style="width: 90%;" type="text" value="'+link+'" /></td>'
				+ '<td><i class="ioc ioc-delete"></i></td></tr>').insertAfter(row);
			linkrow.find('input').focus().select();
			linkrow.find('.ioc').click(function() {
				$(this).parents('tr').first().remove();
			});
		},
		showCardDAV:function(id) {
			//console.log('showCardDAV: ', id);
			var row = this.adrsettings.find('tr[data-id="'+id+'"]');
			var owner = row.data('owner');
			var uri = (owner === oc_current_user ) ? row.data('uri') : row.data('uri') + '_shared_by_' + owner;
			this.showLink(id, row, $('#totalurl').val()+'addressbooks/'+encodeURIComponent(oc_current_user)+'/'+encodeURIComponent(uri));
		},
		showVCF:function(id) {
			//console.log('showVCF: ', id);
			var row = this.adrsettings.find('tr[data-id="'+id+'"]');
			var owner = row.data('owner');
			var uri = (owner === oc_current_user ) ? row.data('uri') : row.data('uri') + '_shared_by_' + owner;
			var link = $('#totalurl').val()+'addressbooks/'+encodeURIComponent(oc_current_user)+'/'+encodeURIComponent(uri)+'?export';
			//console.log(link);
			this.showLink(id, row, link);
		},
		VCFExport:function(id) {
			//console.log('showVCF: ', id);
			var row = this.adrsettings.find('tr[data-id="'+id+'"]');
			var owner = row.data('owner');
			var uri = (owner === oc_current_user ) ? row.data('uri') : row.data('uri') + '_shared_by_' + owner;
			var link = OC.generateUrl('apps/'+OC.ContactsPlus.appname+'/exportcontacts') + '?bookid=' +id;
			document.location.href =link;
			
		},
		showAboBirthday:function(id) {
			//console.log('showVCF: ', id);
			var row = this.adrsettings.find('tr[data-id="'+id+'"]');
			var owner = row.data('owner');
			var uri = (owner === oc_current_user ) ? row.data('uri') : row.data('uri') + '_shared_by_' + owner;
			var link = OC.generateUrl('apps/'+OC.ContactsPlus.appname+'/exportbirthdays')+'?aid=' +id;
			document.location.href =link;
			
		}
	}
};



