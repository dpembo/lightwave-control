<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
?>
<?php include "../config.php"; ?>
<html>
<?php

//Get the transmitters into an array from the query
$ids   = array();
$descs = array();

$conn = mysqli_connect($dbserv, $dbuser, $dbpass);
if(!$conn)
{
  die('Could not connect: ' . mysql_error($conn));

}
//echo 'Connected successfully';
mysqli_select_db($conn,$dbname); //or die('Could not select database');

// _______________________________________________________________________________

$query = 'SELECT * from schedule where enabled=1';

if(!$query)die('Could not query: ' . mysql_error($conn));
$list = mysqli_query($conn,$query); //or die('Query failed: ' . mysql_error());


?>
<head>

<title>Schedule</title>

<link href="/ha/css/wizard.css" rel="stylesheet">
<link rel="stylesheet" href="/ha/jquery/css/redmond/jquery-ui-1.10.4.custom.min.css">
<link rel="stylesheet" href="/ha/jquery/jquery.pnotify.default.css">
<link rel="stylesheet" href="/ha/jquery/jquery.dropdown.css">

<style>
optgroup
{
	color:#dddddd;
}
</style>

<script src="../jquery/js/jquery-1.10.2.js"></script>
<script src="../jquery/js/jquery-ui-1.10.4.custom.js"></script>
<script src="../jquery/jquery.dropdown.min.js"></script>

<script>
function next()
{
        document.location.replace('index.php');
}
</script>
</head>
<body>
<form method="post" action="step2.php">
<div id="header">
<a data-dropdown="#dropdown-1" class="menuicon" href="#">  &#9776;</a> Schedule
</div>

<?php include '../menu.php'; ?>

<div id="body">
 <div id="instructions"><br><br><br>
Schedule has been stored and activated
</div>

<!-- =================== EXISTNG SCHEDULE ======================== -->

<?php
$schedId = 0;
$crontab = "#Generated through Lightwave RF UI
";

while ($options = mysqli_fetch_array($list))
{
$schedId++;

$ID=$options['ID'];
$hour = $options['Hour'];
$mins = $options['Minute'];
$dow  = $options['dow'];
$transmitter = $options['transmitter'];
$command = $options['command'];
$notes = $options['Notes'];
$enabled = $options['enabled'];

if($hour == '99') 
{

$crontab = $crontab.'

'.'#'.$notes.'
0,10,20,30,40,50 15,16,17,18,19,20,21,22 * * '.$dow.' sleep 44 ; wget --no-check-certificate -O - "http://localhost:81/ha/schedule/sunset.php?id='.$transmitter.'&onoff='.substr($command,0,-2).'&dim='.substr($command,-2).'&room=Schedule+-+'.$ID.'" 2>&1 &';
  
}
else
{

$crontab = $crontab.'

'.'#'.$notes.'
'.$mins.' '.$hour.' * * '.$dow.' sleep 44 ; wget --no-check-certificate -O - "http://localhost:81/ha/ha.php?id='.$transmitter.'&onoff='.substr($command,0,-2).'&dim='.substr($command,-2).'&room=Schedule+-+'.$ID.'" 2>&1 &';

}

?>



<br>
<?php
}
$crontab = $crontab.'
';
//echo '<PRE>';
//echo $crontab;
//echo '</PRE><br><br>';

$file='/tmp/newcron.txt';
file_put_contents($file,$crontab);


//echo '<PRE>-------------------------
//';
echo exec('crontab /tmp/newcron.txt');
//echo '
//--------------------</PRE>';



//$output = shell_exec('id');
//echo '<PRE>'.$output.'</PRE>';

?>

<?php if(isset($_GET['errno']))
{
?>
<table>
<tr class="headerrow"><td colspan="2" class="headerrowtext errorText">Error Occurred</td></tr>
<tr>
<td colspan="2">
<span class="errorText">

<?php
	echo $_GET['errno'].' - '.$_GET['errmsg'];

	if(substr($_GET['errmsg'],0,6)=='mysqli')
	{
		echo '<br>Please check and confirm the database and settings';
	}

	if(substr($_GET['errmsg'],0,5)=='fopen')
	{
		echo '<br>Please check <i>config.php</i> exists and has the correct permissions (666)';
	}
	if($_GET['errno']=='512')
	{
		echo '<br>Confirm the COM Port exists and the device is connected';
	}
?>
</span>
</td>
</tr>
</Table>

<?php
}?>


</div>
<div id="buttons">
<input disabled="disabled" class="prevb" type="button" value="<< Previous" onclick="previous();">
<input class="nextb" type="button" value="View Schedule" onclick="next();">
</div>
</form>
</body>
</html>

