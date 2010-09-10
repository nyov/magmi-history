	<div class="clear"></div>
	<div id="import_log" class="col">
		<input type="hidden" name="reset" id="reset" value="<?php echo $_REQUEST["reset"]?>"></input>
		<input type="hidden" name="filename" id="filename" value="<?php echo $curfile?>"></input>
		<input type="hidden" name="mode" id="mode" value="<?php echo $_REQUEST["mode"]?>"></input>
		<input type="hidden" name="reindex" id="reindex" value="<?php echo $_REQUEST["indexes"]?>"></input>
		<div class="section_title">
			<span>Importing <?php echo $curfile?></span>
			<?php if(isset($curfile) && $curfile!=""){?>
			<span><input type="button" value="cancel" onclick="cancelImport()"></input></span>
			<?php }?>
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
	}

	startProgress=function()
	{
		window.upd=new Ajax.PeriodicalUpdater("runlog","./magmi_progress.php",{frequency:1,evalScripts:true});
		window.clearTimeout(window._dp);
	}
	
	startImport=function(filename)
	{
		if(window._sr==null)
		{
			var rq=new Ajax.Request("./magmi_run.php",
									{method:'get',
									 parameters:{'filename':filename,'reset':$F('reset'),'mode':$F('mode'),'reindex':$F('reindex')},
									onCreate:function(r){window._sr=r;}});
			
		}
		window._dp=window.setTimeout(startProgress,500);
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
	
	
	startImport('<?php echo $curfile?>');
	
</script>
