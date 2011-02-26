<?php
require_once("../inc/magmi_pluginhelper.php");
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
$plinst=Magmi_PluginHelper::getInstance($profile)->createInstance($pltype,$plclass);
echo $plinst->getOptionsPanel($file)->getHtml();