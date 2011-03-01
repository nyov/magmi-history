<?php
$stats=$this->getStatistics();
?>
<div class="col">
<h3>Current EAV Status</h3>
</div>
<div>
<ul>
<?php 
foreach($stats as $type=>$data)
{?>
	<?php $style="";
		if($data["pc"]==0)
		{
			$style="background-color:#88ff88";
		}
		else
		if($data["pc"]<15)
		{
			$style="background-color:#ffff88";
		}
		else
		{
			$style="background-color:#ff8888";
		}
	?>
	<li style="<?php echo $style?>">
		<span><?php echo $type?></span>
		<span><?php echo "{$data["empty"]}/{$data["total"]}"?></span>
		<span><?php echo $data["pc"]?></span>
	</li>
<?php 
}?>
</ul>
</div>
