<div class="plugin_description">
This plugin imports data from Generic SQL Backend
</div>
<ul class="formline">
<?php $dbtype=$this->getParam("SQL:dbtype");?>
<li class="label">Input DB Type</li>
<li><select name="SQL:dbtype">
	<option value="mysql" <?if ($dbtype=="mysql"){?>selected="selected"<?php }?>>MySQL</option>
	<option value="other" <?if ($dbtype=="other"){?>selected="selected"<?php }?>>Other</option>
</select></li>
</ul>
<ul class="formline">
<li class="label">Input DB Host</li>
<li class="value"><input type="text" name="DT:dbhost" value="<?echo $this->getParam("SQL:dbhost","localhost")?>"/></li>
</ul>
<ul class="formline">
<li class="label">Input DB Name</li>
<li class="value"><input type="text" name="DT:dbname" value="<?echo $this->getParam("SQL:dbname","")?>"/></li>
</ul>
<ul class="formline">
<li class="label">Input DB User</li>
<li class="value"><input type="text" name="DT:dbuser" value="<?echo $this->getParam("SQL:dbuser","")?>"/></li>
</ul>
<ul class="formline">
<li class="label">Input DB Password</li>
<li class="value"><input type="password" name="DT:dbpass" value="<?echo $this->getParam("SQL:dbpass","")?>"/></li>
</ul>
<ul class="formline">
<li class="label">Input DB Initial Statements (optional)</li>
<li class="value"><textarea name="SQL:extra" cols="80" rows="5">
<?echo $this->getParam("SQL:extra","")?>
</textarea>
<div class="fieldinfo">
Put DB requests like SET NAMES if necessary separated by ;
</div>
</li>

</ul>
<ul class="formline">
<li class="label">SQL file</li>
<li class="value">
<?php $dr=$this->getParam("SQL:queryfile");?>
<?php $sqlfiles=$this->getSQLFileList();?>
<select name="SQL:queryfile">
	<?php foreach($sqlfiles as $curfile):?>
	<option <?php if($curfile==$dr){?>selected=selected<?php }?> value="<?php echo $curfile?>" ><?php echo basename($curfile)?></option>
	<?php endforeach?>
</select>
</li>
</ul>
