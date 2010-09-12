
<?php 
	function initlog()
	{
		set_time_limit(0);
		$f=fopen("../state/tmp_out.txt","w");
		fclose($f);
	}
	
	function weblog($data,$type)
	{
			$f=fopen("../state/tmp_out.txt","a");
			fwrite($f,"$type:$data\n");
			fclose($f);
			
	}
	
?>

<?php 
		require_once("../inc/magmi_importer.php");
		require_once("../inc/magmi_statemanager.php");
		
		if(Magmi_StateManager::getState()!=="running")
		{
			Magmi_StateManager::setState("idle");
			initlog();
			$mmi_imp=new MagentoMassImporter();
			$mmi_imp->setLoggingCallback("weblog");
			$mmi_imp->import($_REQUEST);
			
		}
?>
