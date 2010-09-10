<?php function buildDSPanel()
{
	require_once("../magmi_pluginhelper.php");
	$plugins=Magmi_PluginHelper::scanPlugins();	
	$sout="<select id=\"datasource_class\" onchange=\"load_ds_panel()\">";
	$vout="<script type=\"text/javascript\">\n";
	$vout.="window.dsurls=[];\n";
	foreach($plugins["datasources"] as $dsc)
	{
		$dsi=Magmi_PluginHelper::createInstance($dsc["class"]);
		$info=$dsi->getPluginInfo();
		$cl=get_class($dsi);
		$sout.="<option value=\"$cl\">".$info["name"]." ".$info["version"]."</option>";
		$vout.="window.dsurls['$cl']='".$dsc["dir"]."/".$dsi->getOptionsPanel()."';\n";
	}
	$sout.="</select>";
	$vout.="</script>";
	return $sout."\n".$vout;
}
?>
<script type="text/javascript">
 load_ds_panel=function()
 {
	 var dsc=$F('datasource_class');
	 var test=new Ajax.Updater('ds_option_panel',window.dsurls[dsc]);
 }
</script>
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
	<form id="import_form" method="post" action="">
	<h3>Data Source:</h3>
	<div>
		<?php echo buildDSPanel();?>
	<div id="ds_option_panel">
	</div>

	</div>
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
	load_ds_panel();

	</script>
