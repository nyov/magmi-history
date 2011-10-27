<?php
session_start();
unset($_SESSION["magmi_install_error"]);
$zip = new ZipArchive();
$res = $zip->open($_FILES["magmi_package"]["tmp_name"]);
try
{
	$info=$zip->statName('magmi/conf/magmi.ini.default');
	$mode="full";
	if($info==false)
	{
		$info=$zip->statName('conf/magmi.ini.default');
		$mode=$info==false?"":"updpack";
	}	
	if ($res === TRUE && mode!="") 
    {
         $zip->extractTo("..");
         $zip->close();
         $_SESSION["magmi_install"]="OK";
         $_SESSION["magmi_install"]=array("info",$mode=="updpack"?"Magmi Update Version installed":"Magmi Release installed");
    } 
    else 
    {
    	$zip->close();
    	$_SESSION["magmi_install"]=array("error","Invalid Magmi Archive");
    }
    session_write_close();
}
catch(Exception $e)
{
	session_write_close();
	die($e->getMessage());
}
header("Location: ./magmi.php");