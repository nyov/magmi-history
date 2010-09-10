<?php
$t=0;
?>
<div class="container_12">

	<div class="grid_12 subtitle">Configuration</div>
</div>
<div class="clear"></div>

<?php 

$cnf="../conf/magento_mass_importer.ini";
if(!file_exists("$cnf"))
{
	$cnf="../conf/magento_mass_importer.ini.default";
}
$props=new Properties();
$props->load($cnf);
?>
<form method="post" action="magmi_saveconfig.php">
<div class="container_12">
	<div class="grid_4 col">
	<h3>Database</h3>
	<ul class="formline">
		<li class="label">Name:</li>
		<li class="value"><input type="text" name="DATABASE:dbname" value="<?php echo $props->get("DATABASE","dbname")?>" ></input></li>
	</ul>
	<ul class="formline">
		<li class="label">Host:</li>
		<li class="value"><input type="text" name="DATABASE:host" value="<?php echo $props->get("DATABASE","host")?>" ></input></li>
	</ul>
	<ul class="formline">
		<li class="label">Username:</li>
		<li class="value"><input type="text" name="DATABASE:user" value="<?php echo $props->get("DATABASE","user")?>" ></input></li>
	</ul>
	<ul class="formline">
		<li class="label">Password:</li>
		<li class="value"><input type="text" name="DATABASE:password" value="<?php echo $props->get("DATABASE","password")?>" ></input></li>
	</ul>
	<ul class="formline">
		<li class="label">Table prefix:</li>
		<li class="value"><input type="text" name="DATABASE:table_prefix" value="<?php echo $props->get("DATABASE","table_prefix")?>" ></input></li>
	</ul>
	</div>
	<div class="grid_4 col">
	<h3>Magento</h3>
	<ul class="formline">
		<li class="label">Base dir:</li>
		<li class="value"><input type="text" name="MAGENTO:basedir" value="<?php echo $props->get("MAGENTO","basedir")?>" ></input></li>
	</ul>
	<ul class="formline">
		<li class="label">Enabled Status:</li>
		<li class="value"><input type="text" name="MAGENTO:enabled_status_label" value="<?php echo $props->get("MAGENTO","enabled_status_label","Enabled")?>"></input></li>
	</ul>
	</div>
	<div class="grid_4 col omega">
	<h3>Global</h3>
	<ul class="formline">
		<li class="label">Reporting step:</li>
		<li class="value"><input type="text" name="GLOBAL:step" value="<?php echo $props->get("GLOBAL","step")?>"></input></li>
	</ul>
	<ul class="formline">
		<li class="label">CSV separator:</li>
		<li class="value"><input type="text" name="GLOBAL:csv_separator" value="<?php echo $props->get("GLOBAL","csv_separator")?>"></input></li>
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
