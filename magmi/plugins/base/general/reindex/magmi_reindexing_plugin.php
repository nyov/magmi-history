<?php
class Magmi_ReindexingPlugin extends Magmi_GeneralImportPlugin
{
	protected $_reindex;
	protected $_indexlist="catalog_product_attribute,catalog_product_price,catalog_product_flat,catalog_category_flat,catalog_category_product,cataloginventory_stock,catalog_url,catalogsearch_fulltext";
	protected $_phpexecname;
	protected $_indexingcl=null;
	
	public function getPluginInfo()
	{
		return array("name"=>"Magmi Magento Reindexer",
					 "author"=>"Dweeves",
					 "version"=>"1.0.3c");
	}
	
	public function afterImport()
	{
		$this->log("running indexer","info");
		$this->updateIndexes();
		return true;
	}
	
	public function getPluginParamNames()
	{
		return array("REINDEX:indexes");
	}
	
	public function getIndexList()
	{
		return $this->_indexlist;
	}
	
	public function updateIndexes()
	{
		if(!isset($this->_indexingcl))
		{
			$cl=$this->getIndexingCommandLine();
			if(is_array($cl))
			{
				$this->log($cl[1],"error");
				return false;			
			}
			else
			{
				$this->_indexingcl=$cl;
			}
		}
		$idxlstr=$this->getParam("REINDEX:indexes","");
		$idxlist=explode(",",$idxlstr);
		if(count($idxlist)==0)
		{
			$this->log("No indexes selected , skipping reindexing...","warning");
			return true;
		}
		foreach($idxlist as $idx)
		{
			$tstart=microtime(true);
			$this->log("Reindexing $idx....","info");
			$out = shell_exec("$this->_indexingcl --reindex $idx 2>&1");
			$this->log($out,"info");
			$tend=microtime(true);
			$this->log("done in ".round($tend-$tstart,2). " secs","info");
			if(Magmi_StateManager::getState()=="canceled")
			{
				exit();
			}			
			flush();
		}
	}
	
	public function getIndexingCommandLine()
	{
		$magdir=Magmi_Config::getInstance()->get("MAGENTO","basedir");
		
		$indexer=realpath("$magdir/shell/indexer.php");
		if($indexer==false)
		{
			return array(false,"cannot find magento shell indexer script");
		}
		
		$phpexectest=array("php5","php");
		$runok=false;
		$errors=array();
		foreach($phpexectest as $php)
		{
			
			$this->_phpexecname=$php;
			$out=shell_exec("$php $indexer 2>&1");
			if(preg_match("/Usage:/msi",$out))
			{
				$runok=true;	
				break;
			}
			else
			{
				$errors[]=$out;
			}
			
		}
		if($runok)
		{
			return "$php $indexer";
		}
		else
		{
			return array($runok,"Multiple tries result:\n".implode("---------\n",$errors));
		}
	}
	
	public function isRunnable()
	{
		$cl=$this->getIndexingCommandLine();
		if(is_array($cl))
		{
			return $cl;
		}
		else
		{
			return array(true,"");
		}
	}
	
	public function initialize($params)
	{
		
	}
}