<?php
session_start();

function extractZipDir($zip,$bdir,$zdir)
{
	$files=array();
	for($i = 0; $i < $zip->numFiles; $i++) {
        $entry = $zip->getNameIndex($i);
		if (preg_match("|^$zdir/(.*)|",$entry,$matches)) 
		{
		  if($matches[1]=='')
		  {
		   $zip->deleteIndex($i);
		  }
		  else
		  {
		    $zip->renameIndex($i,$matches[1]);
		    //Add the entry to our array if it in in our desired directory
            $files[] = $matches[1];
	      }
        }
	}
   
	if(count($files)>0)
	{
        $ok=$zip->extractTo($bdir,$files);
	}
	else
	{
		$ok=false;
	}
	return $ok;
}

unset($_SESSION["magmi_install_error"]);
$zip = new ZipArchive();
$res = $zip->open($_FILES["magmi_package"]["tmp_name"]);
try
{
	$info=$zip->statName('magmi/conf/magmi.ini.default');
	
	if ($res === TRUE && $info!==FALSE) 
    {
         $ok=extractZipDir($zip, "..", "magmi");
         $zip->close();
		 if(file_exists("../inc/magmi_postinstall.php"))
		 {
		 	require_once("../inc/magmi_postinstall.php");
		 	if(function_exists("magmi_post_install"))
		 	{
		 		$result=magmi_post_install();
		 	}
		 }
	    $_SESSION["magmi_install"]=array("info","Magmi updated<br>".$result["OK"]);
	} 
    else
    {
    	$zip->close();
    	$_SESSION["magmi_install"]=array("error","Invalid Magmi Archive");
    }
    if(!$ok)
    {
    	$_SESSION["magmi_install"]=array("error","Cannot unzip Magmi Archive");
    }
    session_write_close();
}
catch(Exception $e)
{
	session_write_close();
	die($e->getMessage());
}
header("Location: ./magmi.php");