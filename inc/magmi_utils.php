<?php
//utilities function
// return null for empty string
function nullifempty($val)
{
	return (isset($val)?(trim($val)==""?null:$val):null);
}
// return false for empty string
function falseifempty($val)
{
	return (isset($val)?(strlen($val)==0?false:$val):false);
}
//test for empty string
function testempty($arr,$val)
{
	
	return !isset($arr[$val]) || strlen(trim($arr[$val]))==0;
}

function deleteifempty($val)
{
	return (isset($val)?(trim($val)==""?"__MAGMI_DELETE__":$val):"__MAGMI_DELETE__");
}

function csl2arr($cslarr,$sep=",")
{
	$arr=explode($sep,$cslarr);
	for($i=0;$i<count($arr);$i++)
	{
		$arr[$i]=trim($arr[$i]);		
	}
	return $arr;
}