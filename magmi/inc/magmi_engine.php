<?php
require_once("dbhelper.class.php");
require_once("magmi_config.php");
require_once("magmi_version.php");
require_once("magmi_utils.php");
require_once("magmi_statemanager.php");
require_once("magmi_pluginhelper.php");

abstract class Magmi_Engine extends DbHelper
{
	protected $_conf;
	protected $_initialized=false;
	
	public $magversion;
	public $magdir;
	public $tprefix;
	protected $_connected;
	protected $_activeplugins;
	protected $_pluginclasses;
	private $_excid=0;
	public $logger=null;
	
	public function getEngineInfo()
	{
		return array("name"=>"Generic Magmi Engine","version"=>"1.0","author"=>"dweeves");
	}
	
	public function __construct()
	{
		//force PHP internal encoding as UTF 8
		mb_internal_encoding("UTF-8");
	}
	
	public final  function initialize($params=array())
	{
		try
		{
			$this->_conf=Magmi_Config::getInstance();
			$this->_conf->load();
			$this->magversion=$this->_conf->get("MAGENTO","version");
			$this->magdir=$this->_conf->get("MAGENTO","basedir");
			$this->tprefix=$this->_conf->get("DATABASE","table_prefix");
			$this->_excid=0;
			$this->_initialized=true;
		}
		catch(Exception $e)
		{
			die("Error initializing Engine:{$this->_conf->getConfigFilename()} \n".$e->getMessage());
		}
		
	}
	
	/**
	 * Returns magento directory
	 */
	public function getMagentoDir()
	{
		return $this->magdir;
	}
	
	public function getPluginFamilies()
	{
		return array();
	}
	
	
	public function getEnabledPluginClasses($profile)
	{
		$enabledplugins=new EnabledPlugins_Config($profile);
		$enabledplugins->load();
		return $enabledplugins->getEnabledPluginFamilies($this->getPluginFamilies());
	}
	
	public function initPlugins($profile=null)
	{
		
		$this->_pluginclasses=$this->getEnabledPluginClasses($profile);
	}
	
	public function getBuiltinPluginClasses()
	{
		return array();
	}
	
	public function getPluginClasses()
	{
		return $this->_pluginclasses;
	}
	
	public function getPluginInstances($family=null)
	{
		$pil=null;
		if($family==null)
		{
			$pil=$this->_activeplugins();
		}
		else
		{
			$pil=(isset($this->_activeplugins[$family])?$this->_activeplugins[$family]:array());
		}
		return $pil;
	}
	
	public function createPlugins($profile,$params)
	{
		$plhelper=Magmi_PluginHelper::getInstance($profile);
		$this->_pluginclasses = array_merge_recursive($this->_pluginclasses,$this->getBuiltinPluginClasses());
		foreach($this->_pluginclasses as $pfamily=>$pclasses)
		{
			if(!isset($this->_activeplugins[$pfamily]))
			{
				$this->_activeplugins[$pfamily]=array();
			}
			foreach($pclasses as $pclass)
			{
				$this->_activeplugins[$pfamily][]=$plhelper->createInstance($pfamily,$pclass,$params,$this);
			}
		}
		
	}
	

	public function getPluginInstance($family,$order=0)
	{
		return $this->_activeplugins[$family][$order];	
	}
	
	public function callPlugins($types,$callback,&$data=null,$params=null,$break=true)
	{
		$result=true;
		if(!is_array($types))
		{
			if($types!="*")
			{
				$types=explode(",",$types);
			}
			else
			{
				$types=array_keys($this->_activeplugins);
			}
		}
		foreach($types as $ptype)
		{
			if(isset($this->_activeplugins[$ptype]))
			{
				foreach($this->_activeplugins[$ptype] as $pinst)
				{
					if(method_exists($pinst,$callback))
					{
						$callres=($data==null?($params==null?$pinst->$callback():$pinst->$callback($params)):$pinst->$callback($data,$params));
						if($callres===false && $data!=null)
						{
						  $result=false;
						  if($break)
						  {
						  	return $result;
						  }							
						}						
					}
				}
			}
		}
		return $result;
	}
	
	public function getParam($params,$pname,$default=null)
	{
		return isset($params[$pname])?$params[$pname]:$default;
	}
	
