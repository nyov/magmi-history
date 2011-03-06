<?php

/**
 * MAGENTO MASS IMPORTER CLASS
 *
 * version : 0.6
 * author : S.BRACQUEMONT aka dweeves
 * updated : 2010-10-09
 *
 */

/* use external file for db helper */
require_once("../inc/magmi_engine.php");
require_once("../inc/magmi_pluginhelper.php");

/* Magmi ProductImporter is now a Magmi_Engine instance */
class Magmi_UtilityEngine extends Magmi_Engine
{

	/**
	 * constructor
	 * @param string $conffile : configuration .ini filename
	 */
	public function __construct()
	{
	}

	public function getEngineName()
	{
		return "Magmi Utilities Engine";
	}
	
	/**
	 * load properties
	 * @param string $conf : configuration .ini filename
	 */

	
	public function initPlugins($profile)
	{
	}
	


	public function engineInit($params)
	{
		$this->_profile=$this->getParam($params,"profile","default");
		$this->initPlugins($this->_profile);
		$this->mode=$this->getParam($params,"mode","update");
	}
	
	public function engineRun($params)
	{
		$this->log("Magento Mass Importer by dweeves - version:".Magmi_Version::$version,"title");
		//initialize db connectivity
		Magmi_StateManager::setState("running");
		Magmi_StateManager::setState("idle");		
	}
	
	public function onEngineException($e)
	{
		Magmi_StateManager::setState("idle");
	}

}