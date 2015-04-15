<?php
include 'boot.php';
$ch = curl_init();
/*
The data availble on the  iTune is for two days back from current day, so we will select the date untill which the data is to updated in $today veriable
*/
$today =strtotime('-2 day', time());


$starttime =strtotime('-30 day',$today);

/* Import the data form specific date, if not then set the value of $starttime to current date minus two*/
while($starttime<$today)
{
process($starttime);
$starttime = strtotime('+1 day', $starttime);
echo"<br>".date('d-m-Y',$starttime);
}
//echo date('d-M-Y', $starttime);
curl_close ($ch);
function process($time)
{
	$date = date('Ymd', $time);
	if($date>'20140101')
	{
	global $dbh, $ch, $accounts;
	foreach($accounts as $account)
	{
		$fields_string = "USERNAME=" . urlencode($account['username']);// your user name
		$fields_string .= "&PASSWORD=" . urlencode($account['password']);//your password
		$fields_string .= "&VNDNUMBER=" . $account['vndnumber'];//your vendor id
		$fields_string .= "&TYPEOFREPORT=Sales";
		$fields_string .= "&DATETYPE=Daily";
		$fields_string .= "&REPORTTYPE=Summary";
		$fields_string .= "&REPORTDATE=$date";
		echo '<br>Processing date of '.$account['account'].' for' .$date;
		$filename = "Downloaded_files/{$date}-{$account['vndnumber']}";
		
		$fp = fopen("$filename.gz", 'w');
		//With these curl functions you will be able to connect to the apples gateway for sales data
		curl_setopt($ch,CURLOPT_URL, 'https://reportingitc.apple.com/autoingestion.tft');
		curl_setopt($ch,CURLOPT_POST, 7);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
  		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_FILE, $fp);
		
		//execute post
		$contents = curl_exec ($ch);
		if ($contents  === false)
		{
    			echo 'Curl error: ' . curl_error($ch);
		}
	
		fclose($fp);
// Apple stores data for a day into a .gz file, which this code will download and extract
		if (filesize("$filename.gz"))
        {
            if (function_exists('gzdecode'))
			 {
                file_put_contents($filename, gzdecode(file_get_contents("$filename.gz")) );
            } 
			$i=0;
			$lines = gzfile("$filename.gz");
				foreach ($lines as $line)
				 {
				 $i++;
				   // the $line will return a tab seprated string of values
				   $data = explode("\t", $line);
				   
				   if($data[0]=='APPLE')
						{

						$begin_date= date('Ymd',strtotime($data[9]));
						$end_date= date('Ymd',strtotime($data[10]));
						$app_name=$data[4];
						$app_name=htmlentities($app_name,ENT_NOQUOTES,'UTF-8');
	
		// Inserting records into your database table
						$sql = "INSERT INTO `ios_sales_data`( `begin_date`, `end_date`,`provider`, `provider_country`, `sku`, `developer`, `title`, `version`, 
						`product_type_identifier`, `units`, `developer_proceeds`, `customer_currency`,
						 `country_code`, `currency_proceeds`, `apple_identifier`, `customer_price`, `promo_code`, `parent_identifier`, 
						 `subscription`, `period`, `category`) 
						 VALUES ('".$begin_date."','".$end_date."',
						'".$data[0]."','".$data[1]."','".$data[2]."','".$account['account']."','".mysql_real_escape_string($app_name)."','".$data[5]."',
						'".$data[6]."','".$data[7]."','".$data[8]."','".$data[11]."','".$data[12]."',
						'".$data[13]."','".$data[14]."','".$data[15]."','".$data[16]."','".$data[17]."','".$data[18]."','".$data[19]."',
						'".$data[20]."')";
						
						$i++;
						mysql_query($sql)or die (mysql_error()."<br>SQL-->".$sql);
						}
				}			
										
					echo "<br>\t  $i rows  Imported !!<br>";
		}
		else
		{
			echo ' File is of size 0' . PHP_EOL;
		}
	}
	}
}

header("Location:update_tables.php");