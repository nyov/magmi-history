<?php
	set_include_path(get_include_path().PATH_SEPARATOR."../inc");
	ini_set("display_errors",1);
	ini_set("error_reporting",E_ALL);
	require_once("magmi_importer.php");
	require_once("magmi_config.php");
	require_once("fshelper.php");
	session_start();
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<title>Magento Mass Importer by Dweeves - version <?php echo MagentoMassImporter::$version ?></title>
<link rel="stylesheet" href="css/960.css"></link>
<link rel="stylesheet" href="css/reset.css"></link>
<link rel="stylesheet" href="css/magmi.css"></link>
<script type="text/javascript" src="js/prototype/prototype.js"></script>
<script type="text/javascript" src="js/ScrollBox.js"></script>

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
	$badrights=array();
	foreach(array("../state","../conf","../plugins") as $dirname)
	{
		if(!FSHelper::isDirWritable($dirname))
		{
			$badrights[]=$dirname;
		}
	}
	if(count($badrights)==0)
	{
	
		if(isset($_REQUEST["run"]) && file_exists("../conf/magmi.ini"))
		{
			if($_REQUEST["run"]==2 ||Magmi_StateManager::getState()=="running" )
			{
				require_once("magmi_import_run.php");
			}
			else
			{
				Magmi_StateManager::setState("idle",true);
				require_once("magmi_import_config.php");
			}
		}
		else
		{
			require_once("magmi_config_setup.php");
		}
	}
	else
	{
		?>
	
	<div class="container_12" style="margin-top:5px">
		<div class="magmi_error">
		Directory permissions not compatible with Mass Importer operations
		<ul>
		<?php foreach($badrights as $dirname){
			$trname=str_replace("..","magmi",$dirname);
			?>
			<li><?php echo $trname?> not writable!</li>
		<?php }?>
		</ul>
		</div>
	</div>
		<?php 
	}
?>
</body>
</html>
