<?php
class Magmi_OptimizerPlugin extends Magmi_GeneralImportPlugin
{
	public function getPluginInfo()
	{
		return array("name"=>"Magmi Optimizer",
					 "author"=>"Dweeves",
					 "version"=>"1.0.0");
	}
	
	public function beforeImport()
	{
		try
		{
			$this->log("Optimizing magmi","info");
			$eaov=$this->tablename("eav_attribute_option_value");
			$sql="ALTER IGNORE TABLE $eaov ADD INDEX MAGMI_EAOV_OPTIMIZATION_IDX (`value`)";
			$this->exec_stmt($sql);
		}
		catch(Exception $e)
		{
			//ignore exception
			$this->log("Already optmized!");
		}
		return true;
	}
	
		
	public function initialize($params)
	{
		
	}
}