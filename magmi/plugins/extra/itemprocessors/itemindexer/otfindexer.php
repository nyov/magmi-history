<?php

class ItemIndexer extends Magmi_ItemProcessor
{
	
	protected $_toindex;
	protected $tns;
	
	public function getPluginInfo()
	{
		return array(
            "name" => "On the fly indexer",
            "author" => "Dweeves",
            "version" => "0.1.1"
            );
	}
	
	public function initialize($params)
	{
		$this->_toindex=null;
		//initialize shortname array for tables
		$this->tns=array("cpe"=>$this->tablename("catalog_product_entity"),
						 "cce"=>$this->tablename("catalog_category_entity"),
						 "ccp"=>$this->tablename("catalog_category_product"),
						 "cpw"=>$this->tablename("catalog_product_website"),
						 "cs"=>$this->tablename("core_store"),
						 "cpev"=>$this->tablename("catalog_product_entity_varchar"),
						 "cpei"=>$this->tablename("catalog_product_entity_int"),
						 "ccev"=>$this->tablename("catalog_category_entity_varchar"),
						 "ea"=>$this->tablename("eav_attribute"),
						 "ccpi"=>$this->tablename("catalog_category_product_index"),
						 "curw"=>$this->tablename("core_url_rewrite"));	
	}
	
	/** 
	 * Return item category ids from PID (full tree)
	 * @param $baselevel :  begin tree from specified category level (defaults 0)
	 * @return array of category ids (all mixed branches) for the item
	 * **/
	public function getItemCategoryIds($pid,$baselevel=0)
	{
		$sql="SELECT cce.path FROM {$this->tns["ccp"]} as ccp 
		JOIN {$this->tns["cce"]} as cce ON ccp.category_id=cce.entity_id AND cce.level>?
		WHERE ccp.product_id=?";
		$result=$this->selectAll($sql,array($baselevel,$pid));
		$catidlist=array();
		foreach($result as $row)
		{
			$catidlist=array_merge($catidlist,explode("/",$row["path"]));	
		}
		$catidlist=array_unique($catidlist);
		sort($catidlist);
		return $catidlist;
	}
	
	
	/**
	 * Build catalog_category_product_index entry for given pid
	 * @param int $pid , product id to create index entry for
	 */
	public function buildCatalogCategoryProductIndex($pid)
	{
		//get all category ids on which the product is affected
		$inf=$this->getAttrInfo("visibility");
		if($inf==null)
		{
			initAttrInfos(array("visibility"));
			$inf=$this->getAttrInfo("visibility");
		}
		$catidlist=$this->getItemCategoryIds($pid);
		//remove the "absolute root" (id 1)
		array_shift($catidlist);
		//let's make a IN placeholder string with that
		$catidin=$this->arr2values($catidlist);
		//first delete lines where last inserted product was
		$sql="DELETE FROM {$this->tns["ccpi"]} WHERE product_id=?";
		$this->delete($sql,$pid);
		//then add lines for index
		$sqlsel="INSERT INTO {$this->tns["ccpi"]} 
				 SELECT cce2.entity_id as category_id,ccp.product_id,ccp.position,IF(cce2.entity_id=ccp.category_id,1,0) as is_parent,cs.store_id,cpev.value as visibility 
				 FROM {$this->tns["ccp"]} as ccp
				 JOIN {$this->tns["cpe"]} as cpe ON ccp.product_id=cpe.entity_id AND cpe.entity_id=?
				 JOIN {$this->tns["cpev"]} as cpev ON cpev.attribute_id=? AND cpev.entity_id=cpe.entity_id
				 JOIN {$this->tns["cce"]} as cce2 ON cce2.entity_id AND (cce2.entity_id=ccp.category_id OR cce2.entity_id IN ($catidin))
				 JOIN {$this->tns["cpw"]} as cps ON cps.product_id=cpe.entity_id 
				 JOIN {$this->tns["cs"]} AS cs ON cs.website_id=cps.website_id
				 GROUP by cce2.entity_id,store_id
	    		 ORDER by store_id,cce2.entity_id";
		//build data array for request
		$data=array_merge(array($pid,$inf["attribute_id"]),$catidlist);
		//create index line(s)
		$this->insert($sqlsel,$data);
	}
	
