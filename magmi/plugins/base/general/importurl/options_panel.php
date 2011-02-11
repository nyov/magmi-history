<div class="plugin_description">
This plugin provides a textarea that gives the wget or curl command line (or just url if you want) that will run magmi with
current settings.
<p>this area is refreshed each time you leave any field of this import config page.</p>
</div>
<select id="GETURL:mode">
	<option value="wget">wget</option>
	<option value="curl">curl</option>
	<option value="rawurl">just url</option>
</select>
<div id="GETURL:urlcontainer">
	<textarea id="GETURL:url" cols="132" rows="5"></textarea>
</div>
<script type="text/javascript">
/*
magmi_getimporturl=function()
	{
		var mode=$F('GETURL:mode');
		old_action=$('import_form').action;
		$('import_form').action="./magmi_run.php";
		var url=$('import_form').action+'?'+$('import_form').serialize();
		var content="";
		switch(mode)
		{
			case "wget":
				content='wget "'+url+'" -O /dev/null';
				break;
			case "curl":
				content='curl -o /dev/null "'+url+'"';
				break;
			case "rawurl":
				content=url;
				break;
			default:
				content=url;
		}
		$('GETURL:url').update(content);
		$('import_form').action=old_action;
	}
	Event.observe(window, 'load', function() {
		
		$('import_form').getElements().each(function(it){
				if(it.id!='GETURL:urlcontainer')
				{
					it.observe('blur',magmi_getimporturl);
				}});
		$('GETURL:mode').observe('change',magmi_getimporturl);
		magmi_getimporturl();
		
	});
	*/
</script>