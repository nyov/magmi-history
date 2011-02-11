<?php 
require_once("magmi_config.php");
$conf=Magmi_Config::getInstance();
$conf->load();
$conf_ok=1;
?>

<!-- MAGMI UPLOADER -->
<div class="container_12">
<form method="post" enctype="multipart/form-data" action="magmi_upload.php">
	<div class="grid_12 col"><h3>Update Magmi Release</h3>
		<input type="file" name="magmi_package"></input>
		<input type="submit" value="Upload Magmi Release"></input>
		<?php if(isset($_SESSION["magmi_install_error"])){?>
		<div class="plupload_error">
				<?php echo $_SESSION["magmi_install_error"];?>
		</div>
		<?php }?>
	</div>
</form>
</div>

<!--  PLUGIN UPLOADER -->
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

<div class="container_12" >
<div class="grid_12 subtitle">Common Configuration </div>
</div>

<div class="clear"></div>
<form method="post" action="magmi_saveconfig.php">
<div class="container_12" id="common_config">
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
<div class="clear"></div>

	<div class="container_12">
		<div style="float:right">
		<input type="submit" value="Save Common Configuration"></input>
		</div>
	</div>
	</div>
	
</form>
<div class="clear"></div>
