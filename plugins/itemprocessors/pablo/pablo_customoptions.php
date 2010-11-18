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
	public function getPluginInfo()
	{
		return array(
            "name" => "Custom Options",
            "author" => "Pablo & Dweeves",
            "version" => "0.0.3"
            );
	}

	public function processItemBeforeId(&$item,$params=null)
	{
		return true;
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
				$sort_order=0;
					
				unset($item[$field]);
				$hasOptions = 1;
				$title = $fieldParts[0];
				$type = $fieldParts[1];
				$is_required = $fieldParts[2];
				if($is_required) {
					$requiredOptions = 1;
				}
				if(isset($fieldParts[3])) {
					$sort_order = $fieldParts[3];
				}
				//@list($title,$type,$is_required,$sort_order) = $fieldParts;
				$title = ucfirst(str_replace('_',' ',$title));
				$opt = array(
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
					$parts = explode(':',$v);
					$c=count($parts);
					$title = $parts[0];
					$price_type=($c>1)?$parts[1]:'fixed';
					$price=($c>2)?$parts[2]:0;
					$sku=($c>3)?$parts[3]:'';
					$sort_order=($c>4)?$parts[4]:0;
					switch($type) {
						case 'file':
							/* TODO */
							break;

				

						case 'date':
						case 'date_time':
						case 'time':
							$opt['price_type'] = $price_type;
							$opt['price'] = $price;
							$opt['sku'] = $sku;
							break;
						case 'field':
						case 'area':
							$opt['max_characters'] = $sort_order;
							/* NO BREAK */
						case 'drop_down':
						case 'radio':
						case 'checkbox':
						case 'multiple':
						default:
							$opt['values'][]=array(
                                'is_delete'=>0,
                                'title'=>$title,
                                'option_type_id'=>-1,
                                'price_type'=>$price_type,
                                'price'=>$price,
                                'sku'=>$sku,
                                'sort_order'=>$sort_order,
							);
							break;
					}
				}
				$custom_options[]=$opt;
			}
		}


		// create new custom options
		if(count($custom_options)>0) {
				
			$pid = $params['product_id'];
			$tname=$this->tablename('catalog_product_entity');
			$data = array($hasOptions, $requiredOptions, $pid);
			$sql="UPDATE `$tname` SET has_options=?,required_options=? WHERE entity_id=?";
			$this->update($sql,$data);

			$t1 = $this->tablename('catalog_product_option');
			$t2 = $this->tablename('catalog_product_option_title');
			$t3 = $this->tablename('catalog_product_option_price');
			$t4 = $this->tablename('catalog_product_option_type_value');
			$t5 = $this->tablename('catalog_product_option_type_title');
			$t6 = $this->tablename('catalog_product_option_type_price');

			// delete old custom options
			if(!$params["same"] ) {
				$sql = "DELETE $t1 FROM $t1 WHERE $t1.product_id=$pid";
				$this->delete($sql);
			}
				
			$oc=isset($item['options_container'])?$item['options_container']:"container2";
			if(!in_array($oc,array('container1','container2')))
			{
				$item['options_container'] = $this->_containerMap[$oc];
			}
			
			$sids=$this->getItemStoreIds($item,0);
			if(!$params["same"])
			{
				$sids=array_unique(array_merge(array(0),$sids));
			}	
			
			foreach($custom_options as $option) {
				$values = array($pid, $option['type'], $option['is_require']);
				$f="product_id, type, is_require";
				$i="?,?,?";
				
				$mx=isset($option["max_characters"]);
				if($mx)
				{
					$values[]=$option["max_characters"];
					$i.=",?";
					$f.=",max_characters";
				}
				
				$sql = "INSERT INTO $t1 ($f) VALUES ($i)";
				$optionId = $this->insert($sql, $values);
				
				if(count($option['values'])>0)
				{
					$optionTitleSql = "INSERT IGNORE INTO $t2 (option_id, store_id, title) VALUES ";
					$optionTitleValues = array();

					$optionPriceSql = "INSERT IGNORE INTO $t3 (option_id, store_id, price, price_type) VALUES ";
					$optionPriceValues = array();
					
					$optionTypeTitleSql = "INSERT INTO $t5 (option_type_id, store_id, title) VALUES ";
					$optionTypeTitleValues = array();
					
					$optionTypePriceSql = "INSERT INTO $t6 (option_type_id, store_id, price, price_type) VALUES ";
					$optionTypePriceValues = array();
				}			
				
				foreach($option['values'] as $val) 
				{
					$values = array($optionId, $val['sku'], $val['sort_order']);
					$sql = "INSERT INTO $t4
               	             (option_id, sku, sort_order)
               	             VALUES (?, ?, ?)";
					$optionTypeId = $this->insert($sql, $values);
					
					
					foreach($sids as $sid)
					{
						$optionTitleSql = $optionTitleSql."(?, ?, ?),";
						$optionTitleValues[] = $optionId;
						$optionTitleValues[] = $sid;
						
						$optionTitleValues[] = ($sid!=0?$val['title']:$option['title']);

						
						$optionPriceSql = $optionPriceSql."(?, ?, ?,?),";
						$optionPriceValues[] = $optionId;
						$optionPriceValues[] = $sid;
						$optionPriceValues[] = ($sid!=0?$val['price']:$option['price']);
						$optionPriceValues[] = ($sid!=0?$val['price_type']:$option['price_type']);				
										
						$optionTypePriceSql = $optionTypePriceSql."(?, ?, ?, ?),";
						$optionTypePriceValues[] = $optionTypeId;
						$optionTypePriceValues[] = $sid;
						$optionTypePriceValues[] = ($sid!=0?$val['price']:$option['price']);
						$optionTypePriceValues[] = ($sid!=0?$val['price_type']:$option['price_type']);				
							
						$optionTypeTitleSql = $optionTypeTitleSql."(?, ?, ?),";
						$optionTypeTitleValues[] = $optionTypeId;
						$optionTypeTitleValues[] = $sid;
						$optionTypeTitleValues[] = ($sid!=0?$val['title']:$option['title']);
	
					}
				}
			}

			if(count($option['values'])>0)
			{
				$optionTitleSql = rtrim($optionTitleSql, ',');
				$this->insert($optionTitleSql, $optionTitleValues);

				$optionTypeTitleSql = rtrim($optionTypeTitleSql, ',');
				$this->insert($optionTypeTitleSql, $optionTypeTitleValues);
				
				$optionPriceSql = rtrim($optionPriceSql, ',');
				$this->insert($optionPriceSql, $optionPriceValues);
				
				$optionTypePriceSql = rtrim($optionTypePriceSql, ',');
				$this->insert($optionTypePriceSql, $optionTypePriceValues);
			}
		}
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