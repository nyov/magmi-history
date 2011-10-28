<?php
session_start();

function extractZipDir($zip,$bdir,$zdir)
{
	$files=array();
	for($i = 0; $i < $zip->numFiles; $i++) {
        $entry = $zip->getNameIndex($i);
		if (strpos($entry, "/$zdir/")) {
          //Add the entry to our array if it in in our desired directory
          $files[] = $entry;
	 }
	}
	$zip->extractTo($bdir,$files);
}

unset($_SESSION["magmi_install_error"]);
$zip = new ZipArchive();
$res = $zip->open($_FILES["magmi_package"]["tmp_name"]);
try
{
	$info=$zip->statName('magmi/conf/magmi.ini.default');
	
	if ($res === TRUE && $info!==FALSE) 
    {
         extractZipDir($zip, "..", "magmi");
         $zip->close();
         $_SESSION["magmi_install"]="OK";
         $_SESSION["magmi_install"]=array("info","Magmi updated");
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