File to import:
<select name="csvfile">
	<?php foreach($files as $fname){ ?>	
		<option <?php if($fname==$curfile)?>selected<?php ?>><?php echo $fname?></option>
	<?php }?>
</select>
