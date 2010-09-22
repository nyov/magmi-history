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
<div>
<span class="">CSV separator:</span><input type="text" maxlength="1" size="1" name="csv_separator" value=","></input>
<span class="">CSV Enclosure:</span><input type="text" maxlength="1" size="1" name="csv_enclosure" value='"'></input>
</div>
