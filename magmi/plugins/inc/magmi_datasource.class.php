<?php
/**
 * Class Magmi_Datasource
 * @author dweeves
 *
 * This class enables to perform input format support for ingested data
 */ 
 require_once("magmi_plugin.php");
abstract class Magmi_DataSource extends Magmi_Plugin
{
	
	public function getColumnNames()
	{
		
	}
	public function getRecordsCount()
	{
		
	}
	
	public function beforeImport()
	{
		
	}
	
	public function afterImport()
	{
		
	}
	
	public function getNextRecord()
	{
		
	}
	public function getOptionsPanel()
	{
		return "options_panel.php";
	}
}