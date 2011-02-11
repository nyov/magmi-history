<?php
require_once("dbhelper.class.php");

class MesSouliersDatasource extends Magmi_Datasource
{

	private $_imptype;
	private $_request;
	public $stmt;
	
	public function initialize($params)
 	{
 		$this->_imptype=$this->getParam("MS:imptype");
 		if($this->_imptype="configurable")
		{
			$expfile=dirname(__FILE__).DIRECTORY_SEPARATOR."exp_configurables.sql";
		}
		if($this->_imptype="simple")
		{
			$expfile=dirname(__FILE__).DIRECTORY_SEPARATOR."exp_configurables.sql";
		}
		$this->_request=file_get_contents($expfile);
 		$this->stmt=null;	
 	}
 	
	public function getPluginInfo()
	{
		return array("name"=>"Messouliers Datasource",
					 "author"=>"Softarch Consulting",
					 "version"=>"1.0");
	}
	
	public function getPluginParamNames()
	{
		return array('MS:imptype');
	}
	
 	 	
 	public function ingest_files()
 	{
 		$init_tables=dirname(__FILE__).DIRECTORY_SEPARATOR."init_tables.sql";
 		$sqltpl=file_get_contents($init_tables);
 		$lcvfile=realpath(dirname(__FILE__).DIRECTORY_SEPARATOR."ms_simple.csv");
 		$meviafile=realpath(dirname(__FILE__).DIRECTORY_SEPARATOR."ms_configurable.csv");
 		$sql=str_replace("[[lcv_file]]",$lcvfile,$sqltpl);
 		$sql=str_replace("[[mevia_file]]",$meviafile,$sql);
 		$stmts=array();
 		$sqllines=explode("--",$sql);
 		foreach($sqllines as $sqlline)
 		{
 			if($sqlline!="")
 			{
 				$subs=explode(";\n","--".$sqlline);
 				foreach($subs as $sub)
 				{
 					
 					if(trim($sub)!="" && substr($sub,0,2)!="--")
 					{
 						$stmts[]=$sub;
 					}
 				}
 			}	
 		}
 		foreach($stmts as $stmt)
 		{
 			$this->exec_stmt($stmt);
 		}
	}
 	
	public function getNextRecord()
	{
		if(!isset($this->stmt))
		{
			$this->stmt=$this->select($this->extractsql);
		}
		$data=$this->stmt->fetch();
		if(!$data)
		{
			return false;
		}
		return $data;
	}
		public function getColumnNames()
		{
			$s=$this->select($this->_request);
			$test=$s->fetch();
			$s->closeCursor();
			unset($s);
			$cl=array_keys($test);
			return $cl;
		}

		public function getRecordsCount()
		{
			$sql="SELECT COUNT(*) as cnt FROM (".str_replace("\n"," ",$this->extractsql).") as t1";
			$cnt=$this->selectone($sql,null,"cnt");	
			return $cnt;
		} 	
		
		
		public function startImport()
		{
			
		}
		
		public function endImport()
		{
			
		}
}