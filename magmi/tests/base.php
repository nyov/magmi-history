<?php
 require_once("../magento_mass_importer.class.php");
 $mmi=new MagentoMassImporter();
 $mmi->loadProperties(dirname(dirname(__FILE__))."/conf/magento_mass_importer.ini");
 $mmi->import(array("filename"=>"/media/Data/magento/var/import/test.csv"));