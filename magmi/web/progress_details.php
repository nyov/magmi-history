<?php
 session_start();
 $key=$_REQUEST["key"];
 $data=$_SESSION["log_$key"];
 session_write_close();
 ?>
 <ul>
 <?php foreach($data as $line){?>
 <li><?php echo $line?></li>
 <?php 
 }?>
 </ul>
 