<?php
require_once("../inc/magmi_config.php");
$conf=new Magmi_Config();
$conf->save($_POST);
$date=filemtime($conf->getConfFile());
echo "Common Configuration saved (".strftime("%c",$date).")";