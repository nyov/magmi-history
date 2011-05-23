<?php
class RelatedProducts extends Magmi_ItemProcessor
{
	
 public function getPluginInfo()
 {
 	return array(
            "name" => "Product relater",
            "author" => "Dweeves",
            "version" => "1.0.0"
            );
 }
 
 public function checkRelated(&$rinfo)
 {
  $sql="SELECT testid.sku,cpe.sku as esku FROM ".$this->arr2select($rinfo["direct"],"sku")." AS testid
  LEFT JOIN ".$this->tablename("catalog_product_entity")." as cpe ON cpe.sku=testid.sku
  WHERE testid.sku NOT LIKE '%re::%'
  HAVING esku IS NULL";
  $result=$this->selectAll($sql,$rinfo["direct"]);
  $to_delete=array();
  foreach($result as $row)
  {
  	$this->log("Unknown related sku ".$row["sku"],"warning");
  	$to_delete[]=$row["sku"];
  }
  $rinfo["direct"]=array_diff($rinfo["direct"],$to_delete);
  return count($rinfo["direct"]);
 }
 
 public function processItemAfterId(&$item,$params=null)
 {
 	$related=isset($item["re_skus"])?$item["re_skus"]:null;
 	$xrelated=isset($item["xre_skus"])?$item["xre_skus"]:null;
	$pid=$params["product_id"];
	$new=$params["new"];
 
	if(isset($related))
 	{
 		$rinf=$this->getRelInfos($related);
		if($new==false)
		{
			$this->deleteRelatedItems($item,$rinf["del"]);
		}
 		$this->setRelatedItems($item,$rinf["add"]);
 	}
 	if(isset($xrelated))
 	{
 		$rinf=$this->getRelInfos($xrelated);
 		if($new==false)
		{
			$this->deleteXRelatedItems($item,$rinf["del"]);
		}
		$this->setXRelatedItems($item,$rinf["add"]);
 	}
 }
 
 public function deleteRelatedItems($item,$inf)
 {
 	$joininfo=$this->buildJoinCond($item,$inf);
 	if($joininfo["join"]!="")
 	{
 	$sql="DELETE cplai.*,cpl.*
 		  FROM ".$this->tablename("catalog_product_entity")." as cpe
 		  JOIN ".$this->tablename("catalog_product_link")." as cpl ON cpl.product_id=cpe.entity_id
 		  JOIN ".$this->tablename("catalog_product_link_attribute_int")." as cplai ON cplai.link_id=cpl.link_id
		  JOIN ".$this->tablename("catalog_product_entity")." as cpe2 ON cpe2.sku!=cpe.sku AND {$joininfo["join"]}
		  JOIN ".$this->tablename("catalog_product_link_type")." as cplt ON cplt.code='relation'
		  WHERE cpe.sku=?";
	$this->delete($sql,array_merge($joininfo["data"],array($item["sku"])));
 	}
 }
 
 public function deleteXRelatedItems($item,$inf)
 {
 	$joininfo=$this->buildJoinCond($item,$inf);
 	if($joininfo["join"]!="")
 	{
 	$sql="DELETE cplai.*,cpl.*
 		  FROM ".$this->tablename("catalog_product_entity")." as cpe
 		  JOIN ".$this->tablename("catalog_product_link")." as cpl ON cpl.product_id=cpe.entity_id
 		  JOIN ".$this->tablename("catalog_product_link_attribute_int")." as cplai ON cplai.link_id=cpl.link_id
		  JOIN ".$this->tablename("catalog_product_entity")." as cpe2 ON cpe2.sku!=cpe.sku AND (cpe2.sku=? OR {$joininfo["join"]})
		  JOIN ".$this->tablename("catalog_product_link_type")." as cplt ON cplt.code='relation'
		  WHERE cpe.sku=? OR {$joininfo["join"]}";
	$this->delete($sql,array_merge(array($item["sku"]),$joininfo["data"],array($item["sku"]),$joininfo["data"]));
 	}
 }
 
 public function getDirection(&$inf)
 {
 	$dir="+";
 	if($inf[0]=="-" || $inf[0]=="+")
 	{
 		$dir=$inf[0];
 		$inf=substr($inf,1);
 	}
 	return $dir;
 	
 }
 public function getRelInfos($relationdef)
 {
 	$relinfos=explode(",",$relationdef);
 	$relskusadd=array("direct"=>array(),"re"=>array());
 	$relskusdel=array("direct"=>array(),"re"=>array());
 	foreach($relinfos as $relinfo)
 	{
 		$rinf=explode("::",$relinfo);
 		if(count($rinf)==1)
 		{
 			if($this->getDirection($rinf[0])=="+")
			{
 				$relskusadd["direct"][]=$rinf[0];
			}
			else
			{
 				$relskusdel["direct"][]=$rinf[0];
			}
 		}
 		
 		if(count($rinf)==2)
 		{
 			$dir=$this->getDirection($rinf[0]);
 			if($dir=="+")
 			{
 				switch($rinf[0])
 				{
 					case "re":
 						$relskusadd["re"][]=$rinf[1];
 						break;
 				}
 			}
 			else
 			{
 				switch($rinf[0])
 				{
 					case "re":
 						$relskusdel["re"][]=$rinf[1];
						break;
 				}
 			}
 		}
 	}	
 	
 	return array("add"=>$relskusadd,"del"=>$relskusdel);
 }
 