	 /**
	 * Build core_url_rewrite index entry for given pid
	 * @param int $pid , product id to create index entry for
	 */

	public function buildUrlRewrite($pid)
	{
		//make sure we have attribute product info for needed attributes
		$this->initAttrInfos(array("url_key","name"));
		//getinfo for attributes
		$urlkinf=$this->getAttrInfo("url_key");
		$nameinf=$this->getAttrInfo("name");
		$arr=array($urlkinf["attribute_id"],$nameinf["attribute_id"]);
		
		//build in string
		$instr=$this->arr2values($arr);
			
		$sql="SELECT attribute_id,cpev.value FROM {$this->tsn["cpev"]} as cpev WHERE entity_id=? AND attribute_id IN ($instr)";
		$result=$this->selectAll($sql,array_merge(array($pid),$arr));
		//see what we get as available product attributes
		foreach($result as $row)
		{
			if($row["attribute_id"]==$urlkinf["attribute_id"])
			{
				$pburlk=$row["value"];
			}
			if($row["attribute_id"]==$nameinf["attribute_id"])
			{
				$pname=$row["value"];
			}
		}
		//if we've got an url key use it, otherwise , make a slug from the product name as url key
		$purlk=isset($pburlk)?$pburlk:Slugger::slug($pname);
		
		//delete old "system" url rewrite entries for product
		$sql="DELETE FROM {$this->tns["curw"]} WHERE product_id=? AND is_system=1";
		$this->delete($sql,$pid);
		
		//product url index info 
		$produrlsql="SELECT cpe.entity_id,cs.store_id,
				 CONCAT('product/',cpe.entity_id) as id_path,
				 CONCAT('catalog/product/view/id/',cpe.entity_id) as target_path,
				 ? AS request_path,
				 1 as is_system
				 FROM {$this->tns["cpe"]} as cpe
				 JOIN {$this->tns["cpw"]} as cpw ON cpw.product_id=cpe.entity_id
				 JOIN {$this->tns["cs"]} as cs ON cs.website_id=cpw.website_id
				 JOIN {$this->tns["ccp"]} as ccp ON ccp.product_id=cpe.entity_id
				 JOIN {$this->tns["cce"]} as cce ON ccp.category_id=cce.entity_id
				 WHERE cpe.entity_id=?";
		
		//insert lines
		$sqlprod="INSERT INTO {$this->tns["curw"]} (product_id,store_id,id_path,target_path,request_path,is_system) $produrlsql";
		$this->insert($sqlprod,array($purlk,$pid));
		
		//if we set "use categories in url" flag
		if($this->getParam("OTFI:usecatinurl"))
		{
			//extract product category names with tree level > 1
			$catids=$this->getItemCategoryIds($pid,1);
			//make IN placeholder from returned values
			$catin=$this->arr2values($catids);
			//use tricky double join on eav_attribute to find category related 'name' attribute using 'children' category only attr to distinguish on category entity_id
			$sql="SELECT ccev.value FROM {$this->tns["ccp"]} as ccp
			  JOIN {$this->tns["cce"]} cce ON cce.entity_id IN ($catin)
			  JOIN {$this->tns["ea"]} as ea1 ON ea1.attribute_code='children'
			  JOIN {$this->tns["ea"]} as ea2 ON ea2.attribute_code='name' AND ea2.entity_type_id=ea1.entity_type_id
			  JOIN {$this->tns["ccev"]} as ccev ON ccev.attribute_id=ea2.attribute_id AND ccev.entity_id=cce.entity_id
			  WHERE ccp.product_id=?";
			$result=$this->selectAll($sql,array_merge($catids,array($pid)));
			$names=array();
			//iterate on all names
			foreach($result as $row)
			{
				$names[]=$row["value"];
			}
			//make string with that
			$namestr=implode("/",$names);
			//build category url key (allow / in slugging)
			$curlk=Slugger::slug($namestr,true);
			//product + category url entries request
			$prodcaturlsql="
				 SELECT cpe.entity_id,cs.store_id,cce.entity_id as category_id,
				 CONCAT('product/',cpe.entity_id,'/',cce.entity_id) as id_path,
				 CONCAT('catalog/product/view/id/',cpe.entity_id,'/category/',cce.entity_id) as target_path, 
				 CONCAT(?,'/',?) as request_path,
				 1 as is_system
				 FROM {$this->tns["cpe"]} as cpe
				 JOIN {$this->tns["cpw"]} as cpw ON cpw.product_id=cpe.entity_id
				 JOIN {$this->tns["cs"]} as cs ON cs.website_id=cpw.website_id
				 JOIN {$this->tns["ccp"]} as ccp ON ccp.product_id=cpe.entity_id
				 JOIN {$this->tns["cce"]} as cce ON ccp.category_id=cce.entity_id
				 WHERE cpe.entity_id=?";
			//insert into index
			$sqlprodcat="INSERT INTO {$this->tns["curw"]} (product_id,store_id,category_id,id_path,target_path,request_path,is_system) $prodcaturlsql";
			$this->insert($sqlprodcat,array($curlk,$purlk,$pid));	
		}
			
				 
	}
	
