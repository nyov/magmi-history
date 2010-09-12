	<?php require_once("../inc/magmi_pluginhelper.php");?>
	<?php require_once("../inc/magmi_config.php");?>
	<script type="text/javascript">
		var magmi_import={
			bsfcallbacks:[],
			register_before_submit:function(cb){this.bsfcallbacks.push(cb)}),
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
				$('import_form').submit();};
		}
	</script>
	<div class="container_12">
	<div class="import_params">
	<h2>import parameters</h2>
	Mode:<select name="mode" id="mode">
		<option value="update">Update only</option>
		<option value="create">Create</option>
	</select>
	<span id="rstspan" style="display:none">
	<input type="checkbox" id="reset" name="reset">Clear all products</span>

	<form id="import_form" method="post" action="">
	<?php 
		$conf=Magmi_Config::getInstance();
		$conf->load();
		$dst=$conf->getEnabledPluginClasses("datasources");
		$ds=$dst[0];
		$dsinst=Magmi_PluginHelper::createInstance($ds);
		$dsinfo=$dsinst->getPluginInfo();
	?>
	<h2>Data Source - <?php echo $dsinfo["name"] . " -v".$dsinfo["version"]?></h2>
	<div id="ds_option_panel">
		<?php 
		echo $dsinst->getOptionsPanel()->getHtml();?>
	</div>
	
	<div>
	<?php foreach($conf->getEnabledPluginClasses("GENERAL") as $plc){?>
		<div class="general_plugin_config">
			<?php $plinst=Magmi_PluginHelper::createInstance($plc); 
				  $plinfo=$plinst->getPluginInfo();
				  $panel=$plinst->getOptionsPanel();
				  ?>
				  
				  <h2><input type="checkbox" id="<?php echo $plc?>" onclick=""> <?php echo "{$plinfo["name"]} - v{$plinfo["version"]}"?></h2>
				  
				  <div class="plugin_configpanel" id="<?php echo "$plc_opanel"?>">
				  	<?php if($panel){
				  		echo $panel->getHtml();
				  	} ?>
				  </div>
		</div>
	<?php }?>
	<?php foreach($conf->getEnabledPluginClasses("ITEMPROCESSORS") as $plc){?>
		<div class="general_plugin_config">
			<?php $plinst=Magmi_PluginHelper::createInstance($plc); 
				  $plinfo=$plinst->getPluginInfo();
				  $panel=$plinst->getOptionsPanel();
				  ?>
				  
				  <h2><input type="checkbox" id="<?php echo $plc?>" onclick=""> <?php echo "{$plinfo["name"]} - v{$plinfo["version"]}"?></h2>
				  
				  <div class="plugin_configpanel" id="<?php echo "$plc_opanel"?>">
				  	<?php if($panel){
				  		echo $panel->getHtml();
				  	} ?>
				  </div>
		</div>
	<?php }?>
	</div>
	<div>
	<a href="javascript:magmi_import.submit()">Launch Import</a>
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
