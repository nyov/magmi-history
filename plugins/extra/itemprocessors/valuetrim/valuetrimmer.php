<?php
/**
 * Class SampleItemProcessor
 * @author dweeves
 *
 * This class is a sample for item processing   
*/ 
class ValueTrimItemProcessor extends Magmi_ItemProcessor
{

    public function getPluginInfo()
    {
        return array(
            "name" => "Value Trimmer for select/multiselect",
            "author" => "Dweeves",
            "version" => "0.0.2"
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
	
	
	public function processItemBeforeId(&$item,$params=null)
	{
		//return true , enable item processing
		foreach(array_keys($item) as $col)
		{
			$ainfo=$this->getAttrInfo($col);
			if(count($ainfo)>0)
			{
				if($ainfo["frontend_input"]=="select" || $ainfo["frontend_input"]=="multiselect")
				{
					$item[$col]=trim($item[$col]);
				}
			}
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
	
}