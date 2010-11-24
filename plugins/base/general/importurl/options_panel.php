<div class="plugin_description">
This plugin provides a textarea that gives the url mapping of the current settings that can be used by wget or curl
this area is refreshed each time you leave any field of this import config page.
</div>
<div id="GETURL:urlcontainer">
	<textarea id="GETURL:url" cols="132" rows="5"></textarea>
</div>
<script type="text/javascript">
	magmi_getimporturl=function()
	{
		old_action=$('import_form').action;
		$('import_form').action="./magmi_run.php";
		var url=$('import_form').action+'?'+$('import_form').serialize();
		$('GETURL:url').update(url);
		$('import_form').action=old_action;
	}
	Event.observe(window, 'load', function() {
		
		$('import_form').getElements().each(function(it){
				if(it.id!='GETURL:urlcontainer')
				{
					it.observe('blur',magmi_getimporturl);
				}});
		magmi_getimporturl();
	});
	
</script>