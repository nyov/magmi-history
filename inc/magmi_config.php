<?php
require_once("properties.php");
class Magmi_Config extends Properties
{
	private static $_instance=null;
	private $_configname=null;
	private $_defaultconfigname=null;
	
	public function __construct()
	{
		$this->_configname=dirname(__FILE__)."/../conf/magmi.ini";
		$this->_defaultconfigname=dirname(__FILE__)."/../conf/magmi.default.ini";
	}
	
	public function getConfigFilename()
	{
		return $this->_configname;	
	}
	
	public static function getInstance()
	{
		if(self::$_instance==null)
		{
			self::$_instance=new Magmi_Config();
		}
		return self::$_instance;
	}
	
	public function isDefault()
	{
		return !file_exists($this->_conffile);	
	}
	
	public function load()
	{
		parent::load($this->_configname);
		return $this;
	}
	
	public function loadDefault()
	{
		$this->load($this->_defaultconfigname);
	}
	
	public function isPluginEnabled($type,$classname)
	{
		return in_array($classname,explode(",",$this->get["PLUGINS:$type"]));
	}
}