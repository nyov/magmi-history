	<input type="hidden" name="indexes" id="indexes"></input>
	<div>
	<ul>
	Indexing:<a href="#" onclick="fcheck(1);">All</a>&nbsp;<a href="#" onclick="fcheck(0)">None</a>
	<?php $idxarr=explode(",",MagentoMassImporter::$indexlist);
		foreach($idxarr as $indexname)
		{
	?>
		<li><input type="checkbox" name="<?php echo $indexname?>" class="_magindex"><?php echo $indexname?></input></li>
	<?php }?>
	</ul>
