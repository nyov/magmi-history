<?php 
require_once("magmi_config.php");
require_once("magmi_pluginhelper.php");
$engclass=$_REQUEST["engineclass"];
$ph=Magmi_PluginHelper::getInstance($profile);
$ph->setEngineClass($engclass);
$profilelist=$ph->getEngine()->getProfileList();
$conf_ok=1;
?>
<?php include_once("./magmi_profile_panel.php")?>