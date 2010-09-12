File to import:
<?php 
	$conf=Magmi_Config::getInstance();
	$conf->load();
	$magdir=$conf->get("MAGENTO","basedir");
	$files=glob($magdir."/var/import/*.csv");
?>
<select name="filename">
	<?php foreach($files as $fname){ ?>	
		<option ><?php echo $fname?></option>
	<?php }?>
</select>
