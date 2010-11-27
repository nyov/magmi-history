<div class="plugin_description">
This plugin is the new image importing feature of magmi.
It enables image renaming from input value with some dynamic values coming from input values.
</div>

<div class="formline">
<span>Read local images from:</span><input type="text"  name="IMG:sourcedir" size="80" value="<?php echo $this->getParam("IMG:sourcedir","media/import")?>"></input>
<div class="fieldinfo">if relative path is used, it means "relative to magento base dir",absolute path is used as is</div>
</div>
<div class="formline ifield">
<span>Image Renaming:</span><input type="text"  name="IMG:renaming" size="80" value="<?php echo $this->getParam("IMG:renaming")?>"></input>
<div class="fieldhelp"></div>
<div class="fieldinfo">
Leave blank to keep original image name.
<div class="fieldsyntax" style="display:none">
<ul>
<li>You can use "dynamic variables" to fill this field.</li>
<li>{item.[some item field]}  and {magmi.[store|imagename|imagename.noext|imagename.ext|attr_code]} are supported.</li>
<li>{item.sku}.jpg  : will create an image with item sku value as name and a jpg extension.</li>
<li>{item.sku}_{magmi.store}.jpg : this is a little trickier.<br/>
if you've got 5 stores,i will create 5 different copies of the input image &amp; force the name to be [item sku]_[store id].jpg for each copy.</li>
<li>{item.sku}_{magmi.imagename.noext}_{magmi.attr_code}.jpg, will create [sku]_[image name without extension]_[column name].jpg magento filename.</li>
</ul>
</div>
</div>
</div>
<div class="formline">
<span>Image import mode</span>
<?php $iwm=$this->getParam("IMG:writemode","keep");?>
<select name="IMG:writemode">
<option value="keep" <?php if($iwm=="keep"){?>selected="selected"<?php }?>>Keep existing images</option>
<option value="override" <?php if($iwm=="override"){?>selected="selected"<?php }?>>Override existing images</option>
</select>
</div>


