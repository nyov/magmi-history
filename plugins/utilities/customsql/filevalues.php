<?php
if(!isset($dr))
{
if(isset($_REQUEST['UTCSQL:queryfile']))
{
	$dr=$_REQUEST['UTCSQL:queryfile'];
}
}
if(isset($dr))
{
	$rparams=$this->getRequestParameters($dr,true);
}
foreach($rparams as $plabel=>$pname)
{?>
<ul class="formline">
<li class="label"><?php echo $plabel	?> </li>
<li class="value"><input name="UTCSQL:<?php echo $pname?>" type="text" value="<?php echo $this->getParam("UTCSQL:$pname") ?>"></input></li>
</ul>
<?}