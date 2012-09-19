<?php
require_once("../../../../inc/fshelper.php");

class RAResponse
{
	public $body;
	public $status;
	public $parsed;
	public $is_error; 
	public $result;
	public $error;
	public $op;
	
	public function __construct($arr,$op)
	{
		$this->status=$arr['status'][1];
		$this->body=$arr['body'];
		$this->parsed=json_decode($this->body,true);
		$this->op=$op;
		$this->parsed=$this->parsed[$op];
		if(isset($this->parsed['result']))
		{
			$this->result=$this->parsed['result'];
			$this->is_error=false;
		}
		else {
			$this->error=$this->parsed['error'];
			$this->is_error=true;
		}
	} 
}

class Magmi_RemoteAgent_Proxy extends MagentoDirHandler
{
	protected $_raurl=NULL;
	
	public function __construct($magurl,$raurl)
	{
		parent::__construct($magurl);
		$this->_raurl=$raurl;	
	}

	public function doPost($url, $params, $optional_headers = null)
	{
  		$ctxparams = array('http' => array(
              'method' => 'POST',
              'content' => http_build_query($params)
            ));
  		if ($optional_headers !== null) {
   			 $ctxparams['http']['header'] = $optional_headers;
  		}
        $ctx = stream_context_create($ctxparams);
  		$fp = @fopen($url, 'rb', false, $ctx);
  		if (!$fp) {
			return false;
  		}
  		$meta=stream_get_meta_data($fp);
  		$httpinfo=explode(' ',$meta['wrapper_data'][0]);
  		$response = @stream_get_contents($fp);
  		if ($response === false) {
			
  		}
  		return array("status"=>$httpinfo,"body"=>$response);
	}
	
	
	public function doOperation($op,$params=array())
	{
		$hresp=$this->doPost($this->_raurl,array_merge(array("api"=>$op),$params));
		if($hresp)
		{
			$resp=new RAResponse($hresp,$op);
			return $resp;
		}
		else
		{
			$this->_lasterror=array("code"=>0,"message"=>"No connection to proxy");
			return false;
		}
	}
	
	
	public function getVersion()
	{
		$r=$this->doOperation('getVersion');
		return $r->result['version'];
	}
	
	public function file_exists($filepath)
	{
		$r=$this->doOperation('file_exists',array('path'=>$filepath));
		return $r->result;
	}
	
	public function mkdir($path)
	{
		$r=$this->doOperation('file_exists',array('path'=>$filepath));
		if($r->is_error)
		{
			$this->_lasterror=$r->error;
		}
		return !$r->is_error;
	}
	
	public function unlink($filepath)
	{
		$r=$this->doOperation('unlink',array('path'=>$filepath));
		if($r->is_error)
		{
			$this->_lasterror=$r->error;
		}
		return !$r->is_error;
		
	}
	
	public function copy($srcpath, $destpath)
	{
		$r=$this->doOperation('copy',array('src'=>$srcpath,'dest'=>$destpath));
		if($r->is_error)
		{
			$this->_lasterror=$r->error;
		}
		return !$r->is_error;
		
	}
	
	public static function canHandle($url)
	{
		return preg_match('|^.*://.*$|',$url);
	}
	
	public function patchFSHelper()
	{
		MagentoDirHandlerFactory::registerHandler($this);
	}
	
	
}