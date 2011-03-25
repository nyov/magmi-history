<?php
class Magmi_ConfigurableItemProcessor extends Magmi_ItemProcessor
{

	
	private $_configurable_attrs=array();
	private $_use_defaultopc=false;
	
	public function initialize($params)
	{
			
	}
	
	public function getPluginInfo()
	{
		return array(
            "name" => "Configurable Item processor",
            "author" => "Dweeves",
            "version" => "1.0.9"
            );
	}
	
public function getConfigurableOptsFromAsId($asid)
{
	if(!isset($this->_configurable_attrs[$asid]))
	{
		$ea=$this->tablename("eav_attribute");
		$eea=$this->tablename("eav_entity_attribute");
		$eas=$this->tablename("eav_attribute_set");
		$eet=$this->tablename("eav_entity_type");
	
		$sql="SELECT ea.attribute_code FROM `$ea` as ea
		JOIN $eet as eet ON eet.entity_type_id=ea.entity_type_id AND eet.entity_type_id=?
		JOIN $eas as eas ON eas.entity_type_id=eet.entity_type_id AND (eas.attribute_set_id=? OR eas.attribute_set_id=eet.default_attribute_set_id)
		JOIN $eea as eea ON eea.attribute_id=ea.attribute_id";
		$cond="ea.is_user_defined=1";
		if($this->_mmi->magversion=="1.4.x")
		{
			$cea=$this->tablename("catalog_eav_attribute");
			$sql.=" JOIN $cea as cea ON cea.attribute_id=ea.attribute_id AND cea.is_global=1 AND cea.is_configurable=1";
		}
		else
		{
			$cond.=" AND ea.is_global=1 AND ea.is_configurable=1";
		}
		$sql.=" WHERE $cond
			GROUP by ea.attribute_id";

		$result=$this->selectAll($sql,array($this->_mmi->prod_etype,$asid));
		foreach($result as $r)
		{
			$this->_configurable_attrs[$asid][]=$r["attribute_code"];
		}
	}	
	return $this->_configurable_attrs[$asid];
}

	
	public function doLink($pid,$cond)
	{
			$cpsl=$this->tablename("catalog_product_super_link");
			$cpr=$this->tablename("catalog_product_relation");
			$cpe=$this->tablename("catalog_product_entity");
			$sql="DELETE cpsl.*,cpsr.* FROM $cpsl as cpsl
				JOIN $cpr as cpsr ON cpsr.parent_id=cpsl.parent_id
				WHERE cpsl.parent_id=?";
			$this->delete($sql,array($pid));
			//recreate associations
			$sql="INSERT INTO $cpsl (`parent_id`,`product_id`) SELECT cpec.entity_id as parent_id,cpes.entity_id  as product_id  
				  FROM $cpe as cpec 
				  JOIN $cpe as cpes ON cpes.type_id='simple' AND cpes.sku $cond
			  	  WHERE cpec.entity_id=?";
			$this->insert($sql,array($pid));
			$sql="INSERT INTO $cpr (`parent_id`,`child_id`) SELECT cpec.entity_id as parent_id,cpes.entity_id  as child_id  
				  FROM $cpe as cpec 
				  JOIN $cpe as cpes ON cpes.type_id='simple' AND cpes.sku $cond
			  	  WHERE cpec.entity_id=?";
			$this->insert($sql,array($pid));
		
	}
	
	
	public function autoLink($pid)
	{
		$this->dolink($pid,"LIKE CONCAT(cpec.sku,'%')");
	}
	
	public function fixedLink($pid,$skulist)
	{
		$arrin=csl2arr($skulist);
		$skulist=implode(",",$this->quotearr($arrin));
		unset($arrin);
		$this->dolink($pid,"IN ($skulist)");		
	}
	
	public function processItemBeforeId(&$item,$params)
	{
		if($this->_use_defaultopc)
		{
			$item["options_container"]="container2";
		}
	}
	
	public function processItemAfterId(&$item,$params)
	{
		//if item is not configurable, nothing to do
		if($item["type"]!=="configurable")
		{
			return true;
		}		
		$confopts=$this->getConfigurableOptsFromAsId($params["asid"]);
		//limit configurable options to ones presents in item
		$confopts=array_intersect(array_keys($item),$confopts);
		
		//if no configurable attributes, nothing to do
		if(count($confopts)==0)
		{
			return true;
		}
		//set product to have options & required
		$tname=$this->tablename('catalog_product_entity');
		$sql="UPDATE $tname SET has_options=1,required_options=1 WHERE entity_id=?";
		$this->update($sql,$params["product_id"]);
		//matching mode
		//if associated skus 
		$matchmode=(isset($item["simples_skus"])?(trim($item["simples_skus"])!=""?"fixed":"none"):"auto");
		
		
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
		
		switch($matchmode)
		{
			case "none":
				break;
			case "auto":
				//destroy old associations
				$this->autoLink($pid);
				break;
			case "fixed":
				$this->fixedLink($pid,$item["simples_skus"]);
				unset($item["simples_skus"]);
				break;
			default:
				break;
		}
		
		return true;
	}
	
	public function processColumnList(&$cols,$params=null)
	{
		if(!in_array("options_container",$cols))
		{
			$cols=array_unique(array_merge($cols,"options_container"));
			$this->_use_defaultopc=true;
		}
	}
}