<?php
	require_once("header.php");
	require_once("../engines/magmi_utilityengine.php");
	
?>
<div class="container_12">
<div class="grid_12 col omega">
<h3>Magmi Utilities</h3>
<ul>
<?php 
 $mmi=new Magmi_UtilityEngine();
 $mmi->initialize();
 $mmi->createPlugins("__utilities__",null);
 $plist=$mmi->getPluginInstances("utilities");
 ?>
<?php foreach($plist as $pinst)
{
	$pclass=$pinst->getPluginClass();
	$pinfo=$pinst->getPluginInfo();
	?>
	<li class="utility" >
	<div class="pluginselect">
	<span class="pluginname"><?php echo $pinfo["name"]." v".$pinfo["version"];?></span>
	</div>
	<?php 
	  $info=$pinst->getShortDescription();
	?>
	<div class="plugininfo">
	<?php if($info!==null){?>
		<span>info</span>
		<div class="plugininfohover">
			<?php echo $info?>
		</div>		
	<?php }?>
	</div>
	<div class="plugininfo">
		<a href="javascript:togglePanel('<?php echo $pclass?>')">Options</a>
	</div>
	
	<div class="utility_run">
		<span><a id="plrun_<?php echo $pclass?>" href="javascript:runUtility('<?php echo $pclass?>')" class="actionbutton " >Run Utility</a></span>
	</div>
	<div id="plugin_run:<?php echo $pclass?>"></div>
	<div class="pluginoptionpanel" id="pluginoptions:<?php echo $pclass?>" style="display:none">
		<?php echo $pinst->getOptionsPanel()->getHtml()?>
	</div>
	</li>
<?php }?>	
</ul>
</div>
</div>

<script type="text/javascript">
	
	runUtility=function(pclass)
	{
		var targetUrl="magmi_run.php";
		new Ajax.Updater("plugin_run:"+pclass,"magmi_run.php",{parameters:{
			engine:'magmi_utilityengine:Magmi_UtilityEngine',
			logfile:'utility_run.txt',
			plugin_class:pclass}});
	}
	
	togglePanel=function(pclass)
	{
		var target="pluginoptions:"+pclass;
		$(target).toggle();
	}

	var warntargets=[];
	<?php $warn=$pinst->getWarning();
	if($warn!=null)
	{
		$pclass=$pinst->getPluginClass();?>
		warntargets.push({target:'plrun_<?php echo $pclass?>',msg:'<?php echo $warn?>'});
	<?php 	
	}?>
	warntargets.each(function(it){
		$(it.target).observe('click',function(ev){
			var res=confirm(it.msg);
			if(res==false)
			{
				Event.stop(ev);
				return;
			}
		})});

</script>
<?php require_once("footer.php")?>