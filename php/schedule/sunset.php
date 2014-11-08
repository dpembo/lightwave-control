<?php include "../config.php"; 

$sunsetTimeStr = date_sunset(time(), SUNFUNCS_RET_STRING, 52.87, -1.66, 90, -0.75);

//echo $sunsetTime;
//echo '<br>';
//echo $now;
$nowTime = time();
$sunsetTime = date_sunset(time(), SUNFUNCS_RET_TIMESTAMP, 52.87, -1.66, 90, 0);

//echo '<br>';
//echo $nowTime;
//echo '<br>';
//cho $sunsetTime;

//$sunsetTime = time() - 500;

$delay = 15;
$diff = $sunsetTime - (60*($delay*3)) - $nowTime;
$diffTop = $diff + (60*$delay);

//echo '<br>';
//echo $diff;
//echo '<br>';
//echo $diffTop;
//echo '<br>';

if ($diff<=0 and $diffTop > 0 and $difftop <= (60*$delay))
{
	//echo 'In Sunset setting';

    	header("Status: 301 Moved Permanently");
    	header("Location:/ha/ha.php?". $_SERVER['QUERY_STRING']);
    	exit;


}
else
{
	echo '<h3>Not in sunset</h3>';
        echo '<pre>';
	echo $sunsetTimeStr.'<br>';
	echo $sunsetTime.'<br>';
	echo $nowTime.'<br>';
	echo $diff.'<br>';
	echo $diffTop;
	echo '</pre>';
}

?>
