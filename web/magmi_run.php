<?php 
	session_start();
	if(isset($_SESSION["magmi_import_params"]))
	{
		$params=$_SESSION["magmi_import_params"];
	}
	else
	{
		$params=$_REQUEST;
	}
	session_write_close();
	if(count($params)==0)
	{
		die("No Parameters set, abort import");
	}
	ini_set("display_errors",1);
	require_once("../inc/magmi_statemanager.php");
	require_once("../inc/magmi_importer.php");

	class FileLogger
	{
		protected $_fname;
		
		public function __construct($fname)
		{
			$this->_fname=$fname;
			$f=fopen($this->_fname,"w");
			fclose($f);
		}

		public function log($data,$type)
		{
			
			$f=fopen($this->_fname,"a");
			fwrite($f,"$type:$data\n");
			fclose($f);
		}
		
	}
	
	if(Magmi_StateManager::getState()!=="running")
	{
		Magmi_StateManager::setState("idle");
		set_time_limit(0);
		$mmi_imp=new MagentoMassImporter();
		$logfile=isset($params["logfile"])?$params["logfile"]:dirname(Magmi_StateManager::getStateFile())."/tmp_out.txt";
		$mmi_imp->setLogger(new FileLogger($logfile));		
		$mmi_imp->import($params);
		
	}
?>
