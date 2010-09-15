<?php
class CSVException extends Exception
{
	
}

class Magmi_CSVDataSource extends Magmi_Datasource
{
	protected $_filename;
	protected $_fh;
	protected $_cols;
	protected $_csep;
	
	public function initialize($params)
	{
		$this->_filename=$params["filename"];	
		if(!isset($this->_filename))
		{
			throw new CSVException("No csv file set");
		}
		if(!file_exists($this->_filename))
		{
			throw new CSVException("{$this->_filename} not found");
		}
		$this->_csep=$this->getParam("csv_separator",",");
	}
	
	public function getPluginInfo()
	{
		return array("name"=>"CSV Datasource",
					 "author"=>"Dweeves",
					 "version"=>"1.0");
	}
	
	public function getRecordsCount()
	{
		//open csv file
		$f=fopen($this->_filename,"rb");
		//get records count
		$count=-1;
		while(fgetcsv($f,4096,$this->_csep))
		{
			$count++;
		}
		fclose($f);
		return $count;
	}
	
	public function getAttributeList()
	{
		
	}
	
	public function beforeImport()
	{
		
	}
	
	public function afterImport()
	{
		
	}
	
	public function startImport()
	{
		//open csv file
		$this->_fh=fopen($this->_filename,"rb");
	}
	
	public function getColumnNames()
	{
		$this->_cols=fgetcsv($this->_fh,4096,$this->_csep,'"');
		return $this->_cols;
	}
	
	public function endImport()
	{
		fclose($this->_fh);	
	}
	
	public function getNextRecord()
	{
		$row=fgetcsv($this->_fh,4096,$this->_csep,'"');
		if($row===false)
		{
			return false;
		}
		//create product attributes values array indexed by attribute code
		$record=array_combine($this->_cols,$row);
		return $record;
	}
	

}