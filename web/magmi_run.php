<?php 
	session_start();
	$params=$_SESSION["magmi_import_params"];
	session_write_close();
	
	ini_set("display_errors",1);
	require_once("../inc/magmi_statemanager.php");
	require_once("../inc/magmi_importer.php");

	function initlog()
	{
		set_time_limit(0);
		$f=fopen(dirname(Magmi_StateManager::getStateFile())."/tmp_out.txt","w");
		fclose($f);
	}
	
	function weblog($data,$type)
	{
			$f=fopen(dirname(Magmi_StateManager::getStateFile())."/tmp_out.txt","a");
			fwrite($f,"$type:$data\n");
			fclose($f);
			
	}

	if(Magmi_StateManager::getState()!=="running")
	{
		Magmi_StateManager::setState("idle");
		initlog();
		$mmi_imp=new MagentoMassImporter();
		$mmi_imp->setLoggingCallback("weblog");
		$mmi_imp->import($params);
		
	}
?>
