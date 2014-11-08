<?php include "../config.php" ?>
<html>

<head>
<link href="/ha/css/wizard.css" rel="stylesheet">

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


// Performing SQL query

$transId = $_POST['transmitterID'];

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

.statusTextVisible
{
        background-color:#DDDDDD;
        height:50px;
        border-top:2px solid #444444;
        margin-top: 10px;
        width: 300px;

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

function previous()
{
	history.back(-2);
}
function next()
{
	//generateNewTransmitter();
	//document.forms[0].submit();
	document.location.replace('/ha/');
}

function test(inId, inNotes, onOff)
{
document.getElementById('pairButton').disabled=true;
document.getElementById('teston').disabled=true;
document.getElementById('testoff').disabled=true;
document.getElementById('pairImage').style.visibility='visible';

document.getElementById('pairText').innerHTML="Issuing a Test For\n" + inId + "\n"
 + inNotes;

xmlhttp=new XMLHttpRequest();
xmlhttp.open("GET","/ha/ha.php?id=" + inId + "&onoff=" + onOff + "&dim=00&room=PAIRING&message=Pairing+Command",false);
xmlhttp.send();


document.getElementById('pairText').innerHTML="Test Complete\n" + inId + "\n"
 + inNotes;

document.getElementById('pairImage').style.visibility='hidden';
document.getElementById('teston').disabled=false;
document.getElementById('testoff').disabled=false;
document.getElementById('pairButton').disabled=false;
}


function pair(inId, inNotes)
{
document.getElementById('pairButton').disabled=true;
document.getElementById('testButtons').style.visibility='hidden';
document.getElementById('pairImage').style.visibility='visible';
document.getElementById('pairText').innerHTML="Issuing Pairing Command for\n" + inId + "\n"
 + inNotes;
xmlhttp=new XMLHttpRequest();
xmlhttp.open("GET","/ha/ha.php?id=" + inId + "&onoff=1&dim=00&room=PAIRING&message=Pairing+Command",false);
xmlhttp.send();

document.getElementById('pairText').innerHTML="Pair command Transmitted.  If Receiver is not paired please retry";

document.getElementById('pairButton').disabled=false;
document.getElementById('pairImage').style.visibility='hidden';
document.getElementById('testButtons').style.visibility='visible';

}

</script>
</head>
<body>
<form method="post" action="complete.php">

<?php
$query = 'SELECT * FROM transmitter where ID='.$transId;
if(!$query)die('Could not query: ' . mysql_error($conn));
$result = mysqli_query($conn,$query); //or die('Query failed: ' . mysql_error());
?>

<input type="hidden" name="roomId" value="<?php echo $_POST["roomId"];?>">
<input type="hidden" name="roomDescription" value="<?php echo $_POST["roomDescription"];?>">
<input type="hidden" name="receiverId" value="<?php echo $_POST["receiverId"];?>">
<input type="hidden" name="receiverDescription" value="<?php echo $_POST["receiverDescription"];?>">
<input type="hidden" name="transmitterID" value="<?php echo $_POST["transmitterID"];?>">
<input type="hidden" name="newTransmitterID" value="<?php echo $_POST["newTransmitterID"];?>">

<div id="header">
Step 4: Activation
</div>
<div id="body">
 <div id="instructions">
Initiate pairing mode on the Lightwave RF device by following the instructions contained within the device packaging, then transmit the pairing code using the button below.  If the pairing mode on the device times out before pairing, please retry. 
</div>


<?php
while ($line = mysqli_fetch_array($result))
{ ?>


<span>
<input id="pairButton" type="button" onclick="pair('<?php echo $line['TransmitterID'];?>','<?php echo $line['Notes'];?>');" value="Transmit Code For Pairing">
<img src="/ha/images/ajax-loader.gif" id="pairImage" style="visibility:hidden;">
</span>

<br>
<div id="testButtons" style="visibility:hidden;">
<h3>Test Pairing</h3>
<div id="testButtons">
<input type="button" onclick="test('<?php echo $line['TransmitterID'];?>','<?php echo $line['Notes'];?>','0');" value="Off" id="testoff">
&nbsp;
<input type="button" onclick="test('<?php echo $line['TransmitterID'];?>','<?php echo $line['Notes'];?>','1');" value="On" id="teston">
</div>

</div><br>
<?php
}
?>


<table class="statusTextVisible">
<tr class="statusTextVisible">
<td>
<pre id="pairText"></pre>
</td>
</tr>
</table>

</div>
<div id="buttons">
<input class="prevb" type="button" disabled="disabled" value="<< Previous" onclick="previous();">
<input class="nextb" type="button" value="Finish >>" onclick="next();">
</div>
</form>
</body>
</html>
