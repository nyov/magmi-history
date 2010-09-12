	<?php require_once("../inc/magmi_pluginhelper.php");?>
	<?php require_once("../inc/magmi_config.php");?>
	
	<script type="text/javascript">
		var magmi_import=Class.create({
			bsfcallbacks:[],
			register_before_submit:function(cb){this.bsfcallbacks.push(cb)},
			submit:function(){
				var context={results:[]};
				this.bsfcallbacks.each(function(bsc,o){this.results.push(bsc())},context);
				for(i=0;i<context.results.length;i++)
				{
					if(context.result[i]==false)
					{
						return false;
					}
				}
				$('import_form').submit();}
		});
		var importer=new magmi_import();
	</script>
	<div class="container_12">
	<div class="import_params">

	<form id="import_form" method="post" action="magmi.php?run=2">
	<h2>import parameters</h2>
	Mode:<select name="mode" id="mode">
		<option value="update">Update only</option>
		<option value="create">Create</option>
	</select>
	<span id="rstspan" style="display:none">
	<input type="checkbox" id="reset" name="reset">Clear all products</span>
	<?php 
		$conf=Magmi_Config::getInstance();
		$conf->load();
		$dst=$conf->getEnabledPluginClasses("datasources");
		$ds=$dst[0];
		$dsinst=Magmi_PluginHelper::createInstance($ds);
		$dsinfo=$dsinst->getPluginInfo();
	?>
	<h2>Data Source - <?php echo $dsinfo["name"] . " -v".$dsinfo["version"]?></h2>
	<div id="dsp_option_panel">
		<?php 
		echo $dsinst->getOptionsPanel()->getHtml();?>
	</div>
	
	<div>
	<?php 
		if($conf->getEnabledPluginClasses("GENERAL"))
		{
		foreach($conf->getEnabledPluginClasses("GENERAL") as $plc){?>
		<div class="general_plugin_config">
			<?php $plinst=Magmi_PluginHelper::createInstance($plc); 
				  $plinfo=$plinst->getPluginInfo();
				  $panel=$plinst->getOptionsPanel();
				  ?>
				  
				  <h2><?php echo "{$plinfo["name"]} - v{$plinfo["version"]}"?></h2>
				  
				  <div class="gp_configpanel">
				  	<?php if($panel){
				  		echo $panel->getHtml();
				  	} ?>
				  </div>
		</div>
	<?php }}?>
	<?php 
		if($conf->getEnabledPluginClasses("ITEMPROCESSORS"))
		{
		foreach($conf->getEnabledPluginClasses("ITEMPROCESSORS") as $plc){?>
		<div class="itemprocessor_plugin_config">
			<?php $plinst=Magmi_PluginHelper::createInstance($plc); 
				  $plinfo=$plinst->getPluginInfo();
				  $panel=$plinst->getOptionsPanel();
				  ?>
				  
				  <h2><?php echo "{$plinfo["name"]} - v{$plinfo["version"]}"?></h2>
				  
				  <div class="ipp_configpanel">
				  	<?php if($panel){
				  		echo $panel->getHtml();
				  	} ?>
				  </div>
		</div>
	<?php }}?>
	</div>
	<div>
	<a href="javascript:importer.submit()">Launch Import</a>
	<a href='magmi.php'>Back to configuration</a>
	</div>
	</form>
	</div>
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
