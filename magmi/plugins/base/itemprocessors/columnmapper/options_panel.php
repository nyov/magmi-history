<div class="plugin_description">
This plugin enables to change column names from datasource before they are handled by magmi.
enter columns to set new name for in mapped column list field, separated by commas (,)
when leaving the field, new fields will be inserted for filling new column names.
<b>You can put several values (comma separated) in the mapped column names,doing so , the column mapper will replicate
values of column to map to all mapped columns !!!</b>
</div>
<?php $clist=$this->fixListParam($this->getParam("CMAP:columnlist"))?>
<div>
<ul class="formline">
	<li class="label">Mapped columns list</li>
	<li class="value"><input type="text" id="CMAP:columnlist" name="CMAP:columnlist" size="80" value="<?php echo $clist?>" onblur="cmap_dyn_buildparamlist()"></input></li>
</ul>
<div id="CMAP:columnsetup">
</div>
</div>
<script type="text/javascript">
var cmap_dvals=[];
cmap_getinputline=function(fieldname,dvalue)
{
	var linetpl='<ul class="formline"><li class="label">New name for col '+fieldname+'</li><li class="value"><input type="text" name="CMAP:'+encodeURIComponent(fieldname)+'" value="'+dvalue+'"></input></li></ul>';
	return linetpl;
};


cmap_dyn_buildparamlist=function()
{
  var value=$F('CMAP:columnlist')
  var content='';
  if(value!="")
  {
 	var arr=value.split(",");
  	var farr=[];
 	 arr.each(function(it){
 	 	 if(it!='')
 	 	 {
 	 		 var v=typeof(cmap_dvals[it])!='undefined'?cmap_dvals[it]:'';
  			farr.push({'field':it,'value':v});
 	 	 }
  	});
 	 farr.each(function(it){content+=cmap_getinputline(it.field,it.value)});
  }
  $('CMAP:columnsetup').update(content);
  
};
</script>
<script type="text/javascript">
 <?php $def=array();
 		$cl=$this->getParam('CMAP:columnlist');
 		if($cl!="")
 		{
 			foreach(explode(",",$cl) as $col)
 			{
 				$v=$this->getParam("CMAP:$col");
 				?>
 				cmap_dvals["<?php echo $col?>"]="<?php echo $v?>";
	<?php  		}
	 	}?>
	 cmap_dyn_buildparamlist();
</script>