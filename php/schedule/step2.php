<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$scheduleCount = $_POST["scheduleCount"];

$transmitterNew = $_POST["transmitterNEW"];
$newTransmitterSet = false;
if(!empty($transmitterNew))$newTransmitterSet=true;

if($newTransmitterSet)
{
	$hoursNew = $_POST["hoursNEW"];
	$minsNew =  $_POST["minsNEW"];
	$dayNew = $_POST["dayNEW"];
	$transmitterNew = $_POST["transmitterNEW"];
	$commandNew = $_POST["commandNEW"];
	$notesNew = $_POST["notesNEW"];
	
	if(isset($_POST["sunsetNEW"]))
	{
	  $hoursNew = '99';
	  $minsNew  = '99';
	}
	//$sunsetNew = $_POST["sunsetNEW"];
}

?>
<?php include "../config.php"; ?>
<html>
<?php


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

<title>Schedule Saving...</title>

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
        document.forms[0].submit();
}
</script>
</head>
<body onload="next()">
<form method="post" action="step3.php">
<div id="header">
<a data-dropdown="#dropdown-1" class="menuicon" href="#">  &#9776;</a> Schedule
</div>

<?php include '../menu.php'; ?>

<div id="body">
 <div id="instructions"><br><br><br>
Schedule is being saved... please wait!
</div>

<?php

for($i = 1; $i <= $scheduleCount; $i++)
{
	$id			= $_POST["id".$i];
	$transmitter= $_POST["transmitter".$i];
	$hours		= $_POST["hours".$i];
	$mins		= $_POST["mins".$i];
	$day		= $_POST["day".$i];
	$command	= $_POST["command".$i];
	$notes		= $_POST["notes".$i];

	$pos 		= strpos($transmitter,'-');
	$pairid		= substr($transmitter,0,$pos);
	$tid            = substr($transmitter,$pos+1);

        
	$sunset = 0;
	if(isset($_POST["sunset".$i]))
	{
		$sunset = 1;
		$hours = 99;
		$mins  = 99;
	}

	if(isset($_POST['delete'.$i]))
	{
		$delete=1;
	}
	else
	{
		$delete=0;
	}

	if(isset($_POST['enabled'.$i]))
	{
		$enabled=1;
	}
	else
	{
		$enabled=0;
	}
?>
<pre>
<?php


if($delete==0)
{
	$query = '
	UPDATE `schedule` SET
	Hour='.$hours.' ,
	Minute='.$mins.',
	dow=\''.$day.'\',
	pairid='.$pairid.',
	transmitter=\''.$tid.'\',
	command=\''.$command.'\',
	Notes=\''.$notes.'\',
	enabled='.$enabled.'
	WHERE ID='.$id;
}
else
{
	$query = 'Delete from `schedule`
	WHERE ID='.$id;
}

//echo $query;

$res = mysqli_query($conn,$query); //or die('Query failed: ' .mysql_error());

//echo "Updated Schedule";

?>
</pre>
<?php
}


if($newTransmitterSet)
{
	$pos 		= strpos($transmitterNew,'-');
	$pairid		= substr($transmitterNew,0,$pos);
	$tid        = substr($transmitterNew,$pos+1);




	//Now insert a new schedule if there is one
	$query="
	INSERT INTO `lightwaverf`.`schedule` (
	`Hour` ,
	`Minute` ,
	`dow` ,
	`pairId` ,
	`transmitter` ,
	`command` ,
	`Notes` ,
	`enabled`
	)
	VALUES (
	".$hoursNew.",".$minsNew.",'".$dayNew."',".$pairid.",'".$tid."','".$commandNew."', '".$notesNew."', '1'
	);
	";

	$res = mysqli_query($conn,$query); //or die('Query failed: ' .mysql_error());
}

//echo "<PRE>";
//echo $query;
//echo "</PRE>";

?>

</div>
<div id="buttons">
<input disabled="disabled" class="prevb" type="button" value="<< Previous" onclick="previous();">
<input disabled="disabled" class="nextb" type="button" value="Next >>" onclick="next();">
</div>
</form>
</body>
</html>

