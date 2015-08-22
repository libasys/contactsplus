$(window).bind('hashchange', function(event) {

	var urlTest = window.location.hash.substr(1);
	var url = urlTest.split('-');

	if(url[0] === 'contactsplus'){
		
		$.getJSON(OC.generateUrl('apps/contactsplus/showcontact/{id}',{id:url[1]}), function(jsondata) {
			if($('#SearchView').length === 0){
				$('<div id="SearchView" style="display:block; position:fixed;background-color:red;width:400px;height:400px;top:0;left:0;z-index:3000;">').appendTo($('body')[0]);
			}
			
			$('#SearchView').html(jsondata);
			window.location.hash = '#';
		});
	}
});