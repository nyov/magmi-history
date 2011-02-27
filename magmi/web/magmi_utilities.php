<?php
	require_once("header.php");
	require_once("magmi_config.php");
	require_once("magmi_statemanager.php");
	require_once("magmi_pluginhelper.php");?>

<?php 
$profile="__utilities__";
$k="UTILITIES";
$plugins=Magmi_PluginHelper::getInstance('main')->getPluginClasses(array("utilities"));
?>
<div class="container_12">
<div class="grid_12 col omega">
<h3>Magmi Utilities</h3>
<ul>
<?php 
require_once("magmi_importer.php");
 $mmi=new MagentoMassImporter();
 $mmi->init("__utilities__");
 $mmi->connectToMagento();
?>
<?php foreach($plugins["utilities"] as $pclass)
{

	$pinst=Magmi_PluginHelper::getInstance("__utilities__")->createInstance(strtolower($k),$pclass);
	$pinst->setMmiRef($mmi);
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
		<span><a href="javascript:runUtility('<?php echo $pclass?>')" class="actionbutton " >Run Utility</a></span>
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
		var targetUrl="magmi_utility_run.php";
		new Ajax.Updater("plugin_run:"+pclass,"magmi_utility_run.php",{parameters:{pluginClass:pclass}});
	}
	togglePanel=function(pclass)
	{
		var target="pluginoptions:"+pclass;
		$(target).toggle();
	}
</script>
<?php 
$mmi->disconnectFromMagento();
?>
<?php require_once("footer.php")?>