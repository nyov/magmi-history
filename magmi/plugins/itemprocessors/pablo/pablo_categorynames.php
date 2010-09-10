<?php
/**
 * Class SampleItemProcessor
 * @author dweeves
 *
 * This class is a sample for item processing
*/
class CategoryNamesItemProcessor extends Magmi_ItemProcessor
{
    protected $_categories;

	public function beforeImport()
	{
        $t1 = $this->tablename("catalog_category_entity_varchar");
        $t2 = $this->tablename("eav_attribute");

        $sql = "Select entity_id, value FROM $t1
                JOIN $t2 ON $t2.attribute_id=$t1.attribute_id
                AND $t2.entity_type_id=$t1.entity_type_id
                AND $t2.attribute_code='name'";
        $result=$this->selectAll($sql);
        foreach($result as $r)
        {
            $this->_categories[$r['value']] = $r['entity_id'];
        }
		return true;
	}

	public function afterImport()
	{
		return true;
	}

	public function processItemBeforeId(&$item,$params=null)
	{
		return true;
	}

	public function processItemAfterId(&$item,$params=null)
	{
		return true;
	}

	/*
	public function processItemException(&$item,$params=null)
	{

	}*/

	public function initialize($params)
	{

	}

	public function processColumnList(&$cols,$params=null)
	{

	}
}