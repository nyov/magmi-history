<?php
require_once("../inc/magmi_defs.php");
require_once("magmi_pluginhelper.php");
require_once("magmi_web_utils.php");
$pltype=$_REQUEST["plugintype"];
$plclass=$_REQUEST["pluginclass"];
$profile=$_REQUEST["profile"];
$file=null;
if(isset($_REQUEST['file']))
{
	$file=$_REQUEST['file'];
}
if($profile=="")
{
	$profile=null;
}

$ph=Magmi_PluginHelper::getInstance($profile);
if(isset($_REQUEST["engine"]))
{
	$ph->setEngineClass($_REQUEST["engine"]);
}
else
{
	$enginst=null;
}
$plinst->createInstance($pltype,$plclass,$_REQUEST,true);
echo $plinst->getOptionsPanel($file)->getHtml();