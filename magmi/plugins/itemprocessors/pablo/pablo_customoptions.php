<?php
/**
 * Class SampleItemProcessor
 * @author dweeves
 *
 * This class is a sample for item processing
 */
class CustomOptionsItemProcessor extends Magmi_ItemProcessor
{
	private $_containerMap=array("Product Info Column"=>"container1",
								 "Block after Info Column"=>"container2");
	
	protected $_optids=array();
	protected $_opttypeids=array();
	
	public function getPluginInfo()
	{
		return array(
            "name" => "Custom Options",
            "author" => "Pablo & Dweeves",
            "version" => "0.0.4"
            );
	}

	public function processItemBeforeId(&$item,$params=null)
	{
		return true;
	}

	public function getOptId($field)
	{
		return $this->_optids[$field];
	}
	
	public function setOptId($field,$val)
	{
		$this->_optids[$field]=$val;
	}
	
	public function getOptTypeIds($field)
	{
		return $this->_opttypeids[$field];
	}
	
	public function setOptTypeIds($field,$arr)
	{
		$this->_opttypeids[$field]=$arr;
		
	}
	public function createOption($pid,$sids,$opt)
	{
		$t1 = $this->tablename('catalog_product_option');
		$t2 = $this->tablename('catalog_product_option_title');
		$t3 = $this->tablename('catalog_product_option_price');
		$values = array($pid, $opt['type'], $opt['is_require']);
		$f="product_id, type, is_require";
		$i="?,?,?";

		$mx=isset($opt["max_characters"]);
		if($mx)
		{
			$values[]=$opt["max_characters"];
			$i.=",?";
			$f.=",max_characters";
		}

		$optionId=$this->getOptId($opt['__field']);
		if(!isset($optionId))
		{
			$sql = "INSERT INTO $t1 ($f) VALUES ($i)";
			$optionId = $this->insert($sql, $values);
			$this->setOptId($opt['__field'],$optionId);
		}
		$tvals=array();
		$tins=array();
		$pvals=array();
		$pins=array();

		foreach($sids as $sid)
		{
			$tins[]="(?,?,?)";
			$tvals[]=$optionId;
			$tvals[]=$sid;
			$tvals[]=$opt["title"];
			$pins[]="(?,?,?,?)";
			$pvals[]=$optionId;
			$pvals[]=$sid;
			$pvals[]=$opt["price"];
			$pvals[]=$opt["price_type"];
			//here we set admin values, so no more value needed since all other stores will share it
			if($sid==0)
			{
				break;
			}
		}

		$sql="INSERT IGNORE INTO $t2 (option_id, store_id, title) VALUES ".implode(",",$tins);
		$this->insert($sql,$tvals);

		if(isset($opt['price']))
		{
			$sql = "INSERT IGNORE INTO $t3 (option_id, store_id, price, price_type) VALUES ".implode(",",$pins);
			$this->insert($sql,$pvals);
		}
		return $optionId;
	}

	public function createOptionValues($field,$sids,$valarr)
	{
		if(!isset($valarr) ||count($valarr)==0)
		{
			return;
		}
		$t4 = $this->tablename('catalog_product_option_type_value');
		$t5 = $this->tablename('catalog_product_option_type_title');
		$t6 = $this->tablename('catalog_product_option_type_price');

		$ttvals=array();
		$ttins=array();
		$tpvals=array();
		$tpins=array();
		$optid=$this->getOptId($field);
		
		$optionTypeIds=$this->getOptTypeIds($field);
		$optionTypeId=null;
		for($i=0;$i<count($valarr);$i++)
		{
			$val=$valarr[$i];
			if($i<count($optionTypeIds))
			{
				$optionTypeId=$optionTypeIds[$i];
			}
			else
			{
				$sql = "INSERT INTO $t4
       	             (option_id, sku, sort_order)
      	             VALUES (?, ?, ?)";
				$optionTypeId = $this->insert($sql, array($optid,$val["sku"],$val["sort_order"]));
				$optionTypeIds[]=$optionTypeId;
			}
			foreach($sids as $sid)
			{
				$ttins[]="(?,?,?)";
				$ttvals[]=$optionTypeId;
				$ttvals[]=$sid;
				$ttvals[]=$val["title"];
				$tpins[]="(?,?,?,?)";
				$tpvals[]=$optionTypeId;
				$tpvals[]=$sid;
				$tpvals[]=$val["price"];
				$tpvals[]=$val["price_type"];
				//here we set admin values, so no more value needed since all other stores will share it
				if($sid==0)
				{
					break;
				}
			}
		}

		$this->setOptTypeIds($field,$optionTypeIds);
		
		$sql="INSERT IGNORE INTO $t5 (option_type_id, store_id, title) VALUES ".implode(",",$ttins);
		$this->insert($sql,$ttvals);

		$sql = "INSERT IGNORE INTO $t6 (option_type_id, store_id, price, price_type) VALUES ".implode(",",$tpins);
		$this->insert($sql,$tpvals);


	}

