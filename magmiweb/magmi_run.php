
<?php 
	function initlog()
	{
		set_time_limit(0);
		$f=fopen("./tmp_out.txt","w");
		fclose($f);
	}
	
	function weblog($data,$type)
	{
			$f=fopen("./tmp_out.txt","a");
			fwrite($f,"$type:$data\n");
			fclose($f);
			
	}
	
?>

<?php 
		require_once("../magento_mass_importer.class.php");
		if(MagentoMassImporter::getState()!=="running")
		{
			initlog();
			$cnf="../magento_mass_importer.ini";
			require_once("../magento_mass_importer.class.php");
			$mmi_imp=new MagentoMassImporter();
			$mmi_imp->loadProperties($cnf);
			$mmi_imp->setLoggingCallback("weblog");
			$mmi_imp->import($_REQUEST);
			
		}
?>