	/**
	 * OBSOLETED , TO BE REWORKED LATER
	 */
	public function buildPriceIndex()
	{
		$pid=$this->_toindex["pid"];
		$priceidx=$this->tablename("catalog_product_index_price");
		$sql="DELETE FROM $priceidx WHERE entity_id=?";
		$this->delete($sql,$pid);
		$cpe=$this->tablename("catalog_product_entity");
		$cs=$this->tablename("core_store");
		$cg=$this->tablename("customer_group");
		$cped=$this->tablename("catalog_product_entity_decimal");
		$ea=$this->tablename("eav_attribute");
		$cpetp=$this->tablename("catalog_product_entity_tier_price");
		$cpei=$this->tablename("catalog_product_entity_int");
		$sql="INSERT INTO $priceidx SELECT cped.entity_id,
											cg.customer_group_id,
											cs.website_id,
											cpei.value as tax_class_id,
											cped.value as price,
											MIN(cped.value) as final_price,
											MIN(cped.value) as min_price,
											MIN(cped.value) as max_price,
											cpetp2.value as tier_price
				FROM $cpe as cpe 
				JOIN $cs as cs ON cs.store_id!=0
				JOIN $cped as cped ON cped.store_id=cs.store_id AND cped.entity_id=cpe.entity_id
				JOIN $cg as cg
				JOIN $ea as ead ON ead.entity_type_id=4  AND ead.attribute_code IN('price','special_price','minimal_price') AND cped.attribute_id=ead.attribute_id 
				JOIN $ea as eai ON eai.entity_type_id=4 AND eai.attribute_code='tax_class_id' 
				LEFT JOIN $cpetp as cpetp ON cpetp.entity_id=cped.entity_id 
				LEFT JOIN $cpetp as cpetp2 ON cpetp2.entity_id=cped.entity_id AND cpetp2.customer_group_id=cg.customer_group_id
				LEFT JOIN $cpei as cpei ON cpei.entity_id=cpe.entity_id AND cpei.attribute_id=eai.attribute_id 
				WHERE cpe.entity_id=?
				GROUP by cs.website_id,cg.customer_group_id
				ORDER by cg.customer_group_id,cs.website_id
		";
		$this->insert($sql,$pid);
		
	}
	
	
	//To be done, find a way to avoid reindexing if not necessary
	public function shouldReindex($item)
	{
		return true;
	}
	
	public function processItemAfterImport(&$item,$params=null)
	{
		$this->reindexLastImported();
		//if current item is not the same than previous one
		if($params["same"]==false)
		{
			if($this->shouldReindex($item))
			{
				$this->_toindex=array("sku"=>$item["sku"],"pid"=>$params["product_id"]);
			}
			else
			{
				$this->log("Do not reindex, no indexed column changed");
			}
		}
		return true;
	}
	
	//index last imported item
	public function reindexLastImported()
	{
		if($this->_toindex!=null)
		{
			//$this->buildPrinceIndex();
			$pid=$this->_toindex["pid"];
			$this->buildCategoryIndex($pid);
			$this->buildUrlRewrite($pid);
			$this->_toindex=null;
		}		
		
	}
	
	public function afterImport()
	{
		//reindex last item since we index one row later than the current
		$this->reindexLastImported();
	}
	
}


