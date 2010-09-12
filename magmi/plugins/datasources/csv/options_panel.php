File to import:
<?php 
	$conf=Magmi_Config::getInstance();
	$conf->load();
	$magdir=$conf->get("MAGENTO","basedir");
	$files=glob($magdir."/var/import/*.csv");
	$curfile=$_POST["csvfile"]?>
<select name="csvfile">
	<?php foreach($files as $fname){ ?>	
		<option <?php if($fname==$curfile)?>selected<?php ?>><?php echo $fname?></option>
	<?php }?>
</select>
