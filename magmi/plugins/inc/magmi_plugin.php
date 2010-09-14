<?php

class Magmi_PluginOptionsPanel
{
	private $_plugin;
	
	public function __construct($pinst)
	{
		$this->_plugin=$pinst;	
	}
	
	public function getFile()
	{
		$dir=Magmi_PluginHelper::getPluginDir($this->_plugin);
		return 	substr($dir."/options_panel.php",1);
	}
	
	public function getHtml()
	{
		$plugin=$this->_plugin;
		$panelfile=$this->getFile();
		$content="";
		if(file_exists($panelfile))
		{
			ob_start();
			require_once($panelfile);
			$content = ob_get_contents();
			ob_end_clean();
		}
		return $content;
	}
	
	public function __call($data,$arg)
	{
		 return call_user_func_array(array($this->_plugin,$data), $arg);
	}
}

abstract class Magmi_Plugin
{
	protected $_mmi=null;
	protected $_class;
	protected $_plugintype;
		
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
	
	public function log($data,$type)
	{
		
		$this->_mmi->log($data,"plugin;$this->_class;$type");
	}
	
	public function pluginHello()
	{
		$info=$this->getPluginInfo();
		$hello=array(!isset($info["name"])?"":$info["name"]);
		$hello[]=!isset($info["version"])?"":$info["version"];
		$hello[]=!isset($info["author"])?"":$info["author"];
		$hello[]=!isset($info["url"])?"":$info["url"];
		$hellostr=implode("-",$hello);
		$base=get_parent_class($this);
		$this->log("$hellostr ","pluginhello");
		
	}

	public abstract function initialize($params);
	
	public final function pluginInit($mmi,$params=null,$doinit=true)
	{		
		$this->_mmi=$mmi;
		$this->_class=get_class($this);	
		$this->_params=$params;
		if(isset($mmi))
		{
			$this->pluginHello();		
		}
		if($doinit)
		{
			$this->initialize($params);
			
		}
	}
	public function getOptionsPanel()
	{
		return new Magmi_PluginOptionsPanel($this);
	}
	
	public function __call($data,$arg)
	{
		if(method_exists($this->_mmi,$data))
		{
		  return call_user_func_array(array($this->_mmi,$data), $arg);
		}
		else
		{
			die("Invalid Method Call: $data - Not found in Plugin nor MagentoMassImporter");
		}
	}
}