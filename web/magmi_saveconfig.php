<?php
require_once("../inc/magmi_config.php");
$conf=new Magmi_Config();
$conf->save($_POST);
header("Location: magmi.php?run=1");