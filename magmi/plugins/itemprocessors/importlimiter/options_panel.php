<div class="plugin_description">
This plugin is made to limit magmi import to selected record ranges or matching values
ranges are ranges of rows to be imported
filters are regexps or strings that if matched will exclude record from import
</div>
<div>
<span class="">Limiter ranges:</span><input type="text"  name="LIMITER:ranges" size="80" value="<?php echo $this->getParam("LIMITER:ranges")?>"></input>
examples:
<pre>
1-100  -> for the first 100 records of csv
100-   -> for all records after 100 (including 100th)
1-10,40-50,67,78,89 -> for records 1 to 10,40 to 50 , 67 , 78 & 89
</pre>
</div>
<div>
<span class="">Limiter filters:</span><input type="text"  name="LIMITER:filters" size="80" value="<?php echo $this->getParam("LIMITER:filters")?>"></input>
examples:
<pre>
sku::00.*  -> exclude all skus that begin with 00
!name::.*blue.* -> exclude all items with name not blue (see the  ! before the "name" field to negate the filter)
sku:00.*;;!name::.*blue.* -> exclude all items with skus that begin with 00 which name does not contain blue 
</pre>
</div>
