<?php
class SkuFinderItemProcessor extends Magmi_ItemProcessor
{
 	public function getPluginInfo()
    {
        return array(
            "name" => "SKU Finder",
            "author" => "Dweeves",
            "version" => "0.0.1",
        	"url"=>"http://sourceforge.net/apps/mediawiki/magmi/index.php?title=SKU_Finder"
        );
    }
    
    public function processItemBeforeId(&$item,$params=null)
	{
		$matchfield=$this->getParam("SKUF:matchfield");
		//no item data for selected matching field, skipping
		if(!isset($item[$matchfield]) && trim($item["matchfield"])!=='')
		{
			$this->log("No value for $matchfield in datasource","error");
			return false;
		}
		$attinfo=$this->getAttrInfo($matchfield);
		//no attribute info for matching field, skipping
		if($attinfo==NULL)
		{
			$this->log("$matchfield is not a valid attribute","error");
			return false;
		}
		//now find sku
		$sql="SELECT sku FROM ".$this->tablename("catalog_product_entity")." as cpe JOIN
		catalog_product_entity_".$attinfo["backend_type"]. "ON value=? AND attribute_id=?";
		$stmt=$this->select($sql,array($item[$matchfield],$attinfo["attribute_id"]));
		$n=0;
		while($result=$stmt->fetch())
		{
			//if more than one result, cannot match single sku
			if($n>1)
			{
				$this->log("Several skus match $matchfield value : ".$item["matchfield"],"error");
				return false;
			}
			else
			{
				$item["sku"]=$result["sku"];
			}
			$n++;
		}
		//if no item found, warning & skip
		if($n==0)
		{
				$this->log("No sku found matching $matchfield value : ".$item["matchfield"],"warning");
				return false;
		}
		//found a single sku ! item sku is in place, continue with processor chain
		return true;
	}
	
	static public function getCategory()
	{
		return "Input Data Preprocessing";
	}
}