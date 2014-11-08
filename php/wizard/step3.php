<?php include "../config.php" ?>
<html>

<head>
<link href="/ha/css/wizard.css" rel="stylesheet">

<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

// Connecting, selecting database
$conn = mysqli_connect($dbserv, $dbuser,$dbpass);
if(!$conn)
{
  die('Could not connect: ' . mysql_error($conn));

}
//echo 'Connected successfully';
mysqli_select_db($conn,$dbname); //or die('Could not select database');


$ids = array();
$descs = array();

// Performing SQL query
$query = 'SELECT * FROM ReceiverTypes';
if(!$query)die('Could not query: ' . mysql_error($conn));
$typeList = mysqli_query($conn,$query); //or die('Query failed: ' . mysql_error());

while ($options = mysqli_fetch_array($typeList))
{
  $ids[] =  $options['ID'];
  $descs[] = $options['Description'];
}

$transIds = array();
$transquery = 'select * from transmitter';

if(!$transquery)die('Could not query: ' . mysql_error($conn));
$transList = mysqli_query($conn,$transquery); //or die('Query failed: ' . mysql_error());

while ($trans = mysqli_fetch_array($transList))
{
  $transIds[] =  $trans['TransmitterID'];
}


?>


<title>Setup Wizard</title>
<style>
#allnibbles
{
	display:none;
}

.pairs
{
	width:500px;
	vertical-align: text-top;
	text-align: left;
}

.pairs TD
{
	width:250px;
	vertical-align: text-top;
}

.statusText
{
	background-color:#DDDDDD;
	height:50px;
	border-top:2px solid #444444;
	margin-top: 10px;
	width: 300px;
	display: none;
}

.input150
{
	width: 150px;
	font-family: courier;
}

.input300
{
	width: 300px;
	font-family: courier;
}

</style>
<script>

function alreadyExists(inId)
{
<?php
foreach ($transIds as $v) { ?>
	//alert("Looking for: " + inId);
	if(inId=="<?php echo $v; ?>")return true;
<?php } ?>
	//alert("not exist");
	return false;
}

function generateNewTransmitter()
{
	foundOne = false;
	document.getElementById("statusText").innerHTML="Generating a new transmitter ID";
	attempt = 1;
	while(!foundOne)
	{
			foundOne = randomTransmitter();
			if(foundOne)
			{
				document.getElementById("statusText").innerHTML="New Transmitter ID Identified\n" + getIntValueAsString();
				document.getElementById("newTransmitterID").value=getIntValueAsString();
			}
			else
			{
				attempt++;
				document.getElementById("statusText").innerHTML="Duplicate Transmitter ID Generated - retrying (" + attempt + ")";
			}
	}
	//alert("Found a new one!");
}

function randomTransmitter()
{
		rnd1 = Math.floor((Math.random()*15));
		rnd2 = Math.floor((Math.random()*15));
		rnd3 = Math.floor((Math.random()*15));
		rnd4 = Math.floor((Math.random()*15));
		rnd5 = Math.floor((Math.random()*15));
		rnd6 = Math.floor((Math.random()*15));

		document.getElementById("nibble1").selectedIndex=rnd1;
		document.getElementById("nibble2").selectedIndex=rnd2;
		document.getElementById("nibble3").selectedIndex=rnd3;
		document.getElementById("nibble4").selectedIndex=rnd4;
		document.getElementById("nibble5").selectedIndex=rnd5;
		document.getElementById("nibble6").selectedIndex=rnd6;


		val = getIntValueAsString();
		if(!alreadyExists(val))
		{
			return true;
		}
		else
		{
			return false;
		}
}

function getIntValueAsString()
{
	//Now get the values, convert each to an int, and see what we get!

	nib1 = document.getElementById("nibble1").value;
	nib2 = document.getElementById("nibble2").value;
	nib3 = document.getElementById("nibble3").value;
	nib4 = document.getElementById("nibble4").value;
	nib5 = document.getElementById("nibble5").value;
	nib6 = document.getElementById("nibble6").value;

	nib1Int = parseInt(nib1,2);
	nib2Int = parseInt(nib2,2);
	nib3Int = parseInt(nib3,2);
	nib4Int = parseInt(nib4,2);
	nib5Int = parseInt(nib5,2);
	nib6Int = parseInt(nib6,2);

	//alert(nib1Int + " " + nib2Int+ " " + nib3Int+ " " + nib4Int+ " " + nib5Int+ " " + nib6Int);
	return (nib1Int.toString() + nib2Int.toString() + nib3Int.toString() + nib4Int.toString() + nib5Int.toString() + nib6Int.toString());
}



