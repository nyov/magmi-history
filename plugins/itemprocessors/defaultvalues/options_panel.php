<div class="plugin_description">
This plugin enables to set some default item values if not found in input source.
</div>
<div style="width:400px; height:145px;">
<ul class="formline">
	<li class="label">Default store:</li>
	<li class="value"><input type="text" name="DEFAULT:store" value="<?php echo $this->getParam("DEFAULT:store","admin")?>"></input></li>
</ul>
<ul class="formline">
	<li class="label">Default websites:</li>
	<li class="value"><input type="text" name="DEFAULT:websites" value="<?php echo $this->getParam("DEFAULT:websites","base")?>"></input></li>
</ul>
<ul class="formline">
	<li class="label">Default product type:</li>
	<li class="value"><input type="text" name="DEFAULT:type" value="<?php echo $this->getParam("DEFAULT:type","simple")?>"></input></li>
</ul>
<ul class="formline">
	<li class="label">Default attribute_set:</li>
	<li class="value"><input type="text" name="DEFAULT:attribute_set" value="<?php echo $this->getParam("DEFAULT:attribute_set","default")?>"></input></li>
</ul>
<ul class="formline">
	<li class="label">Default tax class:</li>
	<li class="value"><input type="text" name="DEFAULT:tax_class_id" value="<?php echo $this->getParam("DEFAULT:tax_class_id","Taxable Goods")?>"></input></li>
</ul>
<ul class="formline">
	<li class="label">Default category_ids:</li>
	<li class="value"><input type="text" name="DEFAULT:category_ids" value="<?php echo $this->getParam("DEFAULT:category_ids","2")?>"></input></li>
</ul>
</div>