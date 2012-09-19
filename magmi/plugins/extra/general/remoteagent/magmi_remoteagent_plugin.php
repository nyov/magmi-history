<?php
 require_once(dirname(__FILE__)."/magmi_remoteagent_proxy.php");
 require_once(dirname(__FILE__)."/magmi_remoteagent.php");
 
 class Magmi_RemoteAgentPlugin extends Magmi_GeneralImportPlugin
 {
 	protected $_raproxy;
 	protected $_active;
 	
 	public function __construct()
 	{			
 		$_active=false;
 	}
 	
 	public function getPluginInfo()
    {
        return array(
            "name" => "Remote Agent Plugin",
            "author" => "Dweeves",
            "version" => "0.0.1",
        	"url"=>pluginDocRoot("Remote_Agent")
        );
    }
    
    public function initialize($params)
    {
    	$this->_raproxy=new Magmi_RemoteAgent_Proxy(Magmi_Config::getInstance()->get('MAGENTO','basedir'),$this->getParam("MRAGENT:baseurl"));	
    }
    
    public function checkPluginVersion()
    {
   		$pv=$this->_raproxy->getVersion();
		$cv=Magmi_RemoteAgent::getVersion();
		if($pv<$cv)
		{
			$this->deployPlugin(Magmi_Config::getInstance()->getMagentoDir());
		}
    }
    
    public function deployPlugin($url)
    {
    	$ok=@copy(dirname(__FILE__)."/magmi_remoteagent.php");
    	if($ok==false)
    	{
    		$this->log("Cannot deploy Remote agent to $url");
    		$this->_active=false;
    	}
    }
    
    public function beforeImport()
    {
    	$this->checkPluginVersion();
    }
    
    public function getPluginParamNames()
    {
    	return array("MRAGENT:baseurl");
    }
 }
?>