	public function setLogger($logger)
	{
		$this->logger=$logger;
	}
/**
	 * logging function
	 * @param string $data : string to log
	 * @param string $type : log type
	 */
	public function log($data,$type="default")
	{
		if(isset($this->logger))
		{
			$this->logger->log($data,$type);
		}
	}
	
	public function logException($e,$data)
	{
		$this->_excid++;
		$this->trace($e);
		$this->log($this->_excid.":".$e->getMessage()." - ".$data,"error");
	}
	
	public function trace($e)
	{
		
		$traces=$e->getTrace();
		$f=fopen(Magmi_StateManager::getTraceFile(),"a");
		fwrite($f,"---- TRACE : $this->_excid -----\n");
		$trstr="";
		
		foreach($traces as $trace)
		{
			$trstr="**********************************************\n";
			if(isset($trace["file"]))
			{
				$fname=str_replace(dirname(dirname(__FILE__)),"",$trace["file"]);
				$trstr.= $fname.":".(isset($trace["line"])?$trace["line"]:"?")." - ";
				if(isset($trace["class"]))
				{
					$trstr.=$trace["class"]."->";
					if(isset($trace["function"]))
					{
						$trstr.=$trace["function"];
					}
					$trstr.="\n----------------------------------------\n";
					if(isset($trace["args"]))
					{
						$trstr.=print_r($trace["args"],true);
					}
					
					$trstr.="\n";
					fwrite($f,$trstr);
				}
			}
		}
		fwrite($f,"+++++++++++++++++++++++++++++\nCONTEXT DUMP\n+++++++++++++++++++++++++++++\n");
		fwrite($f,print_r($this,true));
		fwrite($f,"\n+++++++++++++++++++++++++++++\nEND CONTEXT DUMP\n+++++++++++++++++++++++++++++\n");		
		
		fwrite($f,"---- ENDTRACE : $this->_excid -----\n");
		fclose($f);
	}
	
	
	/**
	 * Engine run method
	 * @param array $params - run parameters
	 */
	public final function run($params=array())
	{
		try
		{
			$f=fopen(Magmi_StateManager::getTraceFile(),"w");
			fclose($f);
			$enginf=$this->getEngineInfo();
			$this->log("Running {$enginf["name"]} v${enginf["version"]} by ${enginf["author"]}","startup");
			if(!$this->_initialized)
			{
				$this->initialize($params);
			}
			$this->connectToMagento();
			$this->engineInit($params);
			$this->engineRun($params);
			$this->disconnectFromMagento();
		}
		catch(Exception $e)
		{
			$this->disconnectFromMagento();
			
			$this->handleException($e);
		}
	
	}
	
	public function handleException($e)
	{
		$this->_excid++;
		$this->trace($e);
		$this->log($this->_excid.":".$e->getMessage(),"error");		
		$this->onEngineException($e);
	}
	
	/**
		shortcut method for configuration properties get
	 */
	public function getProp($sec,$val,$default=null)
	{
		return $this->_conf->get($sec,$val,$default);
	}
	
	/**
	 * Initialize Connection with Magento Database
	 */
	public function connectToMagento()
	{
		#get database infos from properties
		if(!$this->_connected)
		{
			$host=$this->getProp("DATABASE","host","localhost");
			$dbname=$this->getProp("DATABASE","dbname","magento");
			$user=$this->getProp("DATABASE","user");
			$pass=$this->getProp("DATABASE","password");
			$debug=$this->getProp("DATABASE","debug");
			$conn=$this->getProp("DATABASE","connectivity");
			$port=$this->getProp("DATABASE","port");
			$socket=$this->getProp("DATABASE","unix_socket");
			$this->initDb($host,$dbname,$user,$pass,$port,$socket,$conn,$debug);
			//suggested by pastanislas
			$this->_db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);
		}
	}
	/*
	 * Disconnect Magento db
	 */
	public function disconnectFromMagento()
	{
		if($this->_connected)
		{
			$this->exitDb();
		}
	}
	
	/**
	 * returns prefixed table name
	 * @param string $magname : magento base table name
	 */
	public function tablename($magname)
	{
		return $this->tprefix!=""?$this->tprefix."_$magname":$magname;
	}
	
	
	public abstract function engineInit($params);
	public abstract function engineRun($params);
	
}