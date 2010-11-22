<div class="plugin_description">
This plugin enables to set some default item values if not found in input source.
enter columns to set default value for in default attribute list field, separated by commas (,)
when leaving the field, new fields will be inserted for filling default values.
</div>
<?php $clist=$this->fixListParam($this->getParam("DEFAULT:columnlist"))?>
<div>
<ul class="formline">
	<li class="label">Default attribute list</li>
	<li class="value"><input type="text" id="DEFAULT:columnlist" name="DEFAULT:columnlist" size="80" value="<?php echo $clist?>" onblur="dyn_buildparamlist()"></input></li>
</ul>
<div id="DEFAULT:columnsetup">
</div>
</div>
<script type="text/javascript">
var dvals=[];
getinputline=function(fieldname,dvalue)
{
	var linetpl='<ul class="formline"><li class="label">Default '+fieldname+'</li><li class="value"><input type="text" name="DEFAULT:'+encodeURIComponent(fieldname)+'" value="'+encodeURIComponent(dvalue)+'""></input></li></ul>';
	return linetpl;
};


dyn_buildparamlist=function()
{
  var value=$F('DEFAULT:columnlist')
  var content='';
  if(value!="")
  {
 	var arr=value.split(",");
  	var farr=[];
 	 arr.each(function(it){
 	 	 if(it!='')
 	 	 {
 	 		 var v=typeof(dvals[it])!='undefined'?dvals[it]:'';
  			farr.push({'field':it,'value':v});
 	 	 }
  	});
 	 farr.each(function(it){content+=getinputline(it.field,it.value)});
  }
  $('DEFAULT:columnsetup').update(content);
  
};
</script>
<script type="text/javascript">
 <?php $def=array();
 		$cl=$this->getParam('DEFAULT:columnlist');
 		if($cl!="")
 		{
 			foreach(explode(",",$cl) as $col)
 			{
 				$v=$this->getParam("DEFAULT:$col");
 				?>
 				dvals["<?php echo $col?>"]="<?php echo $v?>";
	<?php  		}
	 	}?>
 	dyn_buildparamlist();
</script>