 public function buildJoinCond($item,$rinfo)
 {
	$joinconds=array();
 	$data=array();
 	if(count($rinfo["direct"])>0)
 	{
 		$joinconds[]="cpe2.sku IN (".$this->arr2values($rinfo["direct"]).")";	
 		$data=array_merge($data,$rinfo["direct"]);
 	}
 	if(count($rinfo["re"])>0)
 	{
 		foreach($rinfo["re"] as $rinf)
 		{
 			$joinconds[]="cpe2.sku REGEXP ?";
			$data[]=$rinf;
 		}
 	}
 	$join = implode(" OR ",$joinconds);
 	if($join!="")
 	{
 		$join="($join)";
 	}
 	return array("join"=>$join,"data"=>$data);
 }
 
 
 public function setRelatedItems($item,$rinfo)
 {
 	if($this->checkRelated($rinfo)>0)
 	
 	{
 	$joininfo=$this->buildJoinCond($item,$rinfo);
 	if($joininfo["join"]!="")
 	{
  		//insert into link table
 		$bsql="SELECT cplt.link_type_id,cpe.entity_id as product_id,cpe2.entity_id as linked_product_id 
			FROM ".$this->tablename("catalog_product_entity")." as cpe
			JOIN ".$this->tablename("catalog_product_entity")." as cpe2 ON cpe2.sku!=cpe.sku AND {$joininfo["join"]}
			JOIN ".$this->tablename("catalog_product_link_type")." as cplt ON cplt.code='relation'
			WHERE cpe.sku=?";
 	$sql="INSERT IGNORE INTO ".$this->tablename("catalog_product_link")." (link_type_id,product_id,linked_product_id)  $bsql";
 	$data=array_merge($joininfo["data"],array($item["sku"]));
 	$this->insert($sql,$data);
 	$this->updateLinkAttributeTable($joininfo);
 	}
 	}
 }
 
 public function setXRelatedItems($item,$rinfo)
 {
 	if($this->checkRelated($rinfo)>0)
 	
 	{
 	$joininfo=$this->buildJoinCond($item,$rinfo);
 	if($joinfo["join"]!="")
 	{
  	//insert into link table
 	$bsql="SELECT cplt.link_type_id,cpe.entity_id as product_id,cpe2.entity_id as linked_product_id 
			FROM ".$this->tablename("catalog_product_entity")." as cpe
			JOIN ".$this->tablename("catalog_product_entity")." as cpe2 ON cpe2.sku!=cpe.sku AND (cpe2.sku=? OR {$joininfo["join"]})
			JOIN ".$this->tablename("catalog_product_link_type")." as cplt ON cplt.code='relation'
			WHERE cpe.sku=? OR {$joininfo["join"]}";
 	$sql="INSERT IGNORE INTO ".$this->tablename("catalog_product_link")." (link_type_id,product_id,linked_product_id)  $bsql";
 	$data=array_merge(array($item["sku"]),$joininfo["data"],array($item["sku"]),$joininfo["data"]);
 	$this->insert($sql,$data);
 	$this->updateLinkAttributeTable($joininfo);
 	}
 	}
 }
 
 public function updateLinkAttributeTable($joininfo)
 {
 	 	//insert into attribute link attribute int table,reusing the same relations
 	//this enable to mass add 
 	$bsql="SELECT cpl.link_id,cpla.product_link_attribute_id,0 as value
	   	   FROM ".$this->tablename("catalog_product_entity")." AS cpe
		   JOIN ".$this->tablename("catalog_product_entity")." AS cpe2 ON cpe2.sku!=cpe.sku
		   JOIN ".$this->tablename("catalog_product_link_type")." AS cplt ON cplt.code='relation'
		   JOIN ".$this->tablename("catalog_product_link_attribute")." AS cpla ON cpla.product_link_attribute_code='position' AND cpla.link_type_id=cplt.link_type_id
		   JOIN ".$this->tablename("catalog_product_link") ." AS cpl ON cpl.link_type_id=cplt.link_type_id AND cpl.product_id=cpe.entity_id AND cpl.linked_product_id=cpe2.entity_id";
 	$sql="INSERT IGNORE INTO ".$this->tablename("catalog_product_link_attribute_int")." (link_id,product_link_attribute_id,value) $bsql";
 	$this->insert($sql,$joininfo["data"]);
 	
 }
}