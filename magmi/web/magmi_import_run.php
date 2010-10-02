	<?php 
	ini_set('magic_gpc_quotes',0);
	$_SESSION["magmi_import_params"]=$_POST;
	session_write_close();
	?>
	<div class="clear"></div>
	<div id="import_log" class="container_12">
		<div class="section_title grid_12">
			<span>Importing...</span>
			<span><input id="cancel_button" type="button" value="cancel" onclick="cancelImport()"></input></span>
			<div id="progress_container">
				&nbsp;
				<div id="import_progress"></div>
				<div id="import_current">&nbsp;</div>
			</div>
		</div>
		<div id="runlog" class="grid_12">
		</div>
	</div>
<script type="text/javascript">
	endImport=function(t)
	{
		$('cancel_button').hide();
		window.upd.stop();
		window.upd=null;
		if(window._sr!=null)
		{
			window._sr.transport.abort();
			window._sr=null;
		}
	}

	startProgress=function()
	{
		window.upd=new Ajax.PeriodicalUpdater("runlog","./magmi_progress.php",{frequency:1,evalScripts:true});
	}
	
	startImport=function(filename)
	{
		if(window._sr==null)
		{
			var rq=new Ajax.Request('./magmi_run.php',{method:'get',
									 parameters:{'PHPSESSID':'<?php echo session_id();?>'},
									onCreate:function(r){window._sr=r}});
			startProgress.delay(0.5);
		}
	}
	
	setProgress=function(pc)
	{
		$('import_current').setStyle({width:''+pc+'%'});
		$('import_progress').update(''+pc+'%');
	}

	cancelImport=function()
	{
		var rq=new Ajax.Request("./magmi_cancel.php",{method:'get'});
		window._sr.transport.abort();
		window._sr=null;
	}
	
	
	startImport();
	
</script>
