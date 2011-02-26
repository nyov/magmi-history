<?php
$profile=$_REQUEST["profile"];
$dslist=$_REQUEST["PLUGINS_DATASOURCES:class"];
$genlist=$_REQUEST["PLUGINS_GENERAL:classes"];
$iplist=$_REQUEST["PLUGINS_ITEMPROCESSORS:classes"];
if(!isset($iplist))
{
	$iplist="";
}
if(!isset($genlist))
{
	$genlist="";
}
$pflist=array();

foreach(explode(",",$dslist) as $pclass)
{
	$pflist[$pclass]="datasources";		
}

foreach(explode(",",$genlist) as $pclass)
{
	$pflist[$pclass]="general";		
}

foreach(explode(",",$iplist) as $pclass)
{
	$pflist[$pclass]="itemprocessors";		
}


require_once("../inc/magmi_pluginhelper.php");
require_once("../inc/magmi_config.php");
//saving plugin selection
$epc=new EnabledPlugins_Config($profile);
$epc->setPropsFromFlatArray(array("PLUGINS_DATASOURCES:class"=>$dslist,
								  "PLUGINS_GENERAL:classes"=>$genlist,
								  "PLUGINS_ITEMPROCESSORS:classes"=>$iplist));
if($epc->save())
{
	

//saving plugins params
foreach($pflist as $pclass=>$pfamily)
{
	if($pclass!="")
	{
		$plinst=Magmi_PluginHelper::getInstance($profile)->createInstance($pfamily,$pclass,$_REQUEST);		
		if(!$plinst->persistParams($plinst->getPluginParams($_REQUEST)))
		{
				$lasterr=error_get_last();
			echo "<div class='error'>".print_r($lasterr)."</div>";
		}
	}
}
$date=filemtime($epc->getConfFile());
echo "Profile $profile saved (".strftime("%c",$date).")";
}
else
{
	$lasterr=error_get_last();
	echo "<div class='error'>".$lasterr['message']."</div>";
}
