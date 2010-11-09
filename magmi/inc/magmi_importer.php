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
require_once("dbhelper.class.php");

require_once("magmi_statemanager.php");
require_once("magmi_pluginhelper.php");
require_once("magmi_config.php");
require_once("magmi_attributehandler.php");

ini_set("allow_url_fopen",true);
function nullifempty($val)
{
	return (isset($val)?(strlen($val)==0?null:$val):null);
}

function falseifempty($val)
{
	return (isset($val)?(strlen($val)==0?false:$val):false);
}

function testempty($arr,$val)
{
	return !isset($arr[$val]) || strlen($val)==0;
}

class Magmi_DefaultAttributeHandler extends Magmi_AttributeHandler
{

	protected $_curpid=null;

	public function setCurrentPid($pid)
	{
		$this->_curpid=$pid;
	}
	/**
	 * attribute handler for decimal attributes
	 * @param int $pid	: product id
	 * @param int $ivalue : initial value of attribute
	 * @param array $attrdesc : attribute description
	 * @return mixed : false if no further processing is needed,
	 * 					string (magento value) for the decimal attribute otherwise
	 */
	public function handleDecimalAttribute($storeid,$ivalue,$attrdesc)
	{
		$ovalue=falseifempty($ivalue);
		return $ovalue;
	}

	/**
	 * attribute handler for datetime attributes
	 * @param int $pid	: product id
	 * @param int $ivalue : initial value of attribute
	 * @param array $attrdesc : attribute description
	 * @return mixed : false if no further processing is needed,
	 * 					string (magento value) for the datetime attribute otherwise
	 */
	public function handleDatetimeAttribute($storeid,$ivalue,$attrdesc)
	{
		$ovalue=nullifempty($ivalue);
		return $ovalue;
	}

	/**
	 * attribute handler for int typed attributes
	 * @param int $pid	: product id
	 * @param int $ivalue : initial value of attribute
	 * @param array $attrdesc : attribute description
	 * @return mixed : false if no further processing is needed,
	 * 					int (magento value) for the int attribute otherwise
	 */
	public function handleIntAttribute($storeid,$ivalue,$attrdesc)
	{
		$ovalue=$ivalue;
		$attid=$attrdesc["attribute_id"];
		if($ivalue=="" && $this->_mmi->mode=="create")
		{
			return false;
		}
		//if we've got a select type value
		if($attrdesc["frontend_input"]=="select")
		{
			//we need to identify its type since some have no options
			switch($attrdesc["source_model"])
			{
				//if its status, make it available
				case "catalog/product_status":
					$ovalue=($ivalue==$this->_mmi->enabled_label?1:2);
					break;
					//if it's tax_class, get tax class id from item value
				case "tax/class_source_product":
					$ovalue=$this->_mmi->getTaxClassId($ivalue);
					break;
					//if it's visibility ,set it to catalog/search
				case "catalog/product_visibility":
					$ovalue=4;
					break;
					//otherwise, standard option behavior
					//get option id for value, create it if does not already exist
					//do not insert if empty
				default:
					if($ivalue=="" && $this->_mmi->mode=="update")
					{
						return "__MAGMI_DELETE__";
					}
					$oids=$this->_mmi->getOptionIds($attid,$storeid,array($ivalue));
					$ovalue=$oids[0];
					unset($oids);
					break;
			}
		}
		return $ovalue;
	}


	/**
	 * attribute handler for varchar based attributes
	 * @param int $pid : product id
	 * @param string $ivalue : attribute value
	 * @param array $attrdesc : attribute description
	 */
	public function handleVarcharAttribute($storeid,$ivalue,$attrdesc)
	{

		if($storeid!==0 && empty($ivalue) && $this->_mmi->mode=="create")
		{
			return false;
		}

		$ovalue=$ivalue;
		$pid=$this->_curpid;
		$attid=$attrdesc["attribute_id"];
		//if it's an image attribute (image,small_image or thumbnail)
		if($attrdesc["frontend_input"]=="media_image")
		{
			//do nothing if empty
			if($ivalue=="")
			{
				return false;
			}
			//else copy image file
			$imagefile=$this->_mmi->copyImageFile($ivalue);
			$ovalue=$imagefile;
			//add to gallery as excluded
			if($imagefile!==false)
			{
				$vid=$this->_mmi->addImageToGallery($pid,$storeid,$attrdesc,$imagefile,true);
			}
		}
		else
		//if it's a gallery
		if($attrdesc["frontend_input"]=="gallery")
		{
			//do nothing if empty
			if($ivalue=="")
			{
				return false;
			}
			$this->_mmi->resetGallery($pid,$storeid,$attid);
			//use ";" as image separator
			$images=explode(";",$ivalue);
			//for each image
			foreach($images as $imagefile)
			{
				//copy it from source dir to product media dir
				$imagefile=$this->_mmi->copyImageFile($imagefile);
				if($imagefile!==false)
				{
					//add to gallery
					$vid=$this->_mmi->addImageToGallery($pid,$storeid,$attrdesc,$imagefile);
				}
			}
			unset($images);
			//we don't want to insert after that
			$ovalue=false;
		}
		else
		//--- Contribution From mennos , optimized by dweeves ----
		//Added to support multiple select attributes
		//(as far as i could figure out) always stored as varchars
		//if it's a multiselect value
		if($attrdesc["frontend_input"]=="multiselect")
		{
			//if empty delete entry
			if($ivalue=="")
			{
				return "__MAGMI_DELETE__";
			}
			//magento uses "," as separator for different multiselect values
			$multiselectvalues=explode(",",$ivalue);
			$oids=$this->_mmi->getOptionIds($attid,$storeid,$multiselectvalues);
			$ovalue=implode(",",$oids);
			unset($oids);
		}
		return $ovalue;
	}

}
/* here inheritance from DBHelper is used for syntactic convenience */
class MagentoMassImporter extends DBHelper
{

