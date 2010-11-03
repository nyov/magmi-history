<?php
require_once("../inc/magmi_config.php");

class Magmi_PluginConfig extends Properties
{
	protected $_prefix;
	protected $_conffile;
	public function __construct($pname)
	{
		$this->_prefix=$pname;
		$this->_conffile=Magmi_Config::getConfDir()."/$this->_prefix.conf";
	}
	
	public function getIniStruct($arr)
	{
		$conf=array();
		foreach($arr as $k=>$v)
		{
			$k=$this->_prefix.":".$k;
			list($section,$value)=explode(":",$k,2);
			if(!isset($conf[$section]))
			{
				$conf[$section]=array();
			}
			$conf[$section][$value]=$v;
		}
		return $conf;
	}
	
	public function save()
	{
		parent::save(Magmi_Config::getConfDir()."/$this->_prefix.conf");
	}
	
	public function load()
	{
		
		
		if(file_exists($this->_conffile))
		{
			parent::load($this->_conffile);
		}
	}
	
	public function getConfig()
	{
		return parent::getsection($this->_prefix);
	}
}

class Magmi_PluginOptionsPanel
{
	private $_plugin;
	private $_defaulthtml="";
	
	public function __construct($pinst)
	{
		$this->_plugin=$pinst;
		$this->initDefaultHtml();
	}
	
	public function getFile()
	{
		$dir=Magmi_PluginHelper::getPluginDir($this->_plugin);
		return 	substr($dir."/options_panel.php",1);
	}

	public final function initDefaultHtml()
	{
		$panelfile=dirname(__FILE__)."/magmi_default_options_panel.php";
		ob_start();
		require($panelfile);
		$this->_defaulthtml = ob_get_contents();
		ob_end_clean();		
		
	}
	public function getHtml()
	{
		$plugin=$this->_plugin;
		$panelfile=$this->getFile();
		$content="";
		if(!file_exists($panelfile))
		{
			$content=$this->_defaulthtml;
		}
		else
		{
			ob_start();
			require($panelfile);
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
	protected $_config;
	
	public function __construct()
	{
		$this->_config=new Magmi_PluginConfig(get_class($this));	
	}
	
	public function getParam($pname,$default=null)
	{
		return (isset($this->_params[$pname]) && $this->_params[$pname]!="")?$this->_params[$pname]:$default;
		
	}
	
	public function getPluginParamNames()
	{
		return array();
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
	
	public function log($data,$type='std')
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
		$this->_config->load();
		$this->_params=isset($params)?array_merge($this->_config->getConfig(),$params):$this->_config->getConfig();
		if(isset($mmi))
		{
			$this->pluginHello();		
		}
		if($doinit)
		{
			$this->initialize($params);
			
			$this->persistParams($this->getPluginParams($params));
		}
	}
	
	
	public function getPluginParams($params)
	{
		$arr=array();
		$paramkeys=$this->getPluginParamNames();
		foreach($paramkeys as $pk)
		{
			if(isset($params[$pk]))
			{
				$arr[$pk]=$params[$pk];
			}
			else
			{
				$arr[$pk]=$this->_params[$pk];
			}	
		}
		return $arr;
	}
	
	public function persistParams($plist)
	{
		if(count($plist)>0)
		{
			$this->_config->setPropsFromFlatArray($plist);
			$this->_config->save();
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