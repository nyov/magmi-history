<?php
require_once("../properties.php");
$props=new Properties();
$props->setPropsFromFlatArray($_POST);
$props->save("../conf/magmi.ini");
header("Location: magmi.php?run=1");