	public function BuildCustomOption($field,$value)
	{
		$fieldParts=explode(":",$field);
		$sort_order=0;
		$title = $fieldParts[0];
		$type = $fieldParts[1];
		$is_required = $fieldParts[2];
		if(isset($fieldParts[3])) {
			$sort_order = $fieldParts[3];
		}
		//@list($title,$type,$is_required,$sort_order) = $fieldParts;
		$title = ucfirst(str_replace('_',' ',$title));
		$opt = array(
			 '__field'=>$field,
             'is_delete'=>0,
             'title'=>$title,
             'previous_group'=>'',
             'previous_type'=>'',
             'type'=>$type,
             'is_require'=>$is_required,
             'sort_order'=>$sort_order,
              'values'=>array()
		);

		$values = explode('|',$value);
		
	
		foreach($values as $v)
		{
			$ovalues=array();
			$parts = explode(':',$v);
			
			if(count($parts)>1 && $v==$values[0])
			{
				$opt["title"]=$parts[0];
				$vtitle=array_pop($parts);
			}
			else
			{
				$vtitle=$parts[0];
			}
			
			$ovalues["title"]=$vtitle;
			$c=count($parts);
			$price_type=($c>1)?$parts[1]:'fixed';
			$price=($c>2)?$parts[2]:0;
			$sku=($c>3)?$parts[3]:'';
			$sort_order=($c>4)?$parts[4]:0;
			
			switch($type) {
				
				case 'file':
					/* TODO */
					break;
				case 'field':
				case 'area':
					$opt['max_characters'] = $sort_order;
				case 'date':
				case 'date_time':
				case 'time':
					$opt['price_type'] = $price_type;
					$opt['price'] = $price;
					$opt['sku'] = $sku;
					break;
					/* NO BREAK */
				case 'drop_down':
				case 'radio':
				case 'checkbox':
				case 'multiple':
				default:
					$ovalues["price_type"]=$price_type;
					$ovalues["price"]=$price;
					$ovalues["sku"]=$sku;
					$ovalues["sort_order"]=$sort_order;
					$opt['values'][]=$ovalues;
			}
		}
		return $opt;

	}

	public function processItemAfterId(&$item,$params=null)
	{
		$hasOptions = 0;
		$requiredOptions = 0;
		$custom_options = array();
		$itemCopy = $item;

		foreach($itemCopy as $field=>$value)
		{
			$fieldParts = explode(':', $field);
			if(count($fieldParts)>2 && $value)
			{
				$custom_options[]=$this->BuildCustomOption($field,$value);
				unset($item[$field]);
			}
			unset($fieldParts);
		}


		// create new custom options
		if(count($custom_options)>0)
		{
			$pid = $params['product_id'];
			$tname=$this->tablename('catalog_product_entity');
			foreach($custom_options as $opt)
			{
				if($opt["is_require"])
				{
					$requiredOptions=1;
					break;
				}
			}
			$data = array(1, $requiredOptions, $pid);
			//set product has having options
			$sql="UPDATE `$tname` SET has_options=?,required_options=? WHERE entity_id=?";
			$this->update($sql,$data);
			$t1 = $this->tablename('catalog_product_option');
			//destroy existing options if first time we encounter item
			if(!$params["same"] )
			{
				$sql = "DELETE $t1 FROM $t1 WHERE $t1.product_id=$pid";
				$this->delete($sql);
			}
			//check options container
			$oc=isset($item['options_container'])?$item['options_container']:"container2";
			if(!in_array($oc,array('container1','container2')))
			{
				$item['options_container'] = $this->_containerMap[$oc];
			}
			else
			{
				$item['options_container']=$oc;
			}
			//fill custom options table
			$sids=$this->getItemStoreIds($item,0);
			if(!$params["same"])
			{
				$sids=array_unique(array_merge(array(0),$sids));
			}
			
			foreach($custom_options as $option)
			{
				$opt=$this->createOption($pid,$sids,$option);
				$this->createOptionValues($option['__field'],$sids,$option["values"]);
			}
				
		}
		unset($custom_options);
		return true;
	}

	/*
	 public function processItemException(&$item,$params=null)
	 {

	 }*/

	public function initialize($params)
	{
		return true;
	}

	public function processColumnList(&$cols,$params=null)
	{
		//detect if we have at least one custom option
		$hasopt=false;
		foreach($cols as $k)
		{
			$hasopt=count(explode(":",$k))>1;
			if($hasopt)
			{
				break;
			}
		}
		//if we have at least one custom option, add options_container if not exist
		if($hasopt && !in_array('options_container', $cols)) {
			$cols[] = 'options_container';
		}
		return true;
	}
}