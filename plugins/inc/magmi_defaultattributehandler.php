<?php
class Magmi_DefaultAttributeItemProcessor extends Magmi_ItemProcessor
{
	
	public function initialize($params)
	{
		$this->registerAttributeHandler($this);	
	}
	
	public function getPluginInfo()
	{
		return array(
            "name" => "Standard Attribute Import",
            "author" => "Dweeves",
            "version" => "1.0.0"
            );
	}
	
	/**
	 * attribute handler for decimal attributes
	 * @param int $pid	: product id
	 * @param int $ivalue : initial value of attribute
	 * @param array $attrdesc : attribute description
	 * @return mixed : false if no further processing is needed,
	 * 					string (magento value) for the decimal attribute otherwise
	 */
	public function handleDecimalAttribute($pid,$item,$storeid,$attrcode,$attrdesc,$ivalue)
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
	public function handleDatetimeAttribute($pid,$item,$storeid,$attrcode,$attrdesc,$ivalue)
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
	public function handleIntAttribute($pid,$item,$storeid,$attrcode,$attrdesc,$ivalue)
	{
		$ovalue=$ivalue;
		$attid=$attrdesc["attribute_id"];
		//if we've got a select type value
		if($attrdesc["frontend_input"]=="select")
		{
			//we need to identify its type since some have no options
			switch($attrdesc["source_model"])
			{
				//if its status, default to 2 if not correcly mapped
				case "catalog/product_status":
					if(!is_int($ivalue)){
						$ovalue=2;
					}
					break;
				//do not create options for boolean values tagged as select ,default to 0 if not correcly mapped
				case "eav/entity_attribute_source_boolean":
					if(!is_int($ivalue)){
						$ovalue=0;
					}
					break;
				//if visibility no options either,default to 4 if not correctly mapped
				case "catalog/product_visibility":
					if(!is_int($ivalue)){
						$ovalue=4;
					}
					
					break;
					//if it's tax_class, get tax class id from item value
				case "tax/class_source_product":
					$ovalue=$this->getTaxClassId($ivalue);
					break;
					//otherwise, standard option behavior
					//get option id for value, create it if does not already exist
					//do not insert if empty
				default:
					if($ivalue=="" && $this->getMode()=="update")
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
	public function handleVarcharAttribute($pid,$item,$storeid,$attrcode,$attrdesc,$ivalue)
	{

		if($storeid!==0 && empty($ivalue) && $this->_mmi->mode=="create")
		{
			return false;
		}

		$ovalue=$ivalue;
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
			$oids=$this->getOptionIds($attid,$storeid,$multiselectvalues);
			$ovalue=implode(",",$oids);
			unset($oids);
		}
		return $ovalue;
	}

}
