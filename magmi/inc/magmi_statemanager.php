<?php
class Magmi_StateManager
{
	private static $_statefile=null;
	private static $_script=__FILE__;
	private static $_state="idle";
	
	public static function getStateFile()
	{
		return dirname(self::$_script)."/../state/.magmistate";
	}

	public static function setState($state)
	{
		if(self::$_state==$state)
		{
			return;	
		}

		self::$_state=$state;
		$f=fopen(self::getStateFile(),"w");
		fwrite($f,self::$_state);
		fclose($f);	
	}
	
	public static function getState($cached=false)
	{
		if(!$cached)
		{
			if(!file_exists(self::getStateFile()))
			{
				self::setState("idle");
			}
			$state=file_get_contents(self::getStateFile());		
		}
		else
		{
			$state=self::$_state;
		}
		return $state;
	}
	
}