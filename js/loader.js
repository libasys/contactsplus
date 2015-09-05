
OC.ContactsPlus = OC.ContactsPlus || {};
OC.ContactsPlus.appname='contactsplus';

OC.ContactsPlus.Import =  {
	Store:{
		file: '',
		path: '',
		id: 0,
		method: '',
		overwrite: 0,
		addressbookname: '',
		progresskey: '',
		percentage: 0,
		isDragged : false
	},
	Dialog:{
		open: function(filename){
			OC.addStyle(OC.ContactsPlus.appname, 'import');
			OC.ContactsPlus.Import.Store.file = filename;
			OC.ContactsPlus.Import.Store.path = $('#dir').val();
			
			$('body').append('<div id="contacts_import"></div>');
			$('#contacts_import').load(OC.generateUrl('apps/'+OC.ContactsPlus.appname+'/getimportdialogtplcontacts'), {filename:OC.ContactsPlus.Import.Store.file, path:OC.ContactsPlus.Import.Store.path},function(){
					OC.ContactsPlus.Import.Dialog.init();
			});
		},
		close: function(){
			OC.ContactsPlus.Import.reset();
			$('#contacts_import_dialog').dialog('destroy').remove();
			$('#contacts_import_dialog').remove();
			if($('#cAddressbooks').length > 0){
				OC.ContactsPlus.getAddressBooks();
			}
			
		},
		init: function(){
			//init dialog
			$('#contacts_import_dialog').dialog({
				width : 500,
				resizable: false,
				close : function() {
					OC.ContactsPlus.Import.Dialog.close();
				}
			});
			//init buttons
			$('#contacts_import_done').click(function(){
				OC.ContactsPlus.Import.Dialog.close();
			});
			$('#contacts_import_submit').click(function(){
				OC.ContactsPlus.Import.Core.process();
			});
			$('#contacts_import_mergewarning').click(function(){
				$('#contacts_import_newaddressbook').attr('value', $('#contacts_import_availablename').val());
				OC.ContactsPlus.Import.Dialog.mergewarning($('#contacts_import_newaddressbook').val());
			});
			$('#contacts_import_addressbook').change(function(){
				if($('#contacts_import_addressbook option:selected').val() == 'newaddressbook'){
					$('#contacts_import_newaddrform').slideDown('slow');
					OC.ContactsPlus.Import.Dialog.mergewarning($('#contacts_import_newaddressbook').val());
				}else{
					$('#contacts_import_newaddrform').slideUp('slow');
					$('#contacts_import_mergewarning').slideUp('slow');
				}
			});
			$('#contacts_import_newaddressbook').keyup(function(){
				OC.ContactsPlus.Import.Dialog.mergewarning($.trim($('#contacts_import_newaddressbook').val()));
				return false;
			});
			if(OC.ContactsPlus.Import.Store.isDragged === true){
				var aktAddrBookId=$('#cAddressbooks li.isActiveABook').attr('data-adrbid');
				$('#contacts_import_addressbook').val(aktAddrBookId);
			}
			
			//init progressbar
			$('#contacts_import_progressbar').progressbar({value: OC.ContactsPlus.Import.Store.percentage});
			OC.ContactsPlus.Import.Store.progresskey = $('#contacts_import_progresskey').val();
		},
		mergewarning: function(newaddrname){
			$.post(OC.generateUrl('apps/'+OC.ContactsPlus.appname+'/checkaddressbookexists'), {addrbookname: newaddrname}, function(data){
				if(data !== null && data.message == 'exists'){
					$('#contacts_import_mergewarning').slideDown('slow');
				}else{
					$('#contacts_import_mergewarning').slideUp('slow');
				}
			});
			return false;
		},
		update: function(){
			if(OC.ContactsPlus.Import.Store.percentage === 100){
				return false;
			}
			$.post(OC.generateUrl('apps/'+OC.ContactsPlus.appname+'/importvcards'), {progresskey: OC.ContactsPlus.Import.Store.progresskey, getprogress: true}, function(data){
 				if(data.status == 'success'){
 					
 					if(data.percent === null){
	 					return false;
 					}
 					
 					OC.ContactsPlus.Import.Store.percentage = parseInt(data.percent);
					$('#contacts_import_progressbar').progressbar('option', 'value', parseInt(data.percent));
					$('#contacts_import_progressbar > div').css('background-color', '#FF2626');
					$('#contacts_import_process_message').text(data.currentmsg);
					if(data.percent < 100 ){
						window.setTimeout('OC.ContactsPlus.Import.Dialog.update()', 100);
					}else{
						$('#contacts_import_progressbar').progressbar('option', 'value', 100);
						$('#contacts_import_progressbar > div').css('background-color', '#FF2626');
						$('#contacts_import_done').css('display', 'block');
						
					}
				}else{
				
					$('#contacts_import_progressbar').progressbar('option', 'value', 100);
					$('#contacts_import_progressbar > div').css('background-color', '#FF2626');
					$('#contacts_import_status').html(data.message);
				}
			});
			return 0;
		},
		warning: function(selector){
			$(selector).addClass('contacts_import_warning');
			$(selector).focus(function(){
				$(selector).removeClass('contacts_import_warning');
			});
		}
	},
	Core:{
		process: function(){
			var validation = OC.ContactsPlus.Import.Core.prepare();
			if(validation){
				$('#contacts_import_form').css('display', 'none');
				$('#contacts_import_process').css('display', 'block');
				$('#contacts_import_newaddressbook').attr('readonly', 'readonly');
				$('#contacts_import_addressbook').attr('disabled', 'disabled');
				$('#contacts_import_overwrite').attr('disabled', 'disabled');
				OC.ContactsPlus.Import.Core.send();
				window.setTimeout('OC.ContactsPlus.Import.Dialog.update()', 100);
			}
		},
		send: function(){
			
			$.post(OC.generateUrl('apps/'+OC.ContactsPlus.appname+'/importvcards'),
			{progresskey: OC.ContactsPlus.Import.Store.progresskey, method: String (OC.ContactsPlus.Import.Store.method), overwrite: String (OC.ContactsPlus.Import.Store.overwrite), addressbookname: String (OC.ContactsPlus.Import.Store.addressbookname), path: String (OC.ContactsPlus.Import.Store.path), file: String (OC.ContactsPlus.Import.Store.file), id: String (OC.ContactsPlus.Import.Store.id), isDragged:String (OC.ContactsPlus.Import.Store.isDragged)}, function(data){
				if(data.status == 'success'){
					$('#contacts_import_progressbar').progressbar('option', 'value', 100);
					$('#contacts_import_progressbar > div').css('background-color', '#FF2626');
					OC.ContactsPlus.Import.Store.percentage = 100;
					$('#contacts_import_progressbar').hide();
					$('#contacts_import_process_message').text('').hide();
					$('#contacts_import_done').css('display', 'block');
					$('#contacts_import_status').html(data.message);
				}else{
					$('#contacts_import_progressbar').progressbar('option', 'value', 100);
					$('#contacts_import_progressbar > div').css('background-color', '#FF2626');
					$('#contacts_import_status').html(data.message);
						
				}
			});
		},
		prepare: function(){
			OC.ContactsPlus.Import.Store.id = $('#contacts_import_addressbook option:selected').val();
			
			if($('#contacts_import_addressbook option:selected').val() == 'newaddressbook'){
				OC.ContactsPlus.Import.Store.method = 'new';
				OC.ContactsPlus.Import.Store.addressbookname = $.trim($('#contacts_import_newaddressbook').val());
				if(OC.ContactsPlus.Import.Store.addressbookname == ''){
					OC.ContactsPlus.Import.Dialog.warning('#contacts_import_newaddressbook');
					return false;
				}
				
			}else{
				OC.ContactsPlus.Import.Store.method = 'old';
				OC.ContactsPlus.Import.Store.overwrite = $('#contacts_import_overwrite').is(':checked') ? 1 : 0;
			}
			return true;
		}
	},
	reset: function(){
		OC.ContactsPlus.Import.Store.file = '';
		OC.ContactsPlus.Import.Store.path = '';
		OC.ContactsPlus.Import.Store.id = 0;
		OC.ContactsPlus.Import.Store.method = '';
		OC.ContactsPlus.Import.Store.overwrite = 0;
		OC.ContactsPlus.Import.Store.calname = '';
		OC.ContactsPlus.Import.Store.progresskey = '';
		OC.ContactsPlus.Import.Store.percentage = 0;
	}
};



$(document).ready(function(){
	if(typeof FileActions !== 'undefined'){
		FileActions.register('text/vcard','importaddressbook', OC.PERMISSION_READ, '', OC.ContactsPlus.Import.Dialog.open);
		FileActions.setDefault('text/vcard','importaddressbook');
		FileActions.register('text/x-vcard','importaddressbook', OC.PERMISSION_READ, '', OC.ContactsPlus.Import.Dialog.open);
		FileActions.setDefault('text/x-vcard','importaddressbook');
	}
	
});