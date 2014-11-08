<?php include 'config.php';
header('Content-type: application/xml');

// Connecting, selecting database
$deleteconn = mysqli_connect($dbserv, $dbuser, $dbpass);
if(!$deleteconn)
{
  die('Could not connect: ' . mysql_error($deleteconn));

}
//echo 'Connected successfully';
mysqli_select_db($deleteconn,$dbname); //or die('Could not select database');


if (isset($_GET['id']))      $id      = $_GET['id'];

// Performing SQL query
$deleteSchedule    = "delete from schedule where transmitter = '".$id."'";
$deleteTransmitter = "delete from transmitter where TransmitterID = '".$id."'";

//if(!$deleteSchedule)die('Could not query: ' . mysql_error($deleteconn));
//$deleteresult = mysqli_query($deleteconn,$deleteSchedule); 
////or die('Query failed: ' . mysql_error());

//if(!$deleteTransmitter)die('Could not query: ' . mysql_error($deleteconn));
//$deleteresult = mysqli_query($deleteconn,$deleteTransmitter);
////or die('Query failed: ' . mysql_error());

$transPkQuery = "select * from transmitter where TransmitterID = '".$id."'";
if(!$transPkQuery)die('Could not query: ' . mysql_error($deleteconn));
$pkresult = mysqli_query($deleteconn,$transPkQuery);

$row = mysqli_fetch_array($pkresult);
$tid = $row['ID'];

$deletePair = "delete from pairing where TransmitterID= '".$tid."'";
if(!$deletePair)die('Could not query: ' . mysql_error($deleteconn));
$deleteresult = mysqli_query($deleteconn,$deletePair);
$noPairingDeleted = mysqli_affected_rows($deleteconn);
//or die('Query failed: ' . mysql_error());

if(!$deleteSchedule)die('Could not query: ' . mysql_error($deleteconn));
$deleteresult = mysqli_query($deleteconn,$deleteSchedule);
$noScheduleDeleted = mysqli_affected_rows($deleteconn);
//or die('Query failed: ' . mysql_error());

if(!$deleteTransmitter)die('Could not query: ' . mysql_error($deleteconn));
$deleteresult = mysqli_query($deleteconn,$deleteTransmitter);
$noTransmitterDeleted = mysqli_affected_rows($deleteconn);
//or die('Query failed: ' . mysql_error());

$orphanReceiver = "delete from receiver where ID NOT IN (select ReceiverID from pairing)";
if(!$orphanReceiver)die('Could not query: ' . mysql_error($deleteconn));
$deleteresult = mysqli_query($deleteconn,$orphanReceiver);
$noReceiversDeleted = mysqli_affected_rows($deleteconn);

?>
<delete-request>
<transmitterId><?php echo $id;?></transmitterId>
<pkid><?php echo $tid;?></pkid>
<deleteCounts>
<pairing><?php echo $noPairingDeleted;?></pairing>
<transmitter><?php echo $noTransmitterDeleted;?></transmitter>
<receiver><?php echo $noReceiversDeleted;?></receiver>
<schedule><?php echo $noScheduleDeleted;?></schedule>
</deleteCounts>
</delete-request>
