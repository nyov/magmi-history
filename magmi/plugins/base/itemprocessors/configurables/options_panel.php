<div class="plugin_description">
This plugins handles configurable import</b>
</div>
<ul class="formline">
	<li class="label" style="width:360px">auto match simples skus before configurable</li>
	<li class="value"><select name="CFGR:simplesbeforeconf">
	<option value="0" <?php if ($this->getParam("CFGR:simplesbeforeconf")==0){?>selected="selected"<?php }?>>No</option>
	<option value="1" <?php if ($this->getParam("CFGR:simplesbeforeconf")==1){?>selected="selected"<?php }?>>Yes</option></select></li>
</ul>
<ul  class="formline">
<li class="label">
Force simples visibility
</li>
<li class="value">
<?php $v=$this->getParam("CFGR:updsimplevis",0)?>
<select name=CFGR:updsimplevis">
	<option value="0" <?php if($v==0){?>selected="selected"<?php }?>>Keep Untouched</option>
	<option value="1" <?php if($v==1){?>selected="selected"<?php }?>>Force to "Not Visible Individually"</option>
	<option value="2" <?php if($v==2){?>selected="selected"<?php }?>>Force to Catalog</option>
	<option value="3" <?php if($v==3){?>selected="selected"<?php }?>>Force to Search</option>
	<option value="4" <?php if($v==4){?>selected="selected"<?php }?>>Force to Catalog, Search</option>
</select>
</li>
</ul>