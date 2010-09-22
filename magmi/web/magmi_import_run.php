	<div class="clear"></div>
	<form name="import_params" id="import_params" method="post" action="./magmi_run.php">
	<?php
		foreach($_POST as $k=>$v)
		{
			?>
			<input type="hidden" value="<?php echo htmlspecialchars($v)?>" name="<?php echo $k?>"></input>
			<?php
		}
	?>
	</form>
	<div id="import_log" class="col">
	
		<div class="section_title">
			<span>Importing...</span>
			<span><input type="button" value="cancel" onclick="cancelImport()"></input></span>
			<div id="progress_container">
				&nbsp;
				<div id="import_progress"></div>
				<div id="import_current">&nbsp;</div>
			</div>
		</div>
		<div id="runlog">
		</div>
	</div>
<script type="text/javascript">
	endImport=function(t)
	{
		window.upd.stop();
		window.upd=null;
	}

	startProgress=function()
	{
		window.upd=new Ajax.PeriodicalUpdater("runlog","./magmi_progress.php",{frequency:1,evalScripts:true});
	}
	
	startImport=function(filename)
	{
		if(window._sr==null)
		{
			var rq=$('import_params').request({method:'get',
									onCreate:function(r){window._sr=r;startProgress();}});
			
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
