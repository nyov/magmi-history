<?php
$profile=$_REQUEST["profile"];
$dslist=$_REQUEST["PLUGINS_DATASOURCES:class"];
$genlist=$_REQUEST["PLUGINS_GENERAL:classes"];
$iplist=$_REQUEST["PLUGINS_ITEMPROCESSORS:classes"];
$plist=array_merge(explode(",",$dslist),explode(",",$genlist),explode(",",$iplist));
require_once("../inc/magmi_pluginhelper.php");
require_once("../inc/magmi_config.php");
//saving plugin selection
$epc=new EnabledPlugins_Config($profile);
$epc->setPropsFromFlatArray(array("PLUGINS_DATASOURCES:class"=>$dslist,
								  "PLUGINS_GENERAL:classes"=>$genlist,
								  "PLUGINS_ITEMPROCESSORS:classes"=>$iplist));
$epc->save();
//saving plugins params
foreach($plist as $pclass)
{
	if($pclass!="")
	{
		$plinst=Magmi_PluginHelper::getInstance($profile)->createInstance($pclass);
		$plinst->pluginInit(null,$_REQUEST,false,$profile);
		$plinst->persistParams($plinst->getPluginParams($_REQUEST));
	}
}
session_start();
header("Location: magmi.php?configstep=2&profile=$profile");