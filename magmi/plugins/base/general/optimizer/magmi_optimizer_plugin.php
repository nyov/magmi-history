<?php
class Magmi_OptimizerPlugin extends Magmi_GeneralImportPlugin
{
	public function getPluginInfo()
	{
		return array("name"=>"Magmi Optimizer",
					 "author"=>"Dweeves",
					 "version"=>"1.0.3");
	}
	
	public function beforeImport()
	{
		$tbls=array("eav_attribute_option_value"=>"MAGMI_EAOV_OPTIMIZATION_IDX",
					"catalog_product_entity_media_gallery"=>"MAGMI_CPEM_OPTIMIZATION_IDX");
		$this->log("Optimizing magmi","info");
		foreach($tbls as $tblname=>$idxname)
		{
			try
			{
				$t=$this->tablename($tblname);
				$this->log("Adding index $idxname on $t","info");
				$sql="ALTER  TABLE $t ADD INDEX $idxname (`value`)";
				$this->exec_stmt($sql);
			}
			catch(Exception $e)
			{
				//ignore exception
				$this->log("Already optmized!","info");
			}
		}
		return true;
	}
	
		
	public function initialize($params)
	{
		
	}
}