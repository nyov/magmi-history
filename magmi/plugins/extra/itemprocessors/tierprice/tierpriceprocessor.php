<?php
/**
 * Class Tier price processor
 * @author dweeves
 *
 * This imports tier prices for columns names called "tier_price:"
 */
class TierpriceProcessor extends Magmi_ItemProcessor
{
	protected $_tpcol;

	public function getPluginInfo()
	{
		return array(
            "name" => "Tier price importer",
            "author" => "Dweeves",
            "version" => "0.0.3"
            );
	}

	/**
	 * you can add/remove columns for the item passed since it is passed by reference
	 * @param Magmi_Engine $mmi : reference to magmi engine instance (convenient to perform database operations)
	 * @param unknown_type $item : modifiable reference to item before import
	 * the $item is a key/value array with column names as keys and values as read from csv file.
	 * @return bool :
	 * 		true if you want the item to be imported after your custom processing
	 * 		false if you want to skip item import after your processing
	 */



	public function processItemAfterId(&$item,$params=null)
	{
		$pid=$params["product_id"];
		$tpcol=array_intersect(array_keys($this->_tpcol),array_keys($item));

		foreach($tpcol as $k)
		{
		//get tier price column info
		  $tpinf=$this->_tpcol[$k];
		  //now we've got a customer group id
		  $cgid=$tpinf["id"];
		  //add tier price
		  $sql="INSERT INTO ".$this->tablename("catalog_product_entity_tier_price")."
			(entity_id,all_groups,customer_group_id,qty,value,website_id) VALUES ";
		  $inserts=array();
		  $data=array();
		  $wsids=$this->getItemWebsites($item);
		  $tpvals=explode(";",$item[$k]);
		  foreach($wsids as $wsid)
		  {
		  	//for each non admin website
		  	if($wsid!=0)
		  	{
		  		//for each tier price value definition
		  		foreach($tpvals as $tpval)
		  		{
		  			//split on ":"
		  			$tpvinf=explode(":",$tpval);
		  			//if we have only one item
		  			if(count($tpvinf)==1)
		  			{
		  				//set qty to one 
		  				array_unshift($tpvinf,1.0);
		  			}
		  			//if more thant 1, qty first,price second
		  			
		  			$inserts[]="(?,?,?,?,?,?)";
		  			$data[]=$pid;
		  			//if all , set all_groups flag
		  			$data[]=(isset($cgid)?0:1);
		  			$data[]=$cgid;
		  			$data[]=$tpvinf[0];
		  			$data[]=$tpvinf[1];
		  			$data[]=$wsid;
		  		}
		  	}
		  }
		  if(count($inserts)>0)
		  {
		  	$sql.=implode(",",$inserts);
		  	$sql.=" ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)";
		  	$this->insert($sql,$data);
		  }
		 }
		return true;
	}

	public function processColumnList(&$cols)
	{
		//inspect column list for getting tier price columns info
		foreach($cols as $col)
		{
			if(preg_match("|tier_price:(.*)|",$col,$matches))
			{
				$tpinf=array("name"=>$matches[1],"id"=>null);
				//if specific tier price 
		 		 if($tpinf["name"]!=="_all_")
		 		 {
		  			//get tier price customer group id
		  			$sql="SELECT customer_group_id from ".$this->tablename("customer_group")." WHERE customer_group_code=?";
	 				$cgid=$this->selectone($sql,$tpinf["name"],"customer_group_id");
	 				$tpinf["id"]=$cgid;
		  		}
		  		else
		  		{
		  			$tpinf["id"]=null;
		  		}
		  		$this->_tpcol[$col]=$tpinf;
			}
		}
		return true;
	}

	public function initialize($params)
	{
	}
}

