<?php
ini_set("magic_quotes_gpc",0);
$profile=$_REQUEST["profile"];
$dslist=$_REQUEST["PLUGINS_DATASOURCES:class"];
$genlist=$_REQUEST["PLUGINS_GENERAL:classes"];
$iplist=$_REQUEST["PLUGINS_ITEMPROCESSORS:classes"];
$eng=$_REQUEST["engine"];
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
$ph=Magmi_PluginHelper::getInstance($profile);
$ph->setEngineClass($eng);

$epc=new EnabledPlugins_Config($ph->getEngine()->getProfilesDir(), $profile);
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
		$plinst=$ph->createInstance($pfamily,$pclass,$_REQUEST);
		$paramlist=$plinst->getPluginParamNames();
		$sarr=$plinst->getPluginParams($_REQUEST);
		$parr=$plinst->getPluginParamsNoCurrent($_REQUEST);
		
		foreach($paramlist as $pname)
		{
			if(!isset($parr[$pname]))
			{
				$parr[$pname]=0;
			}
		}
		$farr=array_merge($sarr,$parr);
		if(!$plinst->persistParams($farr))
		{
			$lasterr=error_get_last();
			echo "<div class='error'>".$lasterr['message']."</div>";
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
