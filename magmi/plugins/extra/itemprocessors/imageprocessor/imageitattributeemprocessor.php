<?php
class ImageAttributeItemProcessor extends Magmi_ItemProcessor
{

	protected $forcename=null;
	protected $magdir=null;
	protected $imgsourcedir=null;
	protected $errattrs=array();
	
	public function initialize($params)
	{
		//declare current class as attribute handler
		$this->registerAttributeHandler($this,array("frontend_input:(media_image|media_gallery)"));
		$this->magdir=$this->getMagentoDir();
		$this->imgsourcedir=realpath($this->magdir."/".$this->getParam("IMG:sourcedir"));
		if(!file_exists($this->imgsourcedir))
		{
			$this->imgsourcedir=$this->getParam("IMG:sourcedir");
		}
		$this->forcename=$this->getParam("IMG:renaming",$params);
		foreach($params as $k=>$v)
		{
			if(preg_match_all("/^IMG_ERR:(.*)$/",$k,$m))
			{
				$this->errattrs[$m[1][0]]=$params[$k];
			}
		}	
	}

	public function getPluginInfo()
	{
		return array(
            "name" => "Image attributes processor",
            "author" => "Dweeves",
            "version" => "0.0.4"
            );
	}
	public function handleGalleryTypeAttribute($pid,&$item,$storeid,$attrcode,$attrdesc,$ivalue)
	{
		//do nothing if empty
		if($ivalue=="")
		{
			return false;
		}
		$this->resetGallery($pid,$storeid,$attid);
		//use ";" as image separator
		$images=explode(";",$ivalue);
		//for each image
		foreach($images as $imagefile)
		{
			//copy it from source dir to product media dir
			$imagefile=$this->copyImageFile($imagefile,$item,array("store"=>$storeid,"attr_code"=>$attrcode));
			if($imagefile!==false)
			{
				//add to gallery
				$vid=$this->addImageToGallery($pid,$storeid,$attrdesc,$imagefile);
			}
		}
		unset($images);
		//we don't want to insert after that
		$ovalue=false;
	}

	public function handleImageTypeAttribute($pid,&$item,$storeid,$attrcode,$attrdesc,$ivalue)
	{
		//do nothing if empty
		if($ivalue=="")
		{
			return false;
		}
		//else copy image file
		$imagefile=$this->copyImageFile($ivalue,$item,array("store"=>$storeid,"attr_code"=>$attrcode));
		$ovalue=$imagefile;
		//add to gallery as excluded
		if($imagefile!==false)
		{
			$vid=$this->addImageToGallery($pid,$storeid,$attrdesc,$imagefile,true);
		}
		return $ovalue;
	}


	public function handleVarcharAttribute($pid,&$item,$storeid,$attrcode,$attrdesc,$ivalue)
	{

		//if it's a gallery
		switch($attrdesc["frontend_input"])
		{
			case "media_gallery":
				$ovalue=$this->handleGalleryTypeAttribute($pid,$item,$storeid,$attrcode,$attrdesc,$ivalue);
				break;
			case "media_image":
				$ovalue=$this->handleImageTypeAttribute($pid,$item,$storeid,$attrcode,$attrdesc,$ivalue);
				break;
			default:
				$ovalue="__MAGMI_UNHANDLED__";
		}
		return $ovalue;
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
		$gal_attinfo=$this->getAttrInfo("media_gallery");
		$vid=$this->getImageId($pid,$gal_attinfo["attribute_id"],$imgname);
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

	public function parsename($info,$item,$extra)
	{
		while(preg_match("|\{item\.(.*?)\}|",$info,$matches))
		{
			foreach($matches as $match)
			{
				if($match!=$matches[0])
				{
					if(isset($item[$match]))
					{
						$rep=$item[$match];
					}
					else
					{
						$rep="";
					}
					$info=str_replace($matches[0],$rep,$info);
				}
			}
		}
		while(preg_match("|\{magmi\.(.*?)\}|",$info,$matches))
		{
			foreach($matches as $match)
			{
				if($match!=$matches[0])
				{
					if(isset($extra[$match]))
					{
						$rep=$extra[$match];
					}
					else
					{
						$rep="";
					}
					$info=str_replace($matches[0],$rep,$info);
				}
			}
		}
		
		return $info;
	}
	
	public function getPluginParams($params)
	{
		$pp=array();
		foreach($params as $k=>$v)
		{
			if(preg_match("/^IMG(_ERR)?:.*$/",$k))
			{
				$pp[$k]=$v;
			}
		}	
		return $pp;
	}
	
	public function fillErrorAttributes(&$item)
	{
		foreach($this->errattrs as $k=>$v)
		{
			$this->addExtraAttribute($k);
			$item[$k]=$v;
		}
	}
	public function getTargetName($fname,$item,$extra)
	{
		$fname=urlencode($fname);
		$cname=strtolower(preg_replace("/%[0-9][0-9|A-F]/","_",$fname));
		$m=preg_match("/(.*?)\.(jpg|png|gif)$/i",$cname,$matches);	
		if(isset($this->forcename) && $this->forcename!="")
		{
			$extra["imagename"]=$cname;
			$extra["imagename.ext"]=$matches[2];
			$extra["imagename.noext"]=$matches[1];
			$cname=$this->parsename($this->forcename,$item,$extra);
		}
		return $cname;
	}
	/**
	 * copy image file from source directory to
	 * product media directory
	 * @param $imgfile : name of image file name in source directory
	 * @return : name of image file name relative to magento catalog media dir,including leading
	 * directories made of first char & second char of image file name.
	 */
	public function copyImageFile($imgfile,&$item,$extra)
	{
		$bimgfile=$this->getTargetName(basename($imgfile),$item,$extra);
		//source file exists
		$i1=$bimgfile[0];
		$i2=$bimgfile[1];
		$l1d="$this->magdir/media/catalog/product/$i1";
		$l2d="$l1d/$i2";
		$te="$l2d/$bimgfile";
		
		$result="/$i1/$i2/$bimgfile";
		/* test if imagefile comes from export */
		if(!file_exists("$te") || $this->getParam("IMG:writemode")=="override")
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
				$fname=realpath($this->imgsourcedir."/".basename($imgfile));
				$exists=file_exists($fname);
			}
			if(!$exists)
			{
				$this->log("$fname not found, skipping image","warning");
				$this->fillErrorAttributes($item);
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
					$this->fillErrorAttributes($item);
					$this->log("error copying $l2d/$bimgfile : ${$errors["type"]},${$errors["message"]}","warning");
					return false;
				}
			}
		}
		/* return image file name relative to media dir (with leading / ) */
		return $result;
	}

	public function processColumnList(&$cols,$params=null)
	{
		//automatically add modified attributes if not found in datasource
		$cols=array_unique(array_merge(array_keys($this->errattrs),$cols));
		return true;
	}
	

}