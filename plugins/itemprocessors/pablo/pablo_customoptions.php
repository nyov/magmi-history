<?php
/**
 * Class SampleItemProcessor
 * @author dweeves
 *
 * This class is a sample for item processing
*/
class CustomOptionsItemProcessor extends Magmi_ItemProcessor
{

    public function getPluginInfo()
    {
        return array(
            "name" => "Custom Options",
            "author" => "Pablo",
            "version" => "0.0.1"
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
        foreach($itemCopy as $field=>$value) {
            $fieldParts = explode(':', $field);
            if(count($fieldParts)>2 && $value) {
                unset($item[$field]);
                $hasOptions = 1;
                if(isset($fieldParts[0])) {
                    $title = $fieldParts[0];
                }
                if(isset($fieldParts[1])) {
                    $type = $fieldParts[1];
                }
                if(isset($fieldParts[2])) {
                    $is_required = $fieldParts[2];
                    if($is_required) {
                        $requiredOptions = 1;
                    }
                }
                if(isset($fieldParts[3])) {
                    $sort_order = $fieldParts[3];
                }
                //@list($title,$type,$is_required,$sort_order) = $fieldParts;
                $title = ucfirst(str_replace('_',' ',$title));
                $custom_options[] = array(
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
                foreach($values as $v) {
                    $parts = explode(':',$v);
                    $title = $parts[0];
                    if(count($parts)>1) {
                        $price_type = $parts[1];
                    } else {
                        $price_type = 'fixed';
                    }
                    if(count($parts)>2) {
                        $price = $parts[2];
                    } else {
                        $price =0;
                    }
                    if(count($parts)>3) {
                        $sku = $parts[3];
                    } else {
                        $sku='';
                    }
                    if(count($parts)>4) {
                        $sort_order = $parts[4];
                    } else {
                        $sort_order = 0;
                    }
                    switch($type) {
                        case 'file':
                           /* TODO */
                           break;

                        case 'field':
                        case 'area':
                           $custom_options[count($custom_options) - 1]['max_characters'] = $sort_order;
                           /* NO BREAK */

                        case 'date':
                        case 'date_time':
                        case 'time':
                           $custom_options[count($custom_options) - 1]['price_type'] = $price_type;
                           $custom_options[count($custom_options) - 1]['price'] = $price;
                           $custom_options[count($custom_options) - 1]['sku'] = $sku;
                           break;

                        case 'drop_down':
                        case 'radio':
                        case 'checkbox':
                        case 'multiple':
                        default:
                            $custom_options[count($custom_options) - 1]['values'][]=array(
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
            }
        }

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
        if(!$params['new_product']) {
            $sql = "DELETE $t1 FROM $t1 WHERE $t1.product_id=$pid";
            $this->delete($sql);
        }

        // create new custom options
        if(count($custom_options)>0) {
            //$item['options_container'] = 'Block after Info Column';


            $optionTitleSql = "INSERT INTO $t2 (option_id, store_id, title) VALUES ";
            $optionTitleValues = array();

            $optionTypeTitleSql = "INSERT INTO $t5 (option_type_id, store_id, title) VALUES ";
            $optionTypeTitleValues = array();

            $optionTypePriceSql = "INSERT INTO $t6 (option_type_id, store_id, price, price_type) VALUES ";
            $optionTypePriceValues = array();

            foreach($custom_options as $option) {
                $values = array($pid, $option['type'], $option['is_require']);
                $sql = "INSERT INTO $t1
                        (product_id, type, is_require)
                        VALUES (?, ?, ?)";
                $optionId = $this->insert($sql, $values);

                $optionTitleSql = $optionTitleSql."(?, ?, ?),";
                $optionTitleValues[] = $optionId;
                $optionTitleValues[] = 0;
                $optionTitleValues[] = $option['title'];

                foreach($option['values'] as $val) {
                    $values = array($optionId, $val['sku'], $val['sort_order']);
                    $sql = "INSERT INTO $t4
                            (option_id, sku, sort_order)
                            VALUES (?, ?, ?)";
                    $optionTypeId = $this->insert($sql, $values);

                    $optionTypeTitleSql = $optionTypeTitleSql."(?, ?, ?),";
                    $optionTypeTitleValues[] = $optionTypeId;
                    $optionTypeTitleValues[] = 0;
                    $optionTypeTitleValues[] = $val['title'];

                    $optionTypePriceSql = $optionTypePriceSql."(?, ?, ?, ?),";
                    $optionTypePriceValues[] = $optionTypeId;
                    $optionTypePriceValues[] = 0;
                    $optionTypePriceValues[] = $val['price'];
                    $optionTypePriceValues[] = $val['price_type'];
                }
            }

            $optionTitleSql = rtrim($optionTitleSql, ',');
            $this->insert($optionTitleSql, $optionTitleValues);

            $optionTypeTitleSql = rtrim($optionTypeTitleSql, ',');
            $this->insert($optionTypeTitleSql, $optionTypeTitleValues);

            $optionTypePriceSql = rtrim($optionTypePriceSql, ',');
            $this->insert($optionTypePriceSql, $optionTypePriceValues);
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
        $cols=array_unique(array_merge($cols, array('has_options', 'is_require', 'options_container')));
	}
}