<?php
require_once("../magento_mass_importer.class.php");
try
{
	//read whole file
	$data=false;
	$out="";
	$skipped=0;
	//avoid warning
		$out="initializing...";
		if(MagentoMassImporter::getState()=="canceled")
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
			if(file_exists("./tmp_out.txt"))
			{
				$data=file_get_contents("./tmp_out.txt");
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
		foreach($lines as $line)
		{
			if($line!="")
			{
				list($type,$info)=explode(":",$line,2);
				if($type=="lookup")
				{
					list($nlines,$time)=explode(":",$info);
				}
				if($type=="step")
				{
					list($label,$step)=explode(":",$info);
				}

				if($type=="itime")
				{
					$parts=explode("-",$info);
					list($count,$elapsed,$lastinc)=array(trim($parts[0]),trim($parts[1]),trim($parts[2]));
					$speed = ceil(($count*60)/$elapsed);
					$elapsed=round($elapsed,4);
					$lastinc=round($lastinc,4);
				}
				if($type=="error")
				{
					$errors[]=$info;
				}
				if($type=="warning")
				{
					$warnings[]=$info;
				}
				if($type=="indexing")
				{
					if(substr($info,0,7)=="Reindex")
					{
						$curindex=$info;
						$indexes[$info]="";
					}
					else
					{
						$indexes[$curindex]=$info;
					}
				}
				if($type=="end")
				{
					$ended=true;
				}
				if($type=="skip")
				{
					$skipped+=1;
				}
			}

		}
		if($nlines>0)
		{
			if($nlines<$step)
			{
			
			}
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
			foreach($errors as $error)
			{
				$out.="<div class='log_error'>$error</div>";
			}
			foreach($warnings as $warning)
			{
				$out.="<div class='log_warning'>$warning</div>";
			}
			foreach($indexes as $k=>$v)
			{
				$out.="<div class='log_indexing'>$k....$v</div>";
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
		}
	}
	echo $out;
	flush();
	
}
catch(Exception $e)
{
	header("Status : 304",true,304);
}