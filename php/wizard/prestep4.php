<?php include "../config.php" ?>
<html>

<head>

<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

// Connecting, selecting database
$conn = mysqli_connect($dbserv, $dbuser, $dbpass);
if(!$conn)
{
  die('Could not connect: ' . mysql_error($conn));

}
//echo 'Connected successfully';
mysqli_select_db($conn,$dbname); //or die('Could not select database');


$roomId = $_POST["roomId"];
$receiverId = $_POST["receiverId"];
$transmitterId = $_POST["transmitterID"];
$newTransmitterDesc = $_POST["newTransmitterDesc"];

//_______________________________________________________________________________

//Insert room
if($_POST["roomId"]=='new')
{
$query = 'INSERT INTO `room` (`Description`,`Order`) VALUES ("'.$_POST["roomDescription"].'",0);';
if(!$query)die('Could not query: ' . mysql_error($conn));
$res = mysqli_query($conn,$query); //or die('Query failed: ' .mysql_error());
if( mysqli_affected_rows($conn)<0) die('Could not insert room: '.mysqli_error($conn));

//Get inserted id
$query = 'SELECT Max(ID) as ID  FROM `room` WHERE 1';
if(!$query)die('Could not query: ' . mysql_error($conn));
$res = mysqli_query($conn,$query); //or die('Query failed: ' .mysql_error());
$ids = mysqli_fetch_array($res);
$roomId = $ids['ID'];
}

//_______________________________________________________________________________

//Insert receiver
if($_POST["receiverId"]=='new')
{
$query = 'INSERT into `receiver` (`Description`,`TypeID`,`RoomID`) 
VALUES ("'.$_POST["receiverDescription"].'",'.$_POST["typenew"].','.$roomId.');';

$res = mysqli_query($conn,$query); //or die('Query failed: ' .mysql_error());
if( mysqli_affected_rows($conn)<0) die('Could not insert receiver');

//get inserted id;
$query = 'SELECT Max(ID) as ID  FROM `receiver` WHERE 1';
if(!$query)die('Could not query: ' . mysql_error($conn));
$res = mysqli_query($conn,$query); //or die('Query failed: ' .mysql_error());
$ids = mysqli_fetch_array($res);
$receiverId = $ids['ID'];
}

//_______________________________________________________________________________

//Insert transmitter
if($_POST["transmitterID"]=='new')
{
$query = 'INSERT into `transmitter` (`TransmitterID`,`Notes`,`Enabled`)
VALUES ("'.$_POST["newTransmitterID"].'","'.$newTransmitterDesc.'",1);';

$res = mysqli_query($conn,$query); //or die('Query failed: ' .mysql_error());
if( mysqli_affected_rows($conn)<0) die('Could not insert transmitter');

//get inserted id;
$query = 'SELECT Max(ID) as ID  FROM `transmitter` WHERE 1';
if(!$query)die('Could not query: ' . mysql_error($conn));
$res = mysqli_query($conn,$query); //or die('Query failed: ' .mysql_error());
$ids = mysqli_fetch_array($res);
$transmitterId = $ids['ID'];
}
else
{
//Get the transmitter desc
$query = 'SELECT *  FROM `transmitter` WHERE ID='.$_POST["transmitterID"];
if(!$query)die('Could not query: ' . mysql_error($conn));
$res = mysqli_query($conn,$query); //or die('Query failed: ' .mysql_error());
$ids = mysqli_fetch_array($res);
$newTransmitterDesc = $ids['Notes'];
}

//_____________________________________________________________________________

//Insert pairing
$query = 'INSERT into `pairing` (`TransmitterID`,`ReceiverID`,`Notes`)
VALUES ('.$transmitterId.','.$receiverId.',"'.$newTransmitterDesc.'");';

$res = mysqli_query($conn,$query); //or die('Query failed: ' .mysql_error());
if( mysqli_affected_rows($conn)<0) die('Could not insert receiver');

//get inserted id;
$query = 'SELECT Max(ID) as ID  FROM `pairing` WHERE 1';
if(!$query)die('Could not query: ' . mysql_error($conn));
$res = mysqli_query($conn,$query); //or die('Query failed: ' .mysql_error());
$ids = mysqli_fetch_array($res);
$pairingId = $ids['ID'];


?>

<script language="javascript">
function previous()
{
	history.back();
}
function next()
{
	document.forms[0].submit();
}

</script>
</head>
<body onload="next();">
<form method="post" action="step4.php?tId=<?php echo $transmitterId;?>">
<input type="hidden" name="roomId" value="<?php echo $roomId;?>">
<input type="hidden" name="receiverId" value="<?php echo $receiverId;?>">
<input type="hidden" name="transmitterID" value="<?php echo $transmitterId;?>">
<input type="hidden" name="pairingId" value="<?php echo $pairingId;?>">
</form>
</body>
</html>
