<?php
require_once("magmi_csvreader.php");


class Magmi_CSVDataSource extends Magmi_Datasource
{
	protected $_csvreader;
	
	public function initialize($params)
	{
		$this->_csvreader=new Magmi_CSVReader();
		$this->_csvreader->bind($this);
		$this->_csvreader->initialize();
		
	}
	
	public function getAbsPath($path)
	{
		
		return abspath($path,$this->getScanDir());
		
	}
	
	public function getScanDir($resolve=true)
	{
		$scandir=$this->getParam("CSV:basedir","var/import");
		if(!isabspath($scandir))
		{
			$scandir=abspath($scandir,Magmi_Config::getInstance()->getMagentoDir(),$resolve);
		}
		return $scandir;	
	}
	
	public function getCSVList()
	{
		$scandir=$this->getScanDir();
		$files=glob("$scandir/*.csv");
		return $files;
	}
	
	public function getPluginParamNames()
	{
		return array('CSV:filename','CSV:enclosure','CSV:separator','CSV:basedir','CSV:headerline','CSV:noheader','CSV:allowtrunc');
	}
	
	public function getPluginInfo()
	{
		return array("name"=>"CSV Datasource",
					 "author"=>"Dweeves",
					 "version"=>"1.1.4");
	}
	
	public function getRecordsCount()
	{
		return $this->_csvreader->getLinesCount();
	}
	
	public function getAttributeList()
	{
		
	}
	
	public function beforeImport()
	{
		return $this->_csvreader->checkCSV();
	}
	
	public function afterImport()
	{
		
	}
	
	public function startImport()
	{
		$this->_csvreader->openCSV();
	}
	
	public function getColumnNames($prescan=false)
	{
		return $this->_csvreader->getColumnNames($prescan);
	}
	
	public function endImport()
	{
		$this->_csvreader->closeCSV();
	}

	
	public function getNextRecord()
	{
		return $this->_csvreader->getNextRecord();
	}
	

}