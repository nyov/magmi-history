<?php
 require_once("./magmi_datapump.php");
 
 /** Define a logger class that will receive all magmi logs **/
 class TestLogger
 {
 	/**
 	 * logging methos
 	 * @param string $data : log content
 	 * @param string $type : log type
 	 */
 	public function log($data,$type)
 	{
 		echo "$type:$data\n";
 	}
 }
 $dp=Magmi_DataPumpFactory::getDataPumpInstance("productimport");
 $dp->beginImportSession("test_ptj","create",new TestLogger());
 /* Create 5000 items , with  every 100 :
  * 
  * 	upsell on last 100 even
  *     cross sell on last 100 odd 
  *     related on last 100 every 5
  *     cross sell on last 100 every 10 
  *     categories named catX/even or catX/odd with X is thousand of item (using categories plugin) */
 for($sku=0;$sku<5000;$sku++)
 {
 	$cats=array("cat".strval(intval($sku/1000)));
 	if($sku%2==0)
 	{
 		$cats[]="even";
 	}
 	else
 	{
 		$cats[]="odd";
 	}
 	$item=array("sku"=>str_pad($sku,5,"0",STR_PAD_LEFT),"name"=>"item".$sku,"description"=>"test".$sku,"price"=>rand(1,500),"categories"=>implode("/",$cats));
 	if($sku>99 && $sku%100==0)
 	{
 		$upsell=array("-re::.*");
		$csell=array("-re::.*");
 		$re=array("-re::.*");
 		$xre=array();
 		for($i=$sku-99;$i<$sku;$i++)
 		{
 			$rsku=str_pad($i,5,"0",STR_PAD_LEFT);
 			if($i%2==1)
 			{
 				$upsell[]=$rsku;
 			}
 			else
 			{
 				$csell[]=$rsku;
 			}
 			if($i%10==1)
 			{
 				$xre[]=$rsku;
 			}
 			else
 			{
 				if($i%5==1)
 				{
 					$re[]=$rsku;
 				}
 			}
 		}		
 		$item["us_skus"]=implode(",",$upsell);
 		$item["cs_skus"]=implode(",",$csell);
 		$item["re_skus"]=implode(",",$re);
 		$item["xre_skus"]=implode(",",$xre);
 	}
 	/* import newly defined item */
 	$dp->ingest($item);
 }
 /* end import session, will run post import plugins */
 $dp->endImportSession();
 