	public $attrinfo=array();
	public $attrbytype=array();
	public $website_ids=array();
	public $store_ids=array();
	public $status_id=array();
	public $attribute_sets=array();
	public $ws_store_map=array();
	public $store_ws_map=array();
	public $reset=false;
	public $magdir;
	public $imgsourcedir;
	public $tprefix;
	public $logger=null;
	public $enabled_label;
	public $prod_etype;
	public $sidcache=array();
	public $mode="update";
	public static $state=null;
	protected static $_statefile=null;
	public static $version="0.6.10a";
	public $customip=null;
	public  static $_script=__FILE__;
	private $_pluginclasses=array();
	private $_activeplugins;
	private $_conf;
	private $_initialized=false;
	private $_attributehandlers;
	private $current_row;
	private $_optidcache=null;
	private $_curitemids=array("sku"=>null);
	private $_dstore;
	private $_same;
	private $_currentpid;
	public $magversion;

	public function setLogger($logger)
	{
		$this->logger=$logger;
	}

	/**
	 * constructor
	 * @param string $conffile : configuration .ini filename
	 */
	public function __construct()
	{
	}

	/**
	 * load properties
	 * @param string $conf : configuration .ini filename
	 */
	public function init()
	{
		if($this->_initialized)
		{
			return;
		}
		try
		{
			$pluginclasses=Magmi_PluginHelper::getPluginClasses();
			$this->_activeplugins=array("general"=>array(),"processors"=>array());
			$this->_conf=Magmi_Config::getInstance();
			$this->_conf->load();
			$this->magversion=$this->_conf->get("MAGENTO","version");
			$this->magdir=$this->_conf->get("MAGENTO","basedir");
			$this->imgsourcedir=$this->_conf->get("IMAGES","sourcedir",$this->magdir."/media/import");
			$this->tprefix=$this->_conf->get("DATABASE","table_prefix");
			$this->enabled_label=$this->_conf->get("MAGENTO","enabled_status_label","Enabled");
			$enproc=explode(",",$this->_conf->get("PLUGINS_ITEMPROCESSORS","classes",implode(",",$pluginclasses["itemprocessors"])));
			$this->_pluginclasses["processors"]=array_intersect($enproc,$pluginclasses["itemprocessors"]);
			$this->datasource_class=$this->_conf->get("PLUGINS_DATASOURCES","class",$pluginclasses["datasources"][0]);
			$engen=explode(",",$this->_conf->get("PLUGINS_GENERAL","classes",implode(",",$pluginclasses["general"])));
			$this->_pluginclasses["general"]=array_intersect($engen,$pluginclasses["general"]);
			$this->_initialized=true;
		}
		catch(Exception $e)
		{
			die("Error parsing ini file:{$this->_conf->getConfigFilename()} \n".$e->getMessage());
		}
	}

