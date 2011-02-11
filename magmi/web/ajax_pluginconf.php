<?php
require_once("../inc/magmi_pluginhelper.php");
require_once("magmi_web_utils.php");
$plclass=$_REQUEST["pluginclass"];
$profile=$_REQUEST["profile"];
if($profile=="")
{
	$profile=null;
}
$plinst=Magmi_PluginHelper::getInstance($profile)->createInstance($plclass);
echo $plinst->getOptionsPanel()->getHtml();