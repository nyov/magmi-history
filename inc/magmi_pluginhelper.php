<?php
class Magmi_PluginHelper
{
	
	public static function getPluginClasses($basedir,$baseclass)
	{
		$pgdir=dirname(__FILE__);
		$basedir="$pgdir/$basedir";
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
				"datasources"=>self::getPluginClasses("plugins/datasources","Magmi_Datasource"));
		
		
		if(isset($filter))
		{
			$out=array();
			foreach($tmp as $k=>$arr)
			{
				if(!isset($out[$k]))
				{
					$out[$k]=array();
				}
				$out[$k][]=$tmp[$k][$filter];
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