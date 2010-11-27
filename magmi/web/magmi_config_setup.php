<div class="container_12">
	<div class="grid_12 subtitle">Configuration</div>
</div>
<div class="clear"></div>
<?php 
require_once("magmi_config.php");
$conf=Magmi_Config::getInstance();
$conf->load();
$conf_ok=1;
?>
<script type="text/javascript">
	addclass=function(it,o)
	{
		if(it.checked){
			this.arr.push(it.name);
		}
	};
	
	gatherclasses=function(tlist)
	{
		tlist.each(function(t,o){
			var context={arr:[]};
			$$(".pl_"+t).each(addclass,context);
			var target=$("plc_"+t);
			target.value=context.arr.join(",");
		});
	};
</script>
<form method="post" action="magmi_saveconfig.php" onsubmit="return gatherclasses(['GENERAL','ITEMPROCESSORS'])">
<div class="container_12">
	<div class="grid_4 col">
	<h3>Database</h3>
	<ul class="formline">
		<li class="label">Name:</li>
		<li class="value"><input type="text" name="DATABASE:dbname" value="<?php echo $conf->get("DATABASE","dbname")?>" ></input></li>
	</ul>
	<ul class="formline">
		<li class="label">Host:</li>
		<li class="value"><input type="text" name="DATABASE:host" value="<?php echo $conf->get("DATABASE","host")?>" ></input></li>
	</ul>
	<ul class="formline">
		<li class="label">Username:</li>
		<li class="value"><input type="text" name="DATABASE:user" value="<?php echo $conf->get("DATABASE","user")?>" ></input></li>
	</ul>
	<ul class="formline">
		<li class="label">Password:</li>
		<li class="value"><input type="password" name="DATABASE:password" value="<?php echo $conf->get("DATABASE","password")?>" ></input></li>
	</ul>
	<ul class="formline">
		<li class="label">Table prefix:</li>
		<li class="value"><input type="text" name="DATABASE:table_prefix" value="<?php echo $conf->get("DATABASE","table_prefix")?>" ></input></li>
	</ul>
	</div>
	<div class="grid_4 col">
	<h3>Magento</h3>
	<ul class="formline">
		<li class="label">Version:</li>
		<li class="value"><select name="MAGENTO:version">
			<?php foreach(array("1.4.x","1.3.x") as $ver){?>
				<option value="<?php echo $ver?>" <?php if($conf->get("MAGENTO","version")==$ver){?>selected=selected<?php }?>><?php echo $ver?></option>
			<?php }?>
		</select></li>
	</ul>
	<ul class="formline">
		<li class="label">Base dir:</li>
		<li class="value"><input type="text" name="MAGENTO:basedir" value="<?php echo $conf->get("MAGENTO","basedir")?>" ></input></li>
	</ul>
	
	</div>
	<div class="grid_4 col omega">
	<h3>Global</h3>
	<ul class="formline">
		<li class="label">Reporting step:</li>
		<li class="value"><input type="text" name="GLOBAL:step" value="<?php echo $conf->get("GLOBAL","step")?>"></input></li>
	</ul>
	
	</div>
	
	<div class="grid_12 subtitle">Enabled Plugins</div>
<?php
require_once("magmi_pluginhelper.php");
$plugins=Magmi_PluginHelper::getInstance()->getPluginClasses();
$order=array("datasources","general","itemprocessors");
?>

	<?php foreach($order as $k)
	{?>
	<div class="grid_4 col <?php if($k==$order[count($order)-1]){?>omega<?php }?>">
		<h3><?php echo ucfirst($k)?></h3>
		<?php if($k=="datasources")
		{?>
			<?php $pinf=$plugins[$k];?>
			<?php if(count($pinf)>0){?>
			<select name="PLUGINS_DATASOURCES:class">
			
			<?php foreach($pinf as $pclass)
			{
			$pinst=Magmi_PluginHelper::getInstance()->createInstance($pclass);
			$pinfo=$pinst->getPluginInfo();
			?>
			<option value="<?php echo $pclass?>"<?php  if($conf->isPluginEnabled($k,$pclass)){?>selected="selected"<?php }?>><?php echo $pinfo["name"]." v".$pinfo["version"];?></option>
			<?php }?>
					
			</select>
			<?php }else{
						$conf_ok=0;
				
				?>
			Magmi needs a datasource plugin, please install one
			<?php }?>
			<?php 
		}
		else
		{?>
		<ul>
		<?php $pinf=$plugins[$k];?>
		<?php foreach($pinf as $pclass)	{
			$pinst=Magmi_PluginHelper::getInstance()->createInstance($pclass);
			$pinfo=$pinst->getPluginInfo();
		?>
		<li><input type="checkbox" class="pl_<?php echo strtoupper($k)?>" name="<?php echo $pclass?>" <?php if($conf->isPluginEnabled($k,$pclass)){?>checked="checked"<?php }?>><?php echo $pinfo["name"]." v".$pinfo["version"];?></input></li>
		<?php }?>	
		</ul>
		<input type="hidden" id="plc_<?php echo strtoupper($k)?>" value="<?php echo implode(",",$conf->getEnabledPluginClasses($k))?>" name="PLUGINS_<?php echo strtoupper($k)?>:classes"></input>
		<?php 
		}?>
	</div>
	<?php }?>
	<div style="float:right">
		<input type="submit" value="Apply Configuration" <?php if(!$conf_ok){?>disabled="disabled"<?php }?>></input>
	</div>
</div>
</form>
<!-- UPLOADER -->
<div class="container_12">
<form method="post" enctype="multipart/form-data" action="plugin_upload.php">
	<div class="grid_12 col"><h3>Upload New Plugins</h3>
		<input type="file" name="plugin_package"></input>
		<input type="submit" value="Upload Plugins"></input>
<?php if(isset($_SESSION["plugin_install_error"])){?>
<div class="plupload_error">
<?php echo $_SESSION["plugin_install_error"];?>
</div>
<?php }?>
</div>
</form>
</div>