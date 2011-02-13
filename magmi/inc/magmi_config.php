<?php
require_once("properties.php");
define("DS",DIRECTORY_SEPARATOR);

class DirbasedConfig extends Properties
{ 
	protected $_basedir=null;
	protected $_confname=null;
	
	public function __construct($basedir,$confname)
	{
		$this->_basedir=$basedir;
		$this->_confname=$basedir.DS.$confname;
	}
	
	public function getConfFile()
	{
		return $this->_confname;
	}
	
	public function getLastSaved($fmt)
	{
		return strftime($fmt,filemtime($this->getConfFile()));
	}
	
	public function load($name=null)
	{
		if($name==null)
		{
			parent::load($this->_confname);
		}
		else
		{
			parent::load($name);
		}
	}
	
	public function save($arr=null)
	{
		if($arr!=null)
		{
			$this->setPropsFromFlatArray($arr);
		}
		parent::save($this->_confname);
	}

	public function saveTo($arr,$newdir)
	{
		if(!file_exists($newdir))
		{
			mkdir($newdir,0664);
		}	
		parent::save($newdir.DS.basename($this->_confname));
		$this->_basedir=$newdir;
		$this->_confname=$newdir.DS.basename($this->_confname);
	}
	
	public function getConfDir()
	{
		return $this->_basedir;
	}
}

class ProfileBasedConfig extends DirbasedConfig
{
	private static $_script=__FILE__;
	protected $_profile=null;
	
	public function getProfileDir()
	{
		$subdir=($this->_profile==null?"":DS.$this->_profile);
		$confdir=realpath(dirname(dirname(self::$_script)).DS."conf$subdir");
		return $confdir;
	}
	
	public function __construct($fname,$profile=null)
	{
		$this->_profile=$profile;
		parent::__construct($this->getProfileDir(),$fname);
	}
	
}


class Magmi_Config extends DirbasedConfig
{
	private static $_instance=null;
	private $_defaultconfigname=null;
	public static $conffile=null;
	private static $_script=__FILE__;
	private static $_profile=null;

		
	public function getConfDir()
	{
		$confdir=realpath(dirname(dirname(self::$_script)).DS."conf");
		return $confdir;
	}
	
	public function __construct()
	{
		parent::__construct($this->getConfDir(),"magmi.ini");
		
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
		return !file_exists($this->_confname);	
	}
	
	public function load()
	{
		$conf=(!$this->isDefault())?$this->_confname:$this->_confname.".default";
		parent::load($conf);
		//Migration from 0.6.17
		if($this->hasSection("PLUGINS_DATASOURCES"))
		{
			$pluginsconf=new DirbasedConfig($this->getConfDir(),"plugins.conf");
			$arr=array("PLUGINS_DATASOURCES"=>$this->getSection("PLUGINS_DATASOURCES"),
					   "PLUGINS_GENERAL"=>$this->getSection("PLUGINS_GENERAL"),
					   "PLUGINS_ITEMPROCESSORS"=>$this->getSection("PLUGINS_ITEMPROCESSORS"));
			$pluginsconf->setProps($arr);
			$pluginsconf->save();
			$this->removeSection("PLUGINS_DATASOURCES");
			$this->removeSection("PLUGINS_GENERAL");
			$this->removeSection("PLUGINS_ITEMPROCESSORS");
			$this->save();
			
		}
		return $this;
	}
		
	
	public function save($arr=null)
	{
		if($arr!==null)
		{
		foreach($arr as $k=>$v)
		{
			if(!preg_match("/\w+:\w+/",$k))
			{
				unset($arr[$k]);
			}
		}
		}
		parent::save($arr);		
	}
	
	public function getProfileList()
	{
		$proflist=array();
		$candidates=scandir($this->getConfDir());
		foreach($candidates as $candidate)
		{
			if(is_dir($this->getConfDir().DS.$candidate) && $candidate[0]!=".")
			{
				$proflist[]=$candidate;
			}
		}
		return $proflist;
	}
	
}

class EnabledPlugins_Config extends ProfileBasedConfig
{
	
	public function __construct($profile=null)
	{
		parent::__construct("plugins.conf",$profile);
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
	
}
