<?php
require_once("../inc/magmi_statemanager.php");
try
{
	//read whole file
	$data=false;
	$out="";
	$sout="";
	$skipped=0;
	//avoid warning
	$out="initializing...";
	if(Magmi_StateManager::getState()=="canceled")
	{
		$sout="";
		$sout.="<div class='log_warning'>Canceled by user</div>";
		$sout.="<div class='log_warning'>";
		$sout.="<span><a href='magmi.php'>Back to Configuration Page</a></span>";
		$sout.="<span><a href='magmi.php?run=1'>Back to Import</a></span>";
		$sout.="</div>";
		$sout.="<script type=\"text/javascript\">endImport();</script>";
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
				if(preg_match_all("/plugin;(\w+);(\w+)$/",$type,$m))
				{
					$plclass=$m[1][0];
					$type=$m[2][0];
				}
				switch($type){
					case "title":
						break;
					case "raw":
						$out.=$info;
						break;
					case "pluginhello":
						list($name,$ver,$auth)=explode("-",$info);
						$out.="<div class='pluginhello'>$name v$ver by $auth</div>";
						break;
					case "reset":
					case "startup":
						$out.="<div class='log_standard'>".htmlspecialchars($info)."</div>";
						break;
					case "lookup":
						list($nlines,$time)=explode(":",$info);
						break;
					case "step":
						list($label,$step)=explode(":",$info);
						break;
					case "dbtime":
						$parts=explode("-",$info);
						list($dcount,$delapsed,$dlastinc,$dlastcount)=array(trim($parts[0]),trim($parts[1]),trim($parts[2]),trim($parts[3]));
						$dspeed = ceil(($dcount*60)/$delapsed);
						$delapsed=round($delapsed,4);
						$dlastinc=round($dlastinc,4);
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
						$sout.="<div class='log_standard'>".htmlspecialchars($info)."</div>";
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
			if($dcount)
			{
				$out.="<div class='log_itime'>";
				$out.="DB STATS:$dcount requests in $delapsed secs - avg speed : $dspeed reqs/min ,avg reqs ".round($dcount/$count,2)."/item - global efficiency: ".round(($delapsed*100/$elapsed),2)."% - last $step items: $dlastcount reqs (".($dlastcount/$step)." reqs/item)";
				$out.="</div>";
			}
		}
		foreach($errors as $error)
		{
			$out.="<div class='log_error'>".htmlspecialchars($error)."</div>";
		}
		foreach($warnings as $warning)
		{
			$out.="<div class='log_warning'>".htmlspecialchars($warning)."</div>";
		}
		if($skipped>0)
		{
			$out.="<div class='log_info'>Skipped $skipped records</div>";
		}
		if($ended)
		{
			$sout.="<div class='log_end".(count($errors)>0?" log_error":"")."'>";
			$sout.="<span><a href='magmi.php'>Back to Configuration Page</a></span>";
			$sout.="<span><a href='magmi.php?run=1'>Back to Import</a></span>";
			$sout.="</div>";
			$sout.="<script type=\"text/javascript\">endImport();</script>";
		}
	}

	echo $out.$sout;
	flush();

}
catch(Exception $e)
{
	header("Status : 304",true,304);
}