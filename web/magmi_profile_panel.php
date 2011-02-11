<script type="text/javascript">
		var MagmiImporter=Class.create({
			bsfcallbacks:[],
			registerBeforeSubmit:function(cb){this.bsfcallbacks.push(cb)},
			submit:function(){
				var context={results:[]};
				this.bsfcallbacks.each(function(bsc,o){this.results.push(bsc())},context);
				for(i=0;i<context.results.length;i++)
				{
					if(context.results[i]!="" && context.results[i]==false)
					{
						return false;
					}
				}
				$('import_form').submit();}
		});
		var magmi_import=new MagmiImporter();
		var profile="<?php echo $profile?>";
	</script>
<script type="text/javascript">
	addclass=function(it,o)
	{
		if(it.checked){
			this.arr.push(it.name);
		}
	};
	
	gatherclasses=function(tlist)
	{
		tlist.each(function(t,o){
			var context={arr:[]};
			$$(".pl_"+t).each(addclass,context);
			var target=$("plc_"+t);
			target.value=context.arr.join(",");
		});
	};
</script>
<div class="container_12" id="profile_action">
<div class="grid_12 col">
	<form action="magmi_chooseprofile.php" method="POST" id="chooseprofile" >
	<h3>Profile to configure</h3>
	<ul class="formline">
		<li class="label">Current Magmi Profile:</li>
		<li class="value">	
			<select name="profile" onchange="$(chooseprofile).submit()">
			<option <?php if(null==$profile){?>selected="selected"<?php }?> value="default">Default</option>
			<?php foreach($profilelist as $profilename){?>
			<option <?php if($profilename==$profile){?>selected="selected"<?php }?> value="<?php echo $profilename?>"><?php echo $profilename?></option>
			<?php }?>
			</select>
		</li>
	</ul>
	<ul class="formline">
		<li class="label">Copy Selected Profile to:</li>
		<li class="value"><input type="text" name="newprofile"></input></li>
	</ul>
	<input type="submit" value="Copy Profile &amp; switch"></input>
	<?php
	require_once("magmi_pluginhelper.php");
	$plugins=Magmi_PluginHelper::getInstance()->getPluginClasses();
	$order=array("datasources","general","itemprocessors");
?>
</form>
</div>


<form action="magmi_saveprofile.php" method="POST" >
	<input type="hidden" name="profile" value="<?php echo $profile?>">
	<?php foreach($order as $k)
	{?>
	<div class="grid_12 col <?php if($k==$order[count($order)-1]){?>omega<?php }?>">
		<h3><?php echo ucfirst($k)?></h3>
		<?php if($k=="datasources")
		{?>
			<?php $pinf=$plugins[$k];?>
			<?php if(count($pinf)>0){?>
			<ul>
			<li>
			<div class="pluginselect">
			<select name="PLUGINS_DATASOURCES:class">
			
			
			<?php 
			$sinst=null;
			foreach($pinf as $pclass)
			{
				$pinst=Magmi_PluginHelper::getInstance($profile)->createInstance($pclass);
				$pinfo=$pinst->getPluginInfo();
					
				if($plconf->isPluginEnabled($k,$pclass))
				{
					$sinst=$pinst;
				}
			?>
				<option value="<?php echo $pclass?>"<?php  if($sinst==$pinst){?>selected="selected"<?php }?>><?php echo $pinfo["name"]." v".$pinfo["version"]?></option>
			<?php }?>
			
			</select>
			</div>
			<div class="pluginconfpanel selected">
			<?php echo $sinst->getOptionsPanel()->getHtml();?>
			</div>
			</li>
			</ul>
			<?php }else{
						$conf_ok=0;
				
				?>
			Magmi needs a datasource plugin, please install one
			<?php }?>
			<?php 
		}
		else
		{?>
		<ul >
		<?php $pinf=$plugins[$k];?>
		<?php foreach($pinf as $pclass)	{
			$pinst=Magmi_PluginHelper::getInstance()->createInstance($pclass);
			$pinfo=$pinst->getPluginInfo();
		?>
		<li>
		<div class="pluginselect">
		<input type="checkbox" class="pl_<?php echo strtoupper($k)?>" name="<?php echo $pclass?>" <?php if($plconf->isPluginEnabled($k,$pclass)){?>checked="checked"<?php }?>>
		<span class="pluginname"><?php echo $pinfo["name"]." v".$pinfo["version"];?></span>
		</div>

		<?php 
			  $info=$pinst->getShortDescription();
		if($info!==null){?>
		<div class="plugininfo">
			<span>info</span>
			<div class="plugininfohover">
				<?php echo $info?>
			</div>
		</div>
		<?php }?>

		<?php $enabled=$plconf->isPluginEnabled($k,$pclass)?>
		<div class="pluginconf"  <?php if(!$enabled){?>style="display:none"<?php }?>>
			<span><a href="javascript:void(0)">configure</a></span>
		</div>
		<div class="pluginconfpanel">
			<?php if($enabled){echo $pinst->getOptionsPanel()->getHtml();}?>
		</div>

		</li>
		<?php }?>	
		<input type="hidden" id="plc_<?php echo strtoupper($k)?>" value="<?php echo implode(",",$plconf->getEnabledPluginClasses($k))?>" name="PLUGINS_<?php echo strtoupper($k)?>:classes"></input>
					
		<?php 
		}?>
				</ul>

	</div>
	<?php }?>
	<div style="float:right">
		<input type="submit" value="Save Current Profile (<?php echo $profile==null?"Default":$profile ?>)" <?php if(!$conf_ok){?>disabled="disabled"<?php }?>></input>
	</div>
</form>
</div>
<script type="text/javascript">
initConfigureLink=function(maincont)
{
 var cfgdiv=maincont.select('.pluginconf');
 if(cfgdiv.length>0)
 {
 	cfgdiv=cfgdiv[0];
 	var confpanel=maincont.select('.pluginconfpanel');
	 confpanel=confpanel[0]
 	cfgdiv.observe('click',function(ev){confpanel.toggleClassName('selected');});
 }
}
showConfLink=function(maincont)
{
	var cfgdiv=maincont.select('.pluginconf');
	if(cfgdiv.length>0)
	 {
	 
	cfgdiv=cfgdiv[0];
	cfgdiv.show();
	 }
}

loadConfigPanel=function(container,profile,plclass)
{
 new Ajax.Updater({success:container},'ajax_pluginconf.php',{parameters:{profile:profile,pluginclass:plclass},evalScripts:true,onComplete:showConfLink(container.parentNode)});
}
removeConfigPanel=function(container)
{
var cfgdiv=container.parentNode.select('.pluginconf');
cfgdiv=cfgdiv[0];
 cfgdiv.hide();
 container.hide();
 container.update('');
}


initAjaxConf=function(profile)
{
	//foreach plugin selection
	$$('.pluginselect').each(function(pls)
	{
		var del=pls.firstDescendant();
		var evname=(del.tagName=="SELECT"?'change':'click');
			
		//check the click
		del.observe(evname,function(ev)
		{
			var el=Event.element(ev);
			var plclass=(el.tagName=="SELECT")?el.value:el.name;
			var doload=(el.tagName=="SELECT")?true:el.checked;	
			var targets=pls.parentNode.select(".pluginconfpanel");
			var container=targets[0];
			if(doload)
			{
				loadConfigPanel(container,profile,plclass);
			}
			else
			{
				removeConfigPanel(container);
			}
		});
	});			
}
initDefaultPanels=function()
{
	$$('.pluginselect').each(function(it){initConfigureLink(it.parentNode);});
}

initAjaxConf();
initDefaultPanels();

</script>
