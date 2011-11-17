<?php 
	$files=$this->getCSVList();
if($files!==false && count($files)>0){?>
<select name="CSV:filename" id="csvfile">
	<?php foreach($files as $fname){ ?>	
		<option <?php if($fname==$this->getParam("CSV:filename")){?>selected=selected<?php }?>><?php echo $fname?></option>
	<?php }?>
</select>
<a id='csvdl' href="./download_file.php?file=<?php echo $this->getParam("CSV:filename")?>">Download CSV</a>
<script type="text/javascript">
 $('csvfile').observe('change',function(el){
 		$('csvdl').href="./download_file.php?file="+$('csvfile').value;}
	);
</script><?php } else {?>
<span> No csv files found in <?php echo $this->getScanDir()?></span>
	<?php }?>