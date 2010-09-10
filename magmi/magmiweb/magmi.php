<?php
	ini_set("display_errors",1);
	ini_set('include_path',ini_get('include_path').":..");
	require_once("magento_mass_importer.class.php");
	require_once("fshelper.php");
	
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<title>Magento Mass Importer by Dweeves - version <?php echo MagentoMassImporter::$version ?></title>
<link rel="stylesheet" href="css/960.css"></link>
<link rel="stylesheet" href="css/reset.css"></link>
<link rel="stylesheet" href="css/magmi.css"></link>
<script type="text/javascript" src="../../js/prototype/prototype.js"></script>
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Expires" CONTENT="-1">
</head>
<body>
<div class="container_12">
	<div class="grid_12 title  omega">
		<span>
  		Magento Mass Importer by Dweeves
 		</span>
 		<span class="version">
 		version <?php echo MagentoMassImporter::$version ?>
 		</span>
	</div>
	<div class="clear"></div>
</div>
<?php
	if(FSHelper::isDirWritable(".."))
	{
	
		if(isset($_REQUEST["run"]) && file_exists("../conf/magento_mass_importer.ini"))
		{
			unset($mmi);
			require_once("magmi_import.php");
		}
		else
		{
			require_once("magmi_config.php");
		}
	}
	else
	{
		?>
	<div class="container_12 config_error">
		Directory permissions not compatible with Mass Importer operations
		<br/>
		PHP/Web Server must have write permissions to magmi directory
	</div>
		<?php 
	}
?>
</body>
</html>
