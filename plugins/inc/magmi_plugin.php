<?php
abstract class Magmi_Plugin
{
	protected $_mmi=null;
	protected $_baseclass;
	
	public function getParam($pname,$default)
	{
		return isset($this->_params[$pname])?$this->_params[$pname]:$default;
		
	}
	
	public function getPluginInfo()
	{
		return array("name"=>$this->getPluginName(),
					 "version"=>$this->getPluginVersion(),
					 "author"=>$this->getPluginAuthor(),
					 "url"=>$this->getPluginUrl());		
	}
	
	public function getPluginUrl()
	{
		return null;
	}
	
	public function getPluginVersion()
	{
		return null;		
	}
	
	public function getPluginName()
	{
		return null;
	}
	
	public function getPluginAuthor()
	{
		return null;
	}
	
	public function pluginHello()
	{
		$info=$this->getPluginInfo();
		$hello=array(isset($info["name"])?"":$info["name"]);
		$hello[]=!isset($info["version"])?"":$info["version"];
		$hello[]=!isset($info["author"])?"":$info["author"];
		$hello[]=!isset($info["url"])?"":$info["url"];
		$hello=implode(" - ",$hello);
		$this->_mmi->log("Plugin : $hello ","pluginhello:$this->_baseclass");
	}

	public abstract function initialize($params);
	
	public final function pluginInit($mmi,$params=null)
	{		
		$this->_mmi=$mmi;
		$this->_baseclass=get_parent_class($this);
		$this->_params=$params;
		$this->pluginHello();
		$this->initialize($params);
	}
}