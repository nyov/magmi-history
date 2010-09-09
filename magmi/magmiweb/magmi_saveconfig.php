<?php
require_once("../properties.php");
$props=new Properties();
$props->setPropsFromFlatArray($_POST);
$props->save("../magento_mass_importer.ini");
header("Location: magmi.php?run=1");