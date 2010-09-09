<?php
	$mmi=new MagentoMassImporter();
	$mmi->loadProperties("../magento_mass_importer.ini");
	$basedir=$mmi->magdir;
	$files=glob("$basedir/var/import/*.csv");
	$curfile=(isset($_POST["csvfile"])?$_POST["csvfile"]:null);
	?>
<div class="container_12">
	<div class="grid_12">
	<?php if(!isset($curfile) && MagentoMassImporter::getState()!=="running"){?>
	<?php MagentoMassImporter::setState("idle"); ?>
	<script type="text/javascript">
		getIndexes=function()
		{
			var outs=[];
			$$('._magindex').each(function(it){if(it.checked){outs.push(it.name)}});
			return outs.join(",");
		};

		doPost=function()
		{
			$('indexes').value=getIndexes();
			$('csvfile_form').submit();
		};

		fcheck=function(t)
		{
			$$('._magindex').each(function(it){it.checked=t});
			
		}
		
	</script>
	<div class="import_params">
	<h2>import parameters</h2>
	<form id="csvfile_form" method="post" action="">
	File to import:
	<select name="csvfile">
		<?php foreach($files as $fname){ ?>
		
			<option <?php if($fname==$curfile)?>selected<?php ?>><?php echo $fname?></option>
		<?php }?>
	</select>
	Mode:<select name="mode" id="mode">
		<option value="update">Update only</option>
		<option value="create">Create</option>
	</select>
	<span id="rstspan" style="display:none">
	<input type="checkbox" id="reset" name="reset">Clear all products</span>
	<input type="hidden" name="indexes" id="indexes"></input>
	<div>
	<ul>
	Indexing:<a href="#" onclick="fcheck(1);">All</a>&nbsp;<a href="#" onclick="fcheck(0)">None</a>
	<?php $idxarr=explode(",",MagentoMassImporter::$indexlist);
		foreach($idxarr as $indexname)
		{
	?>
		<li><input type="checkbox" name="<?php echo $indexname?>" class="_magindex"><?php echo $indexname?></input></li>
	<?php }?>
	</ul>
	</div>
	<div>
	<a href="javascript:doPost()">Launch Import</a>
	<a href='magmi.php'>Back to configuration</a>
	</div>
	</form>
	</div>
	<script type="text/javascript">
	checkmode=function()
	{
		if($F('mode')=='create')
		{
			$('rstspan').setStyle({display:'inline'});
		}
		else
		{
			$('rstspan').setStyle({display:'none'});
			$('reset').checked=false;
		}
	}
	$('mode').observe('change',checkmode);
	</script>
	<?php }
	else
	{
		?>
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

	<?php }
	?>
	</div>
	
</div>

