<div class="plugin_description">
This plugin enables to change attribute values from datasource before they are handled by magmi.
enter attributes to set new value for in replaced attributes field, separated by commas (,)
when leaving the field, new fields will be inserted for filling new column names.
</div>
<?php $clist=$this->fixListParam($this->getParam("VREP:columnlist"))?>
<div>
<ul class="formline">
	<li class="label">Replaced attributes</li>
	<li class="value"><input type="text" id="VREP:columnlist" name="VREP:columnlist" size="80" value="<?php echo $clist?>" onblur="vrep_mf.buildparamlist()"></input></li>
</ul>
<div id="VREP:columnsetup">
</div>
</div>
<script type="text/javascript">
var vr_vals=<?php echo tdarray_to_js($this,'VREP:columnlist','VREP')?>;
var vr_linetpl='<ul class="formline"><li class="label">New value for {fieldname}</li><li class="value"><input type="text" name="VREP:{fieldname.enc}" value="{value}"></input></li></ul>';
vrep_mf=new magmi_multifield('VREP:columnlist','VREP:columnsetup',vr_linetpl,vr_vals);
vrep_mf.buildparamlist();
</script>
