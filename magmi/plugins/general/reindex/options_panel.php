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
	
	<input type="hidden" name="indexes" id="indexes"></input>
	<div>
	<ul>
	Indexing:<a href="#" onclick="fcheck(1);">All</a>&nbsp;<a href="#" onclick="fcheck(0)">None</a>
	<?php 
	    $idxarr=explode(",",$this->_plugin->getIndexList());
		foreach($idxarr as $indexname)
		{
	?>
		<li><input type="checkbox" name="<?php echo $indexname?>" class="_magindex"><?php echo $indexname?></input></li>
	<?php }?>
	</ul>
