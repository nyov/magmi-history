File to import:
<?php 
	$conf=Magmi_Config::getInstance();
	$conf->load();
	$magdir=$conf->get("MAGENTO","basedir");
	$files=glob($magdir."/var/import/*.csv");
?>
<select name="CSV:filename">
	<?php foreach($files as $fname){ ?>	
		<option <?php if($fname==$this->getParam("CSV:filename")){?>selected=selected<?php }?>><?php echo $fname?></option>
	<?php }?>
</select>
<div>
<span class="">CSV separator:</span><input type="text" maxlength="1" size="1" name="CSV:separator" value="<?php echo $this->getParam("CSV:separator")?>"></input>
<span class="">CSV Enclosure:</span><input type="text" maxlength="1" size="1" name="CSV:enclosure" value='<?php echo $this->getParam("CSV:enclosure")?>'></input>
</div>
