<?php
/**
 * Class SampleItemProcessor
 * @author dweeves
 *
 * This class is a sample for item processing   
*/ 
class DefaultValuesItemProcessor extends Magmi_ItemProcessor
{

	protected $_dset;
	protected $_dcols=array();
	public function __construct()
	{
		$conf=Magmi_Config::getInstance();
		$this->_dset=$conf->getsection("DEFAULT");
	}
	
    public function getPluginInfo()
    {
        return array(
            "name" => "Default Values setter",
            "author" => "Dweeves",
            "version" => "0.0.1"
        );
    }
	
	/**
	 * you can add/remove columns for the item passed since it is passed by reference
	 * @param MagentoMassImporter $mmi : reference to mass importer (convenient to perform database operations)
	 * @param unknown_type $item : modifiable reference to item before import
	 * the $item is a key/value array with column names as keys and values as read from csv file.
	 * @return bool : 
	 * 		true if you want the item to be imported after your custom processing
	 * 		false if you want to skip item import after your processing
	 */
	
	
	public function processItemBeforeId(&$item,$params=null)
	{
		foreach($this->_dcols as $col)
		{
			$item[$col]=$this->_dset[$col];
		}
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
		return true;
	}
	
	public function processColumnList(&$cols,$params=null)
	{
		$dcols=array_diff(array_keys($this->_dset),array_intersect($cols,array_keys($this->_dset)));
		foreach($dcols as $col)
		{
			if(!empty($this->_dset[$col]))
			{
				$cols[]=$col;
				$this->_dcols[]=$col;
			}
		}
	}
}