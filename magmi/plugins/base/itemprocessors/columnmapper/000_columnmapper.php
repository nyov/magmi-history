<?php
/**
 * Class SampleItemProcessor
 * @author dweeves
 *
 * This class is a sample for item processing   
*/ 
class ColumnMappingItemProcessor extends Magmi_ItemProcessor
{

	protected $_dcols=array();
	
    public function getPluginInfo()
    {
        return array(
            "name" => "Column mapper",
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
	
	public function processColumnList(&$cols,$params=null)
	{
		for($i=0;$i<count($cols);$i++)
		{
			$cname=$cols[$i];
			if(isset($this->_dcols[$cname]))
			{
				$cols[$i]=$this->_dcols[$cname];
				$this->log("Replacing Column $cname by ".$cols[$i],"startup");
			}
		}
		return true;
	}
	
	public function processItemBeforeId(&$item,$params)
	{
		foreach($this->_dcols as $oname=>$mname)
		{
			if(isset($item[$oname]))
			{
				$item[$mname]=$item[$oname];
				unset($item[$oname]);
			}
		}
		return true;
	}
	
	public function initialize($params)
	{
		foreach($params as $k=>$v)
		{
			if(preg_match_all("/^CMAP:(.*)$/",$k,$m) && $k!="CMAP:columnlist")
			{
				$this->_dcols[$m[1][0]]=$params[$k];
			}
		}
	}
	
	public function getPluginParams($params)
	{
		$pp=array();
		foreach($params as $k=>$v)
		{
			if(preg_match("/^CMAP:.*$/",$k))
			{
				$pp[$k]=$v;
			}
		}	
		return $pp;
	}	
}