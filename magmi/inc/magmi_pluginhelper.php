<?php
$base_dir=dirname(__FILE__);
$plugin_dir=dirname(__FILE__)."/../plugins";
ini_set("include_path",ini_get("include_path").":$plugin_dir/inc:$base_dir");

require_once("magmi_item_processor.php");
require_once("magmi_datasource.php");

class Magmi_PluginHelper
{
	
	public static function getPluginClasses($basedir,$baseclass)
	{
		$pgdir=dirname(__FILE__);
		$basedir="$pgdir/../$basedir";
		$candidates=glob("$basedir/*/*.php");
		$pluginclasses=array();
		foreach($candidates as $pcfile)
		{
			$content=file_get_contents($pcfile);
			if(preg_match_all("/class\s+(.*?)\s+extends\s+$baseclass/mi",$content,$matches,PREG_SET_ORDER))
			{
				require_once($pcfile);				
				foreach($matches as $match)
				{
					$pluginclasses[]=array("class"=>$match[1],"dir"=>dirname(substr($pcfile,strlen($pgdir))));
				}
			}
		}
		return $pluginclasses;
	}

	public static function scanPlugins($filter=null)
	{
		$tmp=array("itemprocessors"=>self::getPluginClasses("plugins/itemprocessors","Magmi_ItemProcessor"),
				"datasources"=>self::getPluginClasses("plugins/datasources","Magmi_Datasource"),
				"general"=>self::getPluginClasses("plugins/general","Magmi_GeneralImportPlugin"));
					
		
		if(isset($filter))
		{
			$out=array();
			foreach($tmp as $k=>$arr)
			{
				if(!isset($out[$k]))
				{
					$out[$k]=array();
				}
				foreach($arr as $desc)
				{
					$out[$k][]=$desc[$filter];
				}
			}	
			$plugins=$out;
		}
		else
		{
			$plugins=$tmp;
		}
		
		return $plugins;
	}
	
	public static function createInstance($pclass)
	{
		$plinst=new $pclass();
		$plinst->pluginInit(null,$params,false);
		return $plinst;
		
	}
}