function previous()
{
	history.back();
}
function next()
{
	generateNewTransmitter();
	document.forms[0].submit();
}

</script>
</head>
<body>
<form method="post" action="prestep4.php">
<input type="hidden" name="roomId" value="<?php echo $_POST["roomId"];?>">
<input type="hidden" name="roomDescription" value="<?php echo $_POST["roomDescription"];?>">
<input type="hidden" name="receiverId" value="<?php echo $_POST["receiver"];?>">
<input type="hidden" name="receiverDescription" value="<?php echo $_POST["receiverDescription"];?>">
<input type="hidden" name="typenew" value="<?php echo $_POST["typenew"];?>">
<div id="header">
Step 3: Transmitter/Receiver Pairing
</div>
<div id="body">
 <div id="instructions">
 Select an existing Transmitter pairing to associate with the device with others (e.g. to allow a single on/off switch for multiple devices, or create a new transmitter
 <div>

<Table class="pairs">
<tr class="headerrow"><td class="headerrowtext" colspan="2">New Transmitter</td></tr>

<tr>
<td><input type="radio" name="transmitterID" value="new"></td>
<td>New Transmitter Description: <input class="descinput" type="text" name="newTransmitterDesc"></td>
</tr>

<tr class="headerrow"><td class="headerrowtext" colspan="2">Existing Receiver/Transmitter Pairs</td></tr>

<?php
// Performing SQL query
$query = 'SELECT * FROM transmitter where Enabled=1';
if(!$query)die('Could not query: ' . mysql_error($conn));
$result = mysqli_query($conn,$query); //or die('Query failed: ' . mysql_error());

if(!$result)
{
?>
<tr><td colspan="2">No Existing Pairs</td></tr>
<?php
}
?>

<?php
while ($line = mysqli_fetch_array($result)) 
{ ?>
<tr>
<td>
<input type="radio" name="transmitterID" value="<?php echo $line['ID'];  ?>">
</td>

<td>
<div>
<table class="pairs">

<?php

$pairQuery = '
SELECT 
pair.ID,
pair.TransmitterID,
pair.ReceiverID,
pair.Notes,
rec.Description as ReceiverDescription,
rec.TypeID as TypeID,
rec.RoomID,
rtype.Description as TypeDescription
FROM pairing as pair, receiver as rec, ReceiverTypes as rtype
WHERE TransmitterID ='.$line['ID'].'
AND rec.ID = pair.ReceiverID
AND rtype.ID = rec.TypeID
ORDER BY pair.ID
';

if(!$pairQuery)die('Could not query: ' . mysql_error($conn));
$pairResult = mysqli_query($conn,$pairQuery); //or die('Query failed: ' . mysql_error());

while ($pairLine = mysqli_fetch_array($pairResult))
{
echo '<tr>';
echo '<td>'.$pairLine['ReceiverDescription'].'</td>';
echo '<td>'.$pairLine['TypeDescription'].'</td>';
echo '</tr>';
}
?>
</table> 
</div>
</td>

</tr>
<?php } ?>
</Table>



 </div>

<div id="allnibbles">
<input type="text" id="newTransmitterID" name="newTransmitterID">
<select name="nibble1" id="nibble1">
<option value="11110110">1111 0110</option>
<option value="11101110">1110 1110</option>
<option value="11101101">1110 1101</option>
<option value="11101011">1110 1011</option>
<option value="11011110">1101 1110</option>
<option value="11011101">1101 1101</option>
<option value="11011011">1101 1011</option>
<option value="10111110">1011 1110</option>
<option value="10111101">1011 1101</option>
<option value="10111011">1011 1011</option>
<option value="10110111">1011 0111</option>
<option value="01111110">0111 1110</option>
<option value="01111101">0111 1101</option>
<option value="01111011">0111 1011</option>
<option value="01110111">0111 0111</option>
<option value="01101111">0110 1111</option>
</select>

<select name="nibble2" id="nibble2">
<option value="11110110">1111 0110</option>
<option value="11101110">1110 1110</option>
<option value="11101101">1110 1101</option>
<option value="11101011">1110 1011</option>
<option value="11011110">1101 1110</option>
<option value="11011101">1101 1101</option>
<option value="11011011">1101 1011</option>
<option value="10111110">1011 1110</option>
<option value="10111101">1011 1101</option>
<option value="10111011">1011 1011</option>
<option value="10110111">1011 0111</option>
<option value="01111110">0111 1110</option>
<option value="01111101">0111 1101</option>
<option value="01111011">0111 1011</option>
<option value="01110111">0111 0111</option>
<option value="01101111">0110 1111</option>
</select>

<select name="nibble3" id="nibble3">
<option value="11110110">1111 0110</option>
<option value="11101110">1110 1110</option>
<option value="11101101">1110 1101</option>
<option value="11101011">1110 1011</option>
<option value="11011110">1101 1110</option>
<option value="11011101">1101 1101</option>
<option value="11011011">1101 1011</option>
<option value="10111110">1011 1110</option>
<option value="10111101">1011 1101</option>
<option value="10111011">1011 1011</option>
<option value="10110111">1011 0111</option>
<option value="01111110">0111 1110</option>
<option value="01111101">0111 1101</option>
<option value="01111011">0111 1011</option>
<option value="01110111">0111 0111</option>
<option value="01101111">0110 1111</option>
</select>

<select name="nibble4" id="nibble4">
<option value="11110110">1111 0110</option>
<option value="11101110">1110 1110</option>
<option value="11101101">1110 1101</option>
<option value="11101011">1110 1011</option>
<option value="11011110">1101 1110</option>
<option value="11011101">1101 1101</option>
<option value="11011011">1101 1011</option>
<option value="10111110">1011 1110</option>
<option value="10111101">1011 1101</option>
<option value="10111011">1011 1011</option>
<option value="10110111">1011 0111</option>
<option value="01111110">0111 1110</option>
<option value="01111101">0111 1101</option>
<option value="01111011">0111 1011</option>
<option value="01110111">0111 0111</option>
<option value="01101111">0110 1111</option>
</select>

<select name="nibble5" id="nibble5">
<option value="11110110">1111 0110</option>
<option value="11101110">1110 1110</option>
<option value="11101101">1110 1101</option>
<option value="11101011">1110 1011</option>
<option value="11011110">1101 1110</option>
<option value="11011101">1101 1101</option>
<option value="11011011">1101 1011</option>
<option value="10111110">1011 1110</option>
<option value="10111101">1011 1101</option>
<option value="10111011">1011 1011</option>
<option value="10110111">1011 0111</option>
<option value="01111110">0111 1110</option>
<option value="01111101">0111 1101</option>
<option value="01111011">0111 1011</option>
<option value="01110111">0111 0111</option>
<option value="01101111">0110 1111</option>
</select>

<select name="nibble6" id="nibble6">
<option value="11110110">1111 0110</option>
<option value="11101110">1110 1110</option>
<option value="11101101">1110 1101</option>
<option value="11101011">1110 1011</option>
<option value="11011110">1101 1110</option>
<option value="11011101">1101 1101</option>
<option value="11011011">1101 1011</option>
<option value="10111110">1011 1110</option>
<option value="10111101">1011 1101</option>
<option value="10111011">1011 1011</option>
<option value="10110111">1011 0111</option>
<option value="01111110">0111 1110</option>
<option value="01111101">0111 1101</option>
<option value="01111011">0111 1011</option>
<option value="01110111">0111 0111</option>
<option value="01101111">0110 1111</option>
</select>

<table><tr><td class="statusText">
<pre id="statusText">
</pre>
</td></tr></table>

</div>

</div>
</div>
<div id="buttons">
<input class="prevb" type="button" value="<< Previous" onclick="previous();">
<input class="nextb" type="button" value="Next >>" onclick="next();">
</div>
</form>
</body>
</html>
