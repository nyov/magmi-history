	<script type="text/javascript">
		getIndexes=function()
		{
			var outs=[];
			$$('._magindex').each(function(it){if(it.checked){outs.push(it.name)}});
			return outs.join(",");
		};

		
	
		fcheck=function(t)
		{
			$$('._magindex').each(function(it){it.checked=t});
			
		}

		updateIndexes=function()
		{
			$('indexes').value=getIndexes();
		}
		
		magmi_import.registerBeforeSubmit(updateIndexes);
				
	</script>
	
	<input type="hidden" name="REINDEX:indexes" id="indexes" value="<?php echo $this->getParam("REINDEX:indexes")?>"></input>
	<div>
	<span>Indexing:</span><a href="#" onclick="fcheck(1);">All</a>&nbsp;<a href="#" onclick="fcheck(0)">None</a>
	<ul>
	<?php 
	    $idxarr=explode(",",$this->_plugin->getIndexList());
		$indexes=explode(",",$this->getParam("REINDEX:indexes"));
	    foreach($idxarr as $indexname)
		{
	?>
		<li><input type="checkbox" name="<?php echo $indexname?>" class="_magindex" <?php if(in_array($indexname,$indexes)){?>checked=checked<?php }?>><?php echo $indexname?></input></li>
	<?php }?>
	</ul>
	</div>