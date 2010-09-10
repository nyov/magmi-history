<div class="container_12">
	<div class="grid_12">
	<?php if(!isset($curfile) && MagentoMassImporter::getState()!=="running"){
		MagentoMassImporter::setState("idle"); 
		include_once("magmi_import_config.php");
	}
	else
	{
		include_once("magmi_import_run.php");
	}
	?>
	</div>
	
</div>

