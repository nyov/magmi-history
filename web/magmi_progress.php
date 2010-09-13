<?php
require_once("../inc/magmi_statemanager.php");
try
{
	//read whole file
	$data=false;
	$out="";
	$skipped=0;
	//avoid warning
	$out="initializing...";
	if(Magmi_StateManager::getState()=="canceled")
	{
		$out="";
		$out.="<div class='log_warning'>Canceled by user</div>";
		$out.="<div class='log_warning'>";
		$out.="<span><a href='magmi.php'>Back to Configuration Page</a></span>";
		$out.="<span><a href='magmi.php?run=1'>Back to Import</a></span>";
		$out.="</div>";
		$out.="<script type=\"text/javascript\">endImport();</script>";
	}
	else
	{
		if(file_exists("../state/tmp_out.txt"))
		{
			$data=file_get_contents("../state/tmp_out.txt");
		}
	}
	if($data)
	{
		$out.="<div class='log_start'>Import Started</div>";
		$lines=explode("\n",$data);
		$errors=array();
		$warnings=array();
		$indexes=array();
		$step=100;
		$ended=false;
		$count=0;
		$nlines=-1;
		$sout="";
		foreach($lines as $line)
		{
			if($line!="")
			{
				list($type,$info)=explode(":",$line,2);
				switch($type){
					case "title":
						break;
					case "pluginhello":
						list($ptype,$data)=explode(":",$info,2);
						list($name,$ver,$auth)=explode("-",$data);
						$out.="<div class='pluginhello'>$name v$ver by $auth</div>";
						break;
					case "lookup":
						list($nlines,$time)=explode(":",$info);
						break;
					case "step":
						list($label,$step)=explode(":",$info);
						break;
					case "itime":
						$parts=explode("-",$info);
						list($count,$elapsed,$lastinc)=array(trim($parts[0]),trim($parts[1]),trim($parts[2]));
						$speed = ceil(($count*60)/$elapsed);
						$elapsed=round($elapsed,4);
						$lastinc=round($lastinc,4);
						break;
					case "error":
						$errors[]=$info;
						break;
					case "warning":
						$warnings[]=$info;
						break;
					case "end":
						$ended=true;
						break;
					case "skip":
						$skipped+=1;
						break;
					default:
						$sout.="<div class='log_standard'>$info</div>";
						break;
				}

			}
		}
		if($nlines>0)
		{
			$percent=round(((float)$count*100)/$nlines,2);
			$out.="<script type=\"text/javascript\">setProgress($percent);</script>";
			if($count)
			{
				$out.="<div class='log_itime'>";
				$lstep=$count%$step;
				if($lstep!=0)
				{
					$step=$lstep;
				}
				$out.="imported $count items ($percent %) in $elapsed secs (last $step in $lastinc secs) - avg speed : $speed rec/min </div>";
			}
		}
	}
	foreach($errors as $error)
	{
		$out.="<div class='log_error'>$error</div>";
	}
	foreach($warnings as $warning)
	{
		$out.="<div class='log_warning'>$warning</div>";
	}
	if($skipped>0)
	{
		$out.="<div class='log_info'>Skipped $skipped records</div>";
	}
	if($ended)
	{
		$out.="<script type=\"text/javascript\">setProgress(100);</script>";
		$out.="<div class='log_end".(count($errors)>0?" log_error":"")."'>";
		$out.="<span><a href='magmi.php'>Back to Configuration Page</a></span>";
		$out.="<span><a href='magmi.php?run=1'>Back to Import</a></span>";
		$out.="</div>";
		$out.="<script type=\"text/javascript\">endImport();</script>";
	}

	echo $out.$sout;
	flush();

}
catch(Exception $e)
{
	header("Status : 304",true,304);
}