<?php
$t=0;
?>
<div class="container_12">

	<div class="grid_12 subtitle">Configuration</div>
</div>
<div class="clear"></div>

<?php 
require_once("magmi_config.php");
$conf=Magmi_Config::getInstance();
if($conf->isDefault())
{
	$conf->loadDefault();	
}
else
{
	$conf->load();
}
?>
<form method="post" action="magmi_saveconfig.php">
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
		<li class="value"><input type="text" name="DATABASE:password" value="<?php echo $conf->get("DATABASE","password")?>" ></input></li>
	</ul>
	<ul class="formline">
		<li class="label">Table prefix:</li>
		<li class="value"><input type="text" name="DATABASE:table_prefix" value="<?php echo $conf->get("DATABASE","table_prefix")?>" ></input></li>
	</ul>
	</div>
	<div class="grid_4 col">
	<h3>Magento</h3>
	<ul class="formline">
		<li class="label">Base dir:</li>
		<li class="value"><input type="text" name="MAGENTO:basedir" value="<?php echo $conf->get("MAGENTO","basedir")?>" ></input></li>
	</ul>
	<ul class="formline">
		<li class="label">Enabled Status:</li>
		<li class="value"><input type="text" name="MAGENTO:enabled_status_label" value="<?php echo $conf->get("MAGENTO","enabled_status_label","Enabled")?>"></input></li>
	</ul>
	</div>
	<div class="grid_4 col omega">
	<h3>Global</h3>
	<ul class="formline">
		<li class="label">Reporting step:</li>
		<li class="value"><input type="text" name="GLOBAL:step" value="<?php echo $conf->get("GLOBAL","step")?>"></input></li>
	</ul>
	</div>
	<div class="clear"></div>
	
	</div>
<div class="container_12">

	<div class="grid_4 omega push_11">
		<input type="submit"></input>
	</div>
</div>
</form>
