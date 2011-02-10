<?php
class Magmi_ConfigurableItemProcessor extends Magmi_ItemProcessor
{

	private $_matchmode="auto";
	private $_configurable_attrs=array();
	
	public function initialize($params)
	{
			
	}
	
	public function getPluginInfo()
	{
		return array(
            "name" => "Configurable Item processor",
            "author" => "Dweeves",
            "version" => "1.0.0"
            );
	}
	
public function initConfigurableOpts($cols)
	{
		$ea=$this->tablename("eav_attribute");
		$qcolstr=substr(str_repeat("?,",count($cols)),0,-1);
		if($this->_mmi->magversion=="1.4.x")
		{
			$cea=$this->tablename("catalog_eav_attribute");
			$sql="SELECT ea.attribute_code  FROM `$cea` as cea
				JOIN $ea as ea ON ea.attribute_id=cea.attribute_id AND ea.is_user_defined=1 AND ea.attribute_code IN ($qcolstr)
 				WHERE cea.is_global= 1 AND cea.is_configurable=1 ";
		}
		else
		{
			$sql="SELECT ea.attribute_code FROM $ea as ea WHERE ea.is_user_defined=1 AND ea.is_global=1 and ea.is_configurable=1 AND ea.attribute_code IN ($qcolstr) ";
		}
		$result=$this->selectAll($sql,$cols);
		foreach($result as $r)
		{
			$this->_configurable_attrs[]=$r["attribute_code"];
		}
	}
	
	public function processColumnList($cols)
	{
		//gather configurable options attribute code
		$this->initConfigurableOpts($cols);	
		return true;
	}
	
	public function processItemAfterId(&$item,$params)
	{
		//if item is not configurable, nothing to do
		if($item["type"]!=="configurable")
		{
			return true;
		}
		if(count($this->_configurable_attrs)==0)
		{
			return true;
		}
		
		//check if item has exising options
		
		$pid=$params["product_id"];
		$psa=$this->tablename("catalog_product_super_attribute");
		$sql="DELETE FROM `$psa` WHERE `product_id`=?";
		$this->delete($sql,array($pid));
	
			
		//process configurable options
		$ins_sa=array();
		$data_sa=array();
		$ins_sal=array();
		$data_sal=array();
		foreach($confopts as $confopt)
		{
			$attrinfo=$this->getAttrInfo($confopt);
			$cpsa=$this->tablename("catalog_product_super_attribute");
			$cpsal=$this->tablename("catalog_product_super_attribute_label");
			$sql="INSERT INTO `$cpsa` (`product_id`,`attribute_id`,`position`) VALUES (?,?,?)";
			//inserting new options
			$psaid=$this->insert($sql,array($pid,$attrinfo["attribute_id"],0));		
			//for all stores defined for the item
			$sids=$this->getItemStoreIds($item,0);
			$data=array();
			$ins=array();
			foreach($sids as $sid)
			{
				$data[]=$psaid;
				$data[]=$sid;
				$ins[]="(?,?,1,'')";
			}
			$sql="INSERT INTO `$cpsal` (`product_super_attribute_id`,`store_id`,`use_default`,`value`) VALUES ".implode(",",$ins);
			$this->insert($sql,$data);
		}
		if($this->_matchmode=="auto")
		{
			//destroy old associations
			$cpsl=$this->tablename("catalog_product_super_link");
			$cpr=$this->tablename("catalog_product_relation");
			$sql="DELETE cpsl.*,cpsr.* FROM $cpsl as cpsl
				JOIN $cpr as cpsr ON cpsr.parent_id=cpsl.parent_id
				WHERE cpsl.parent_id=?";
			$this->delete($sql,array($pid));
			//recreate associations
			$sql="INSERT INTO $cpsl (`parent_id`,`product_id`) SELECT cpec.entity_id as parent_id,cpes.entity_id  as product_id  
				  FROM catalog_product_entity as cpec 
				  JOIN catalog_product_entity as cpes ON cpes.type_id='simple' AND cpes.sku LIKE CONCAT(cpec.sku,'%')
			  	  WHERE cpec.entity_id=?";
			$this->insert($sql,array($pid));
			$sql="INSERT INTO $cpr (`parent_id`,`child_id`) SELECT cpec.entity_id as parent_id,cpes.entity_id  as child_id  
				  FROM catalog_product_entity as cpec 
				  JOIN catalog_product_entity as cpes ON cpes.type_id='simple' AND cpes.sku LIKE CONCAT(cpec.sku,'%')
			  	  WHERE cpec.entity_id=?";
			$this->insert($sql,array($pid));
		}
		return true;
	}
}