	public function getProp($sec,$val,$default=null)
	{
		return $this->_conf->get($sec,$val,$default);
	}
	/**
	 * Initialize Connection with Magento Database
	 */
	public function connectToMagento()
	{
		#get database infos from properties
		$host=$this->getProp("DATABASE","host","localhost");
		$dbname=$this->getProp("DATABASE","dbname","magento");
		$user=$this->getProp("DATABASE","user");
		$pass=$this->getProp("DATABASE","password");
		$debug=$this->getProp("DATABASE","debug");
		$this->initDb($host,$dbname,$user,$pass,$debug);
		//suggested by pastanislas
		$this->_db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);
	}

	/*
	 * Disconnect Magento db
	 */
	public function disconnectFromMagento()
	{
		$this->exitDb();
	}

	public function registerAttributeHandler($ahclass)
	{
		$this->_attributehandlers[]=new $ahclass($this);
	}
	/**
	 * Initialize websites list
	 */
	public function initWebsites()
	{

		//Get all websites code/ids
		$tname=$this->tablename("core_website");
		$sql="SELECT code,website_id FROM $tname";
		$result=$this->selectAll($sql);
		foreach($result as $r)
		{
			$this->website_ids[$r["code"]]=$r["website_id"];
		}
		unset($result);
	}

	public function getWebsitesStoreIds($wsstr)
	{
		$sids=array();
		$wsarr=explode(",",$wsstr);
		foreach($wsarr as $ws)
		{
			if(!isset($this->website_ids[$ws]))
			{
				$this->log("unknown store code:$ws","warning");
			}
			else
			{
				if(isset($this->ws_store_map[$this->website_ids[$ws]]))
				{
					$sids=array_merge($sids,$this->ws_store_map[$this->website_ids[$ws]]);
				}
			}
		}
		return array_unique($sids);
	}

	public function getWebsiteIds($storestr)
	{
		if(!isset($this->store_ws_map[$storestr]))
		{
			$this->store_ws_map[$storestr]=array();
			$sarr=$this->getStoreIds($storestr);
			$sql="SELECT DISTINCT website_id FROM ".$this->tablename("core_store")." WHERE store_id IN (".implode(",",$sarr).")";
			$result=$this->selectAll($sql,null);
			foreach($result as $r)
			{
				$this->store_ws_map[$storestr][]=$r["website_id"];
			}
		}
		return $this->store_ws_map[$storestr];
	}
	/**
	 * logging function
	 * @param string $data : string to log
	 * @param string $type : log type
	 */
	public function log($data,$type="default")
	{
		if(isset($this->logger))
		{
			$this->logger->log($data,$type);
		}
		else
		{
			print "$type:($data)\n";
			flush();
		}
	}
	/**
	 * Initilialize webstore list
	 */
	public function initStores()
	{
		$tname=$this->tablename("core_store");
		$sql="SELECT code,store_id,website_id FROM $tname";
		$result=$this->selectAll($sql);
		$wsm=array();
		foreach($result as $r)
		{
			$this->store_ids[$r["code"]]=$r["store_id"];
			$wsid=$r["website_id"];
			if(!isset($wsm[$wsid]))
			{
				$wsm[$wsid]=array();
			}
			$wsm[$wsid][]=$r["store_id"];
		}
		unset($result);
		$this->ws_store_map=$wsm;
	}


	public function getStoreIds($storestr)
	{
		//if no cache hit for these store list
		if(!isset($this->sidcache[$storestr]))
		{
			//default store flag
			$bfound=false;
			$stores=explode(",",$storestr);
			$sids=array();
			//find store id for store list
			foreach($stores as $scode)
			{

				$sid=$this->store_ids[$scode];

				//add store id to id list
				$sids[]=$sid;
			}
			//fill id cache list for store list
			$this->sidcache[$storestr]=$sids;
		}
		//return id cache list for store list
		return $this->sidcache[$storestr];
	}
	/**
	 * returns prefixed table name
	 * @param string $magname : magento base table name
	 */
	public function tablename($magname)
	{
		return $this->tprefix!=""?$this->tprefix."_$magname":$magname;
	}

	/**
	 * Initialize attribute infos to be used during import
	 * @param array $cols : array of attribute names
	 */
	public function initAttrInfos($cols)
	{
		//Find product entity type
		$tname=$this->tablename("eav_entity_type");
		$this->prod_etype=$this->selectone("SELECT entity_type_id FROM $tname WHERE entity_type_code=?","catalog_product","entity_type_id");
		$gcols=array_unique(array_merge($cols,array("media_gallery")));
		//create statement parameter string ?,?,?.....
		$qcolstr=substr(str_repeat("?,",count($gcols)),0,-1);
		$tname=$this->tablename("eav_attribute");
		if($this->magversion="1.4.x")
		{
			$extra=$this->tablename("catalog_eav_attribute");
			//SQL for selecting attribute properties for all wanted attributes
			$sql="SELECT `$tname`.*,$extra.is_global FROM `$tname`
			LEFT JOIN $extra ON $tname.attribute_id=$extra.attribute_id
			WHERE  ($tname.attribute_code IN ($qcolstr)) AND (entity_type_id=$this->prod_etype)";		
		}
		else
		{
			$sql="SELECT `$tname`.* FROM `$tname` WHERE ($tname.attribute_code IN ($qcolstr)) AND (entity_type_id=$this->prod_etype)";
		}
		$result=$this->selectAll($sql,$gcols);

		//create an attribute code based array for the wanted columns
		foreach($result as $r)
		{
			$this->attrinfo[$r["attribute_code"]]=$r;
		}
		unset($result);
		//create a backend_type based array for the wanted columns
		//this will greatly help for optimizing inserts when creating attributes
		//since eav_ model for attributes has one table per backend type
		foreach($this->attrinfo as $k=>$a)
		{
			//do not index attributes that are not in header (media_gallery may have been inserted for other purposes)
			if(!in_array($k,$cols))
			{
				continue;
			}
			$bt=$a["backend_type"];
			if(!isset($this->attrbytype[$bt]))
			{
				$this->attrbytype[$bt]=array("data"=>array());

			}
			$this->attrbytype[$bt]["data"][]=$a;
		}
		//now add a fast index in the attrbytype array to store id list in a comma separated form
		foreach($this->attrbytype as $bt=>$test)
		{
			$idlist;
			foreach($test["data"] as $it)
			{
				$idlist[]=$it["attribute_id"];
			}
			$this->attrbytype[$bt]["ids"]=implode(",",$idlist);
		}

		/*now we have 2 index arrays
		 1. $this->attrinfo  which has the following structure:
		 key : attribute_code
		 value : attribute_properties
		 2. $this->attrbytype which has the following structure:
		 key : attribute backend type
		 value : array of :
		 data => array of attribute_properties ,one for each attribute that match
		 the backend type
		 ids => list of attribute ids of the backend type */
	}

	public function getAttrInfo($col)
	{
		return isset($this->attrinfo[$col])?$this->attrinfo[$col]:null;
	}

	/**
	 * retrieves attribute set id for a given attribute set name
	 * @param string $asname : attribute set name
	 */
	public function getAttributeSetId($asname)
	{

		if(!isset($this->attribute_sets[$asname]))
		{
			$tname=$this->tablename("eav_attribute_set");
			$asid=$this->selectone(
				"SELECT attribute_set_id FROM $tname WHERE attribute_set_name=? AND entity_type_id=?",
			array($asname,$this->prod_etype),
				'attribute_set_id');
			$this->attribute_sets[$asname]=$asid;
		}
		return $this->attribute_sets[$asname];
	}

	/**
	 * Retrieves product id for a given sku
	 * @param string $sku : sku of product to get id for
	 */
	public function getProductId($sku)
	{
		$tname=$this->tablename("catalog_product_entity");
		return $this->selectone(
		"SELECT entity_id FROM $tname WHERE sku=?",
		$sku,
		'entity_id');
	}

	/**
	 * creates a product in magento database
	 * @param array $item: product attributes as array with key:attribute name,value:attribute value
	 * @param int $asid : attribute set id for values
	 * @return : product id for newly created product
	 */
	public function createProduct($item,$asid)
	{
		$tname=$this->tablename('catalog_product_entity');
		$values=array($item['type'],$asid,$item['sku'],$this->prod_etype,null,strftime("%Y-%m-%d %H:%M:%S"));
		$sql="INSERT INTO `$tname`
				(`type_id`, 
				`attribute_set_id`,
	 			`sku`, 
	 			`entity_type_id`, 
	 			`entity_id`,
	 			`created_at`
	 			) 
	 			VALUES ( ?,?,?,?,?,?)";
		$lastid=$this->insert($sql,$values);
		return $lastid;
	}

	public function touchProduct($pid)
	{
		$tname=$this->tablename('catalog_product_entity');
		$this->update("UPDATE $tname SET updated_at=? WHERE entity_id=?",array(strftime("%Y-%m-%d %H:%M:%S"),$pid));
	}

	/**
	 * Get Option id for select attributes based on value
	 * @param int $attid : attribute id to find option id from value
	 * @param mixed $optval : value to get option id for
	 * @return : array of lines (should be as much as values found),"opvd"=>option_id for value on store 0,"opvs" option id for value on current store
	 */
	function getOptionsFromValues($attid,$store_id,$optvals)
	{
		$ovstr=substr(str_repeat("?,",count($optvals)),0,-1);
		$t1=$this->tablename('eav_attribute_option');
		$t2=$this->tablename('eav_attribute_option_value');
		$sql="SELECT optvals.option_id as opvs,optvals.value FROM $t2 as optvals";
		$sql.=" JOIN $t1 as opt ON opt.option_id=optvals.option_id AND opt.attribute_id=?";
		$sql.=" WHERE optvals.store_id=? AND optvals.value IN ($ovstr)";
		return $this->selectAll($sql,array_merge(array($attid,$store_id),$optvals));
	}


	/* create a new option entry for an attribute */
	function createOption($attid)
	{
		$t=$this->tablename('eav_attribute_option');
		$optid=$this->insert("INSERT INTO $t (attribute_id) VALUES (?)",$attid);
		return $optid;
	}
	/**
	 * Creates a new option value for an option entry for a store
	 * @param int $optid : option entry id
	 * @param int $store_id : store id to add value for
	 * @param mixed $optval : new option value to add
	 * @return : option id for new created value
	 */
	function  createOptionValue($optid,$store_id,$optval)
	{
		$t=$this->tablename('eav_attribute_option_value');
		$optval_id=$this->insert("INSERT INTO $t (option_id,store_id,value) VALUES (?,?,?)",array($optid,$store_id,$optval));
		return $optval_id;
	}


	function getOptionIds($attid,$storeid,$values)
	{
		$optids=array();
		$existing=$this->getOptionsFromValues($attid,$storeid,$values);
		$exvals=array();
		foreach($existing as $optdesc)
		{
			$exvals[]=$optdesc["value"];
		}
		$new=array_merge(array_diff($values,$exvals));
		if($storeid==0)
		{
			foreach($new as $nval)
			{
				$row=array("opvs"=>$this->createOption($attid),"value"=>$nval);
				$this->createOptionValue($row["opvs"],$storeid,$nval);
				$existing[]=$row;
			}
			$this->cacheOptIds($attid,$existing);

		}
		else
		{
				
			$brows=$this->getCachedOptIds($attid);
			foreach($existing as $ex)
			{
				array_shift($brows);
			}
			for($i=0;$i<count($new);$i++)
			{
				$row=$brows[$i];
				$this->createOptionValue($row["opvs"],$storeid,$new[$i]);
				$existing[]=$row;
			}
		}
		$optids=array();
		foreach($existing as $row)
		{
			$optids[]=$row["opvs"];
		}
		unset($existing);
		unset($exvals);
		return $optids;

	}

	function cacheOptIds($attid,$row)
	{
		$this->_optidcache[$attid]=$row;
	}

	function getCachedOptIds($attid)
	{
		return $this->_optidcache[$attid];
	}


	/**
	 * returns tax class id for a given tax class value
	 * @param $tcvalue : tax class value
	 */
	public function getTaxClassId($tcvalue)
	{
		$t=$this->tablename('tax_class');
		return $this->selectone("SELECT class_id FROM $t WHERE class_name=?",$tcvalue,"class_id");
	}

	/**
	 * imageInGallery
	 * @param int $pid  : product id to test image existence in gallery
	 * @param string $imgname : image file name (relative to /products/media in magento dir)
	 * @return bool : if image is already present in gallery for a given product id
	 */
	public function getImageId($pid,$attid,$imgname)
	{
		$t=$this->tablename('catalog_product_entity_media_gallery');
		$imgid=$this->selectone("SELECT value_id FROM $t WHERE value=? AND entity_id=? AND attribute_id=?" ,
		array($imgname,$pid,$attid),
								'value_id');
		if($imgid==null)
		{
			// insert image in media_gallery
			$sql="INSERT INTO $t
				(attribute_id,entity_id,value)
				VALUES
				(?,?,?)";

			$imgid=$this->insert($sql,array($attid,$pid,$imgname));
		}
		return $imgid;
	}

	/**
	 * reset product gallery
	 * @param int $pid : product id
	 */
	public function resetGallery($pid,$storeid,$attid)
	{
		$tgv=$this->tablename('catalog_product_entity_media_gallery_value');
		$tg=$this->tablename('catalog_product_entity_media_gallery');
		$sql="DELETE emgv,emg FROM `$tgv` as emgv JOIN `$tg` AS emg ON emgv.value_id = emg.value_id AND emgv.store_id=?
		WHERE emg.entity_id=? AND emg.attribute_id=?";
		$this->delete($sql,array($storeid,$pid,$attid));

	}
	/**
	 * adds an image to product image gallery only if not already exists
	 * @param int $pid  : product id to test image existence in gallery
	 * @param array $attrdesc : product attribute description
	 * @param string $imgname : image file name (relative to /products/media in magento dir)
	 */
	public function addImageToGallery($pid,$storeid,$attrdesc,$imgname,$excluded=false)
	{

		$vid=$this->getImageId($pid,$this->attrinfo["media_gallery"]["attribute_id"],$imgname);
		$tg=$this->tablename('catalog_product_entity_media_gallery');
		$tgv=$this->tablename('catalog_product_entity_media_gallery_value');
		#get maximum current position in the product gallery
		$sql="SELECT MAX( position ) as maxpos
				 FROM $tgv AS emgv
				 JOIN $tg AS emg ON emg.value_id = emgv.value_id AND emg.entity_id = ?
				 WHERE emgv.store_id=?
		 		 GROUP BY emg.entity_id";
		$pos=$this->selectone($sql,array($pid,$storeid),'maxpos');
		$pos=($pos==null?0:$pos+1);
		#insert new value (ingnore duplicates)
		$sql="INSERT IGNORE INTO $tgv
			(value_id,store_id,position,disabled)
			VALUES(?,?,?,?)";	
		$data=array($vid,$storeid,$pos,$excluded?1:0);
		$this->insert($sql,$data);
		unset($data);
	}

	public function cleanImageName($fname)
	{
		$cname=preg_replace("/%[0-9][0-9|A-F]/","_",$fname);
		return $cname;
	}
	/**
	 * copy image file from source directory to
	 * product media directory
	 * @param $imgfile : name of image file name in source directory
	 * @return : name of image file name relative to magento catalog media dir,including leading
	 * directories made of first char & second char of image file name.
	 */
	public function copyImageFile($imgfile)
	{
		$bimgfile=$this->cleanImageName(basename($imgfile));
		//source file exists
		$i1=$bimgfile[0];
		$i2=$bimgfile[1];
		$l1d="$this->magdir/media/catalog/product/$i1";
		$l2d="$l1d/$i2";
		$te="$l2d/$bimgfile";
		$fname="$this->imgsourcedir/$bimgfile";

		/* test if imagefile comes from export */
		if(!file_exists("$te"))
		{
			$exists=false;
			if(preg_match("|.*?://.*|",$imgfile))
			{
				$fname=$imgfile;
				$h=@fopen($fname,"r");
				if($h!==false)
				{
					$exists=true;
					fclose($h);
				}
				unset($h);
			}
			else
			{
				$exists=file_exists($fname);
			}
			if(!$exists)
			{
				$this->log("$fname not found, skipping image","warning");
				return false;
			}
			/* test if 1st level product media dir exists , create it if not */
			if(!file_exists("$l1d"))
			{
				mkdir($l1d,0777);
			}
			/* test if 2nd level product media dir exists , create it if not */
			if(!file_exists("$l2d"))
			{
				mkdir($l2d,0777);
			}

			/* test if image already exists ,if not copy from source to media dir*/
			if(!file_exists("$l2d/$bimgfile"))
			{

				if(!@copy($fname,"$l2d/$bimgfile"))
				{
					$errors= error_get_last();
						
					$this->log("error copying $l2d/$bimgfile : ${$errors["type"]},${$errors["message"]}","warning");
		}
	}
}
/* return image file name relative to media dir (with leading / ) */
return "/$i1/$i2/$bimgfile";
	}



	/**
	 * Create product attribute from values for a given product id
	 * @param $pid : product id to create attribute values for
	 * @param $item : attribute values in an array indexed by attribute_code
	 */
	public function createAttributes($pid,$item)
	{
		/**
		 * get all store ids
		 */
		if(isset($item["store"]))
		{
			$bstore_ids=$this->getStoreIds($item["store"]);
			$bstore_ids=array_unique(array_merge($this->_dstore,$bstore_ids));
		}
		else
		{
			$bstore_ids=array(0,1);
		}
		//websites related store_ids
		$ws_store_ids=$this->getWebsitesStoreIds($item["websites"]);
		$bstore_ids=array_unique(array_merge($bstore_ids,$ws_store_ids));
		//set pid for attribute handlers, useful for cache effects
		foreach($this->_attributehandlers as $ah)
		{
			$ah->setCurrentPid($pid);
		}
			
		/* now is the interesring part */
		/* iterate on attribute backend type index */
		foreach($this->attrbytype as $tp=>$a)
		{
			/* for static types, do not insert into attribute tables */
			if($tp=="static")
			{
				continue;
			}

			//table name for backend type data
			$cpet=$this->tablename("catalog_product_entity_$tp");
			//data table for inserts
			$data=array();
			//inserts to perform on backend type eav
			$inserts=array();
			//use reflection to find special handlers
			$handler="handle".ucfirst($tp)."Attribute";
				
			//iterate on all attribute descriptions for the given backend type
			foreach($a["data"] as $attrdesc)
			{
				//by default, we will perform an insetion
				$insert=true;
				//get attribute id
				$attid=$attrdesc["attribute_id"];
				//get attribute value in the item to insert based on code
				$ivalue=$item[$attrdesc["attribute_code"]];

					
				$store_ids=array();
				switch($attrdesc["is_global"])
				{
					//store_view scope
					case 0:
						//use all store values from item
						$store_ids=$bstore_ids;
						break;
						//global scope
					case 1:
						//all values impact store 0
						$store_ids=array(0);
						break;
					case 2:
						//force default store
						$store_ids=array_unique(array_merge($this->_dstore,$ws_store_ids));
				}
				$deletes=array();

				foreach($store_ids as $store_id)
				{
					$ovalue=$ivalue;

					foreach($this->_attributehandlers as $ah)
					{
						if(method_exists($ah,$handler))
						{
							$ovalue=$ah->$handler($store_id,$ivalue,$attrdesc);
						}
					}
						
					if($ovalue=="__MAGMI_DELETE__")
					{
						$deletes[]=array($attid,$store_id,$pid);
						$ddata=array($this->prod_etype,$attid,$store_id,$pid);
						$sql="DELETE FROM $cpet WHERE entity_type_id=? AND attribute_id=? AND store_id=? AND entity_id=?";
						$this->delete($sql,$ddata);
						unset($ddata);
					}
					else
					if($ovalue!==false)
					{
						$inserts[]="(?,?,?,?,?)";
						$data[]=$this->prod_etype;
						$data[]=$attid;
						$data[]=$store_id;
						$data[]=$pid;
						$data[]=$ovalue;
					}
				}
			}
			unset($store_ids);
			if(!empty($inserts))
			{
				//now perform insert for all values of the the current backend type in one
				//single insert
				$sql="INSERT INTO $cpet
			(`entity_type_id`, `attribute_id`, `store_id`, `entity_id`, `value`)
			VALUES ";
				$sql.=implode(",",$inserts);
				//this one taken from mysql log analysis of magento import
				//smart one :)
				$sql.=" ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)";
				$this->insert($sql,$data);
			}
			else
			if(!empty($deletes))
			{
				unset($deletes);
			}
			else
			{
				if(!$this->_same)
				{
					$this->log("No $tp Attributes created for sku ".$item["sku"],"warning");
				}
			}
			unset($data);
			unset($inserts);
		}
	}

	/**
	 * Clear all products from catalog
	 */
	public function clearProducts()
	{
		$this->log("Clearing product list....","reset");
		$sql="SET FOREIGN_KEY_CHECKS = 0";
		$this->exec_stmt($sql);
		$tables=array("catalog_product_bundle_option",
					  "catalog_product_bundle_option_value",
					  "catalog_product_bundle_selection",
					  "catalog_product_entity_datetime",
					  "catalog_product_entity_decimal",
					  "catalog_product_entity_gallery",
					  "catalog_product_entity_int",
					  "catalog_product_entity_media_gallery",
					  "catalog_product_entity_media_gallery_value",
					  "catalog_product_entity_text",
					  "catalog_product_entity_tier_price",
					  "catalog_product_entity_varchar",
					  "catalog_product_link",
					  "catalog_product_link_attribute",
					  "catalog_product_link_attribute_decimal",
					  "catalog_product_link_attribute_int",
					  "catalog_product_link_attribute_varchar",
					  "catalog_product_link_type",
					  "catalog_product_option",
					  "catalog_product_option_price",
					  "catalog_product_option_title",
					  "catalog_product_option_type_price",
					  "catalog_product_option_type_title",
					  "catalog_product_option_type_value",		
					  "catalog_product_super_attribute_label",
					  "catalog_product_super_attribute_pricing",
					  "catalog_product_super_attribute",
					  "catalog_product_super_link",
					  "catalog_product_enabled_index",
					  "catalog_product_website",
					  "catalog_category_product_index",
					  "catalog_category_product",
					  "cataloginventory_stock_item",
					  "cataloginventory_stock_status",
					  "cataloginventory_stock");


		foreach($tables as $table)
		{
			$this->exec_stmt("TRUNCATE TABLE `".$this->tablename($table)."`");
		}

		$sql="INSERT INTO `".$this->tablename("catalog_product_link_type")."` (`link_type_id`,`code`) VALUES (1,'relation'),(2,'bundle'),(3,'super'),(4,'up_sell'),(5,'cross_sell')";
		$this->insert($sql);
		$sql="INSERT INTO `".$this->tablename("catalog_product_link_attribute")."` (`product_link_attribute_id`,`link_type_id`,`product_link_attribute_code`,`data_type`) VALUES (1,2,'qty','decimal'),(2,1,'position','int'),(3,4,'position','int'),(4,5,'position','int'),(6,1,'qty','decimal'),(7,3,'position','int'),(8,3,'qty','decimal')";
		$this->insert($sql);
		$sql="INSERT INTO `".$this->tablename("cataloginventory_stock")."`(`stock_id`,`stock_name`) VALUES (1,'Default')";
		$this->insert($sql);
		$sql="TRUNCATE TABLE `".$this->tablename("catalog_product_entity")."`;\n";
		$this->insert($sql);
		$optv=$this->tablename("eav_attribute_option_value");
		$opt=$this->tablename("eav_attribute_option");
		$et=$this->tablename("eav_attribute");
		$sql="SET FOREIGN_KEY_CHECKS = 1";
		//clearing all product options
		$sql="DELETE $opt FROM $opt
			JOIN $et ON $et.attribute_id=$opt.attribute_id AND $et.entity_type_id=4";
		$this->exec_stmt($sql);
		//clearing all option values for products
		$sql="DELETE $optv FROM $optv
		LEFT JOIN $opt ON $optv.option_id=$opt.option_id
		WHERE $opt.option_id IS NULL";
		$this->exec_stmt($sql);
		//reinit auto_increment
		$sql="ALTER TABLE $opt auto_increment=3";
		$this->exec_stmt($sql);
		$sql="ALTER TABLE $optv auto_increment=3";
		$this->exec_stmt($sql);
		$this->log("OK","reset");
	}


	/**
	 * update product stock
	 * @param int $pid : product id
	 * @param array $item : attribute values for product indexed by attribute_code
	 */
	public function updateStock($pid,$item)
	{
		if(!$this->reset && $this->mode!=="update")
		{
			$tcsi=$this->tablename('cataloginventory_stock_item');
			$tcss=$this->tablename('cataloginventory_stock_status');
			$sqls=array("DELETE FROM `$tcsi` WHERE product_id=?",
				 "DELETE FROM `$tcss` WHERE product_id=?");

			foreach($sqls as $sql)
			{
				$this->delete($sql,$pid);
			}
		}
		$csit=$this->tablename("cataloginventory_stock_item");
		$css=$this->tablename("cataloginventory_stock_status");
		$stockid=1; //Default stock id , not found how to relate product to a specific stock id
		$is_in_stock=isset($item["is_in_stock"])?$item["is_in_stock"]:($item["qty"]>0?1:0);
		if(!$is_in_stock && $item["qty"]>0)
		{
			$is_in_stock=1;
		}
		if($this->mode!=="update")
		{
				
			$lsdate=nullifempty(isset($item["low_stock_date"])?$item["low_stock_date"]:"");
			$sql="INSERT INTO `$csit`
			(`product_id`, 
 			 `stock_id`,
  			  `qty`, 
  			  `is_in_stock`, 
  			  `low_stock_date`,
   			 `stock_status_changed_automatically`) 
			VALUES (?,?,?,?,?,?) 
			ON DUPLICATE KEY UPDATE 
			`qty`=VALUES(`qty`),
			`is_in_stock`=VALUES(`is_in_stock`),
			`low_stock_date`=VALUES(`low_stock_date`),
			`stock_status_changed_automatically`=VALUES(`stock_status_changed_automatically`)";
			$data=array($pid,$stockid,$item["qty"],$is_in_stock,$lsdate,1);
			$this->insert($sql,$data);
			$sql="INSERT INTO `$css` (`website_id`,`product_id`,`stock_id`,`qty`,`stock_status`)";
			$wscodes=explode(",",$item["websites"]);
			unset($data);
			$data=array();
			$inserts=array();
				
			//for each website code
			foreach($wscodes as $wscode)
			{
				$inserts[]="(?,?,?,?,?)";
				$data[]=$this->website_ids[$wscode];
				$data[]=$pid;
				$data[]=$stockid;
				$data[]=$item["qty"];
				$data[]=1;
			}
			$sql.=" VALUES ".implode(",",$inserts);
			$this->insert($sql,$data);
			unset($data);
			unset($inserts);
		}
		else
		{
			$data=array();
			//Fast stock update
			$data[]=$item["qty"];
			$data[]=$is_in_stock;
			$data[]=$pid;
			$sql="UPDATE `$csit` SET qty=?,is_in_stock=? WHERE product_id=?";
			$this->update($sql,$data);
			$sql="UPDATE `$css` SET qty=? WHERE product_id=?";
			unset($data);
			$data=array($item["qty"],$pid);
			$this->update($sql,$data);
			unset($data);
		}
		unset($data);
		unset($inserts);
	}
	/**
	 * assign categories for a given product id from values
	 * categories should already be created & csv values should be as the ones
	 * given in the magento export (ie:  comma separated ids, minus 1,2)
	 * @param int $pid : product id
	 * @param array $item : attribute values for product indexed by attribute_code
	 */
	public function assignCategories($pid,$item)
	{
		$cce=$this->tablename("catalog_category_entity");
		$catids=explode(",",$item["category_ids"]);
		//build possible path list
		$sql="SELECT entity_id FROM $cce
			  WHERE entity_id IN (".$item['category_ids'].")";
		$ccpt=$this->tablename("catalog_category_product");
		#if we did not reset products
		if(!$this->reset)
		{
			#remove category assignment of current product
			#only for current store
			$sql="DELETE $ccpt.*
			FROM $ccpt
			JOIN $cce ON $cce.entity_id=$ccpt.category_id
			WHERE product_id=?";
			$this->delete($sql,$pid);
		}

		$inserts=array();
		$data=array();
		foreach($catids as $catid)
		{
			$inserts[]="(?,?)";
			$data[]=$catid;
			$data[]=$pid;
		}
		#create new category assignment for products, if multi store with repeated ids
		#ignore duplicates
		$sql="INSERT IGNORE INTO $ccpt (`category_id`,`product_id`)
			 VALUES ";
		$sql.=implode(",",$inserts);
		$this->insert($sql,$data);
		unset($data);
		unset($inserts);
	}



	/**
	 * set website of product if not exists
	 * @param int $pid : product id
	 * @param array $item : attribute values for product indexed by attribute_code
	 */
	public function updateWebSites($pid,$item)
	{
		$cpst=$this->tablename("catalog_product_website");
		//get all website codes for item (separated by , in magento export format)
		$wscodes=explode(",",$item["websites"]);
		$data=array();
		$inserts=array();
		//for each website code
		foreach($wscodes as $wscode)
		{
			//new value couple to insert
			$inserts[]="(?,?)";
			//product id
			$data[]=$pid;
			//website id
			$data[]=$this->website_ids[$wscode];
		}
		//associate product with all websites in a single multi insert (use ignore to avoid duplicates)
		$sql="INSERT IGNORE INTO `$cpst` (`product_id`, `website_id`)
					VALUES ".implode(",",$inserts);
		$this->insert($sql,$data);
		unset($data);
		unset($inserts);
	}



	public function callPlugins($types,$step,&$item,$params)
	{
		foreach($types as $type)
		{
			$meth="call".ucfirst($type);
			if(!$this->$meth($step,$item,$params))
			{
				return false;
			}
		}
		return true;
	}

	public function callGeneral($step)
	{
		$methname=$step;
		foreach($this->_activeplugins["general"] as $gp)
		{
			if(method_exists($gp,$methname))
			{
				if(!$gp->$methname())
				{
					return false;
				}
			}
		}
		return true;
			
	}

	public function callProcessors($step,&$data=null,$params=null,$prefix="")
	{
		$methname=$prefix.ucfirst($step);
		foreach($this->_activeplugins["processors"] as $ip)
		{
			if(method_exists($ip,$methname))
			{
				if($prefix=="processItem" || $prefix=="process")
				{				
					if(!$ip->$methname($data,$params))
					{
						return false;
					}
				}
				else
				{
					$ip->$methname($params);
				}
			}
		}
		return true;

	}

	public function clearOptCache()
	{
		unset($this->_optidcache);
		$this->_optidcache=array();
	}
	public function onNewSku($sku)
	{
		$this->clearOptCache();
		//only assign values to store 0 by default in create mode for new sku
		//for store related options
		if($this->mode=="create")
		{
			$this->_dstore=array(0);
		}
		else
		{
			$this->_dstore=array();
		}
		$this->_same=false;
	}

	public function onSameSku($sku)
	{
		unset($this->_dstore);
		$this->_dstore=array();
		$this->_same=true;
	}

	public function getItemIds($item)
	{
		$sku=$item["sku"];
		if($sku!=$this->_curitemids["sku"])
		{
			$this->_curitemids["sku"]=$sku;
			//first get product id
			$this->_curitemids["pid"]=$this->getProductId($sku);
			if($this->_mode!=="update")
			{
				$this->_curitemids["asid"]=$this->getAttributeSetId($item["attribute_set"]);
			}
			$this->onNewSku($sku);
			//retrieve attribute set from given name
			//if not in cache, add to cache
		}
		else
		{
			$this->onSameSku($sku);
		}
		return $this->_curitemids;
	}
	/**
	 * full import workflow for item
	 * @param array $item : attribute values for product indexed by attribute_code
	 */
	public function importItem($item)
	{
		if(Magmi_StateManager::getState()=="canceled")
		{
			exit();
		}
		//first step

		if(!$this->callProcessors("beforeId",$item,null,"processItem"))
		{
			return;
		}
		$itemids=$this->getItemIds($item);
		$pid=$itemids["pid"];
		$isnew=false;
		if(!isset($pid))
		{
			//if not found & mode !=update
			if($this->mode!=='update')
			{
				$asid=$itemids["asid"];
				$pid=$this->createProduct($item,$asid);
				$this->_curitemids["pid"]=$pid;
				$isnew=true;
			}
			else
			{
				//mode is update, do nothing
				$this->log("skipping unknown sku:{$item["sku"]} - update mode set","skip");
				return;
			}
		}
		try
		{
			if(!$this->callProcessors("afterId",$item,array("product_id"=>$pid,"new"=>$isnew),"processItem"))
			{
				return;
			}
				
			//begin transaction
			$this->beginTransaction();
				
			//create new ones
			$this->createAttributes($pid,$item);
			if(!testempty($item,"category_ids"))
			{
				//assign categories
				$this->assignCategories($pid,$item);
			}
			//update websites
			if(!testempty($item,"websites"))
			{
				$this->updateWebSites($pid,$item);
			}
			if(!testempty($item,"qty") && !$this->_same)
			{
				//update stock
				$this->updateStock($pid,$item);
			}

			$this->touchProduct($pid);
			//ok,we're done
			$this->commitTransaction();
		}
		catch(Exception $e)
		{
			$this->callProcessors("exception",$item,array("exception"=>$e),"processItem");
			$this->log($e->getMessage()." - {$this->_laststmt->queryString}","error");
			//if anything got wrong, rollback
			$this->rollbackTransaction();
		}
	}

	public function getProperties()
	{
		return $this->_props;
	}

	/**
	 * count lines of csv file
	 * @param string $csvfile filename
	 */
	public function lookup()
	{
		$t0=microtime(true);

		$count=$this->datasource->getRecordsCount();
		$t1=microtime(true);
		$time=$t1-$t0;
		$this->log("$count:$time","lookup");
		return $count;
	}

	/**
	 * main import function
	 * @param string $csvfile : csv file name to import
	 * @param bool $reset : destroy all products before import
	 */

	public function getParam($params,$pname,$default=null)
	{
		return isset($params[$pname])?$params[$pname]:$default;
	}

	public function createGeneralPlugins($params)
	{
		foreach($this->_pluginclasses["general"] as $giclass)
		{
			$gi=new $giclass();
			$gi->pluginInit($this,$params);
			$this->_activeplugins["general"][]=$gi;
		}

	}
	public function createItemProcessors($params)
	{
		foreach($this->_pluginclasses["processors"] as $ipclass)
		{

			$ip=new $ipclass();
			$ip->pluginInit($this,$params);
			$this->_activeplugins["processors"][]=$ip;
		}
	}

	public function createDatasource($params)
	{
		$dsclass=$this->datasource_class;
		$this->datasource=new $dsclass();
		$this->datasource->pluginInit($this,$params);
	}

	public function import($params)
	{
		$this->init();
		$reset=$this->getParam($params,"reset",false);
		$mode=$this->getParam($params,"mode","update");
		//initializing datasource
		try
		{
				
			$this->log("Magento Mass Importer by dweeves - version:".MagentoMassImporter::$version,"title");
			$this->log("step:".$this->getProp("GLOBAL","step",100),"step");
			//initialize db connectivity
			$this->connectToMagento();
			$this->createDatasource($params);
			$this->createGeneralPlugins($params);
			$this->datasource->beforeImport();
			$this->callGeneral("beforeImport");
			$this->registerAttributeHandler("Magmi_DefaultAttributeHandler");
				
			$nitems=$this->lookup();
			Magmi_StateManager::setState("running");
			//store reset flag
			$this->reset=$reset;
			$this->mode=$mode;
			//if reset
			if($this->reset)
			{
				//clear all products
				$this->clearProducts();
			}
			if($nitems>0)
			{
				//initialize website id cache
				$this->initWebSites();
				//intialize store id cache
				$this->initStores();
				setLocale(LC_COLLATE,"fr_FR.UTF-8");
				$this->datasource->startImport();
					
				//initializing item processors
				$this->createItemProcessors($params);
				$this->callProcessors("importStart",$nodata,null,"on");
			
				$cols=$this->datasource->getColumnNames();
				$this->log(count($cols),"columns");
				$this->callProcessors("columnList",$cols,null,"process");
				//initialize attribute infos & indexes from column names
					
				$this->initAttrInfos($cols);
				//counter
				$this->current_row=0;
				//start time
				$tstart=microtime(true);
				//differential
				$tdiff=$tstart;
				//intermediary report step
				$this->initDbqStats();
				$mstep=$this->getProp("GLOBAL","step",100);
				if(!isset($mstep))
				{
					$mstep=100;
				}
				//read each line
				$lastrec=0;
				$lastdbtime=0;
				while(($item=$this->datasource->getNextRecord())!==false)
				{
					//counter
					$this->current_row++;

					try
					{
						if(is_array($item) && count($item)>0)
						{
							//import item
							$this->importItem($item);
						}
						else
						{
							$this->log("ERROR - RECORD #$this->current_row - INVALID RECORD","error");
						}
						//intermediary measurement
						if($this->current_row%$mstep==0)
						{
							$tend=microtime(true);
							$this->log($this->current_row." - ".($tend-$tstart)." - ".($tend-$tdiff),"itime");
							$this->log($this->_nreq." - ".($this->_indbtime)." - ".($this->_indbtime-$lastdbtime)." - ".($this->_nreq-$lastrec),"dbtime");
							$lastrec=$this->_nreq;
							$lastdbtime=$this->_indbtime;
							$tdiff=microtime(true);
						}
					}
					catch(Exception $e)
					{
						$this->log("ERROR - RECORD #$this->current_row - ".$e->getMessage(),"error");
					}
					unset($item);
				}

				$this->datasource->endImport();
				$tend=microtime(true);
				$this->log($this->current_row." - ".($tend-$tstart)." - ".($tend-$tdiff),"itime");
				$this->log($this->_nreq." - ".($this->_indbtime)." - ".($this->_indbtime-$lastdbtime)." - ".($this->_nreq-$lastrec),"dbtime");
			}
			$this->disconnectFromMagento();
			$this->datasource->afterImport();
			$this->callGeneral("afterImport");
			$this->callProcessors("importEnd",$nodata,null,"on");
			
			$this->log("Import Ended","end");
			Magmi_StateManager::setState("idle");
				
		}
		catch(Exception $e)
		{
			$this->log($e->getMessage(),"error");
			$this->log("Import Ended","end");
			Magmi_StateManager::setState("idle");
		}
	}


}