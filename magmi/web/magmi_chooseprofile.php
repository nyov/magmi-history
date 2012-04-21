<?php
require_once("../inc/magmi_config.php");
require_once("../inc/magmi_pluginhelper.php");
$currentprofile=$_REQUEST["profile"];
if($currentprofile=="default")
{
	$currentprofile=null;
}
$eng=$_REQUEST["engineclass"];
$newprofile=$_REQUEST["newprofile"];

if($newprofile!="")
{
	$ph=Magmi_PluginHelper::getInstance($currentprofile);
	$ph->setEngineClass($eng);

	$bcfg=new EnabledPlugins_Config($ph->getEngine()->getProfilesDir(),$currentprofile);
	$confdir=Magmi_Config::getInstance()->getConfDir();
	$npdir=$confdir.DS.$newprofile;
	mkdir($npdir,Magmi_Config::getInstance()->getDirMask());
	$cpdir=$bcfg->getProfileDir();
	$filelist=scandir($cpdir);
	foreach($filelist as $fname)
	{
		if(substr($fname,-5)==".conf")
		{
			copy($cpdir.DS.$fname,$npdir.DS.$fname);
		}
	}
}
else
{
	$newprofile=$currentprofile;
}
header("Location:magmi.php?configstep=2&profile=$newprofile&engineclass=$eng");

