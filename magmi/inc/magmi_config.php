<?php
class Magmi_Config extends Properties
{
	private static $_instance=null;
	private $_configname=null;
	private $_defaultconfigname=null;
	
	private function __construct()
	{
		$this->_configname=dirname(__FILE__)."/../conf/magmi.conf";
		$this->_defaultconfigname=dirname(__FILE__)."/../conf/magmi.default.conf";
	}
	
	public static getInstance()
	{
		if($_instance==null)
		{
			$_instance=new Magmi_Config();
		}
		return $_instance;
	}
	
	public function isDefault()
	{
		return !file_exists($this->_conffile);	
	}
	
	public function load()
	{
		$this->load($this->_configname);
	}
	
	public function loadDefault()
	{
		$this->load($this->_defaultconfigname);
		
	}
}