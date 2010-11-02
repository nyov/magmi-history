<?php
/**
 * Class SampleItemProcessor
 * @author dweeves
 *
 * This class is a sample for item processing
 */
class GenericMapperProcessor extends Magmi_ItemProcessor
{
	protected $_mapping;

	public function getPluginInfo()
	{
		return array(
            "name" => "Generic mapper",
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
		foreach(array_keys($item) as $k)
		{
			if(isset($this->_mapping["$k.csv"]))
			{
				if(isset($this->_mapping["$k.csv"][$item[$k]]))
				{
					$item[$k]=$this->_mapping["$k.csv"][$item[$k]];
				}
			}
		}
		return true;
	}

	public function processItemAfterId(&$item,$params=null)
	{
		return true;
	}


	public function initialize($params)
	{
		$this->_mapping=array();
		$flist=glob(dirname(__file__)."/mappings/*.csv");
		foreach($flist as $fname)
		{
			$idx=basename($fname);
			$this->_mapping[$idx]=array();
			$mf=fopen("$fname","r");
			while (($data = fgetcsv($mf, 1000, ",")) !== FALSE)
			{
				$this->_mapping[$idx][$data[0]]=$data[1];
			}
		}
	}
}

