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
		$this->_defaultconfigname=dirname(__FILE__)."/../conf/magmi.ini.default";
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
		return !file_exists($this->_configname);	
	}
	
	public function load()
	{
		$conf=(!$this->isDefault())?$this->_configname:$this->_defaultconfigname;
		parent::load($conf);
		return $this;
	}
	
	public function loadDefault()
	{
		$this->load($this->_defaultconfigname);
	}
	
	public function getEnabledPluginClasses($type)
	{	
		$type=strtoupper($type);
		if($type=="DATASOURCES")
		{
			return array($this->get("PLUGINS_$type","class"));
		}
		else
		{
			$v=explode(",",$this->get("PLUGINS_$type","classes",""));
			if(count($v)==1 && $v[0]=="")
			{
				return array();
			}
			return $v;
		}
	}
	
	public function isPluginEnabled($type,$pclass)
	{
		return in_array($pclass,$this->getEnabledPluginClasses($type));
	}
	
	public function save($arr)
	{
		foreach($arr as $k=>$v)
		{
			if(!preg_match("/\w+:\w+/",$k))
			{
				unset($arr[$k]);
			}
		}
		$this->setPropsFromFlatArray($arr);
		parent::save($this->_configname);
	}
	
}