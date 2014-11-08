<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
include "php_serial.class.php";
include "config.php";


$id='';
$onoff='';
$dim='';
$message='';
$room='';


//-------------------------------------------------------------------------
//Does the database exist?
//-------------------------------------------------------------------------
$conn = mysqli_connect($dbserv, $dbuser, $dbpass);

if(!$conn)
{
  //die('Could not connect: ' . mysql_error($conn));
  //trigger_error(mysql_error($conn),E_USER_ERROR);
   header( 'Location: setup/index.php') ;
   die();
}

mysqli_select_db($conn,$dbname);

//Lets query the types table to see if it exists already
//if so, loop around to the start and prompt for recreation of db
$query = 'SELECT COUNT(SCHEMA_NAME) as reccount FROM INFORMATION_SCHEMA.SCHEMATA
WHERE SCHEMA_NAME="'.$dbname.'"';

if($typeList = mysqli_query($conn,$query))
{
        //Queried
}
else
{
        //trigger_error("Error Querying Datbase: ".mysqli_error($conn),E_USER_ERROR);
	header( 'Location: setup/index.php') ;
	die();
}

while ($options = mysqli_fetch_array($typeList))
{
  $reccount =  $options['reccount'];
}
$total = $reccount + 0;

if($total>0)
{
	//Db exits
}
else
{
	header( 'Location: setup/index.php') ;
	die();
}


//---------------------------------------------------------------------
//---------------------------------------------------------------------


// Connecting, selecting database
$conn = mysqli_connect($dbserv, $dbuser, $dbpass);
if(!$conn)
{
  die('Could not connect: ' . mysql_error($conn));

}
//echo 'Connected successfully';
mysqli_select_db($conn,$dbname); //or die('Could not select database');

// Performing SQL query
$query = '
SELECT 
   room.ID,
   room.Description,
   room.Order
FROM 
   room as room, 
   receiver as rec
WHERE 
   room.ID = rec.RoomID
GROUP BY
   room.ID
HAVING
   count(rec.RoomID)>0
ORDER BY
  room.Order
';
if(!$query)die('Could not query: ' . mysql_error($conn));
$result = mysqli_query($conn,$query); //or die('Query failed: ' . mysql_error());

if(!$result)
{
	header( 'Location: wizard/index.php') ;
	die();
}
?>

<html lang="en">
<head>

<meta charset="utf-8">
<title>Lightwave RF - Arduino Home Automation</title>
<link rel="stylesheet" href="css/default.css">
<link rel="stylesheet" href="jquery/css/black-tie/jquery-ui-1.10.4.custom.min.css">
<link rel="stylesheet" href="jquery/jquery.pnotify.default.css">
<link rel="stylesheet" href="jquery/jquery.dropdown.css">

<!--<link href="bootstrap/css/bootstrap.css" id="bootstrap-css" rel="stylesheet" type="text/css" />
<link href="bootstrap/css/bootstrap-responsive.css" rel="stylesheet" type="text/css" />
-->
<style>
.ui-pnotify-history-containerwell
{
	display:none;
}
</style>


<script src="jquery/js/jquery-1.10.2.js"></script>
<script src="jquery/js/jquery-ui-1.10.4.custom.js"></script>
<script src="jquery/jquery.pnotify.js"></script>
<script src="jquery/jquery.dropdown.min.js"></script>
<script>

//$(function() {
//  $( "#dialog" ).dialog({
//    height: 140,
//    autoOpen: false,
//    modal: true
//  }
//);
//});

var editId='NOT SET';
var editDesc='NOT SET';
var editRecv='NOT SET';
var editRoom='NOT SET';

$(function(){
        var editexecute = function() {
                //alert('This is Ok button');
		editDesc = document.getElementById('editDesc').value;
		editRecv = document.getElementById('editType').value;
		editRoom = document.getElementById('editRoom').value;
                document.location.href='editTransmitter.php?id=' + editId + '&desc=' + editDesc + '&recv=' + editRecv + '&room=' + editRoom;
        }
        var editcancel = function() {
                //alert('This is Cancel button');
                 $('#editdialog').dialog('close');
        }

        var dialogOpts = {
                height: 240,
                width: 400,
                autoOpen: false,
                modal: true,
                buttons: {
                        "Save": editexecute,
			"Delete": openDeleteDialog,
                        "Cancel": editcancel
                }
        };

        $("#editdialog").dialog(dialogOpts);
});


function openEditDialog(transmitterId,pairDesc,receiverType,roomId)
{
 //Change the dialog to show the pairdesc
 //document.getElementById('deletename').innerHTML=pairdesc;
 editId=transmitterId;
 editDesc = pairDesc;
 editRoom = roomId;
 editRecv = receiverType;

// document.getElementById('').value = editId;
 document.getElementById('editId').vaule = editId;
 document.getElementById('editDesc').value = editDesc;
 document.getElementById('editRoom').value = editRoom;
 document.getElementById('editType').value = editRecv;

 $('#editdialog').dialog('open');
}


$(function(){
	var execute = function() {
		//alert('This is Ok button');
		//document.location.href='deleteTransmitter.php?id=' + editId;
		deleteTransmitter(editId,editDesc);
	}
	var cancel = function() {
		//alert('This is Cancel button');
		 $('#deletedialog').dialog('close');
	}
	var dialogOpts = {
		height: 240,
		width: 400,
    		autoOpen: false,
    		modal: true,
		buttons: {
			"Delete": execute,
			"Cancel": cancel
		}
	};
	
	$("#deletedialog").dialog(dialogOpts);
});


function openDeleteDialog()
{
 //Change the dialog to show the pairdesc
 document.getElementById('deletename').innerHTML=editDesc;
 deleteId=editId;
 $('#editdialog').dialog('close');
 $('#deletedialog').dialog('open');
}

function s4() {
  return Math.floor((1 + Math.random()) * 0x10000)
             .toString(16)
             .substring(1);
};

function guid() {
  return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
         s4() + '-' + s4() + s4() + s4();
}

function wait()
{
}


function transmitCompleteReload(inId,desc)
{
  if(xmlHttp.readyState==4)
  {
      //document.getElementById('disablingDiv').className='disablingDivOff';
      //document.getElementById('spinnerimage').className='loadingOff';
      doNotify(desc);
      document.location.reload();
  }

}


function transmitComplete(inId,onOff,dim,room,desc)
{
  if(xmlHttp.readyState==4)
  {
      document.getElementById('disablingDiv').className='disablingDivOff';
      document.getElementById('spinnerimage').className='loadingOff';
      doNotify(desc);
  }

}

var xmlHttp= new XMLHttpRequest();
function transmitCode(inId,onOff,dim,room,desc)
{
  var uuid=guid();
  document.getElementById('disablingDiv').className='disablingDivOn';
  document.getElementById('spinnerimage').className='loadingOn';
  xmlHttp.open("GET","/ha/ha.php?id=" + inId + "&onoff=" + onOff + "&dim=" + dim + "&room=" + room + "&message=" + desc + "&uuid=" + uuid,true);
  xmlHttp.onreadystatechange = function () {transmitComplete(inId,onOff,dim,room,desc);};
  xmlHttp.send();
}

function transmitQueue(inId,onOff,dim,room,desc)
{
  var uuid=guid();
  //document.getElementById('pairImage').style.visibility='visible';
  document.getElementById('disablingDiv').className='disablingDivOn';
  document.getElementById('spinnerimage').className='loadingOn';
  xmlHttp.open("GET","/ha/haqueue.php?id=" + inId + "&onoff=" + onOff + "&dim=" + dim + "&room=" + room + "&message=" + desc + "&uuid=" + uuid,true);
  xmlHttp.onreadystatechange = function () {transmitComplete(inId,onOff,dim,room,desc);};
  xmlHttp.send();
}

function deleteTransmitter(inId,desc)
{
  var uuid=guid();
  //document.getElementById('pairImage').style.visibility='visible';
  document.getElementById('disablingDiv').className='disablingDivOn';
  document.getElementById('spinnerimage').className='loadingOn';
  xmlHttp.open("GET","/ha/delete.php?id=" + inId + "&message=" + desc);
  xmlHttp.onreadystatechange = function () {transmitCompleteReload(inId,desc);};
  xmlHttp.send();
}


function doNotify(message)
{
    $.pnotify({
    title: 'Command Issued',
    text: message,
    type: 'success',
    history: false,
    styling: 'jqueryui'
    });
}


$(function() {
$( "#accordion" ).accordion({
heightStyle: "content",
collapsible: true,
autoHeight: false,
active: false
});
});
</script>

</head>
<body >
<!-- DIV to block input during command sned --> 
<div id="header" >
<a data-dropdown="#dropdown-1" class="menuicon" href="#">  &#9776;</a> Device Contol
</div>

<div id="disablingDiv" class="disablingDivOff">
</div>

<div class="loadingOff" id="spinnerimage">
<span class="waittext">Issuing Command - Please Wait... </span>
<img id="pairImage" src="/ha/images/ajax-loader.gif">
</div>

<?php include 'menu.php';?>

<!--<div id="buttons">
<input disabled="disabled" class="prevb" type="button" value="<< Previous" onclick="previous();">
<input class="nextb" type="button" value="Next >>" onclick="next();">
</div>-->

<div id="accordianBody">

<div id="accordion">

<?php
while ($line = mysqli_fetch_array($result)) 
{
$roomQuery = 'SELECT * FROM receiver WHERE RoomID=' . $line['ID'];
$roomQuery = '
SELECT 
pair.ID as PairID,
rec.ID as ReceiverID,
rec.Description as ReceiverDescription,
trans.TransmitterID as TransmitterID,
types.ID as ReceiverType,
types.Description as TypeDescription,
pair.Notes as PairDescription
FROM receiver as rec,pairing as pair, ReceiverTypes as types, transmitter as trans
where pair.ReceiverID=rec.ID and
pair.TransmitterID = trans.ID
and trans.Enabled = 1 
and rec.TypeID = types.ID
and rec.RoomID = ' .$line['ID'].'
order by PairID asc';
$result2 = mysqli_query($conn,$roomQuery);

?><h3><?php echo $line['Description'];  ?>
</h3>
<div>

<?php
$transmitterQueue = "";
$numRows = mysqli_num_rows($result2);
?>
<!--<b><?php echo $numRows;?></b>-->
<?php
$rowCount = 0;

while ($receiverLine = mysqli_fetch_array($result2))
{
$rowCount++;
$transmitterQueue = $transmitterQueue.$receiverLine['TransmitterID'];
if($numRows != $rowCount)
{
  $transmitterQueue = $transmitterQueue.",";
}
else
{
  $transmitterQueue = $transmitterQueue."*";
}
?>

<span class="pairgroup">
<span class="Pairdesc"><a href="javascript:openEditDialog('<?php echo $receiverLine['TransmitterID']; ?>','<?php echo $receiverLine['PairDescription']; ?>','<?php echo $receiverLine['ReceiverType']?>','<?php echo $line['ID'] ?>')"><img class="editIcon" src="images/edit.png"></a>
<!--<a href="javascript:openDeleteDialog('<?php echo $receiverLine['TransmitterID']; ?>','<?php echo $receiverLine['PairDescription']; ?>')"><img class="trashIcon" src="images/trash.png"></a>--><?php echo $receiverLine['PairDescription']; ?><p></p></span>

<!--<p class="Pairdesc"><?php echo $receiverLine['PairDescription']; ?></p>-->
<!--<span>-->
<a href="javascript:transmitCode('<?php echo $receiverLine['TransmitterID']; ?>','0','00','<?php echo $line['Description'];?>','<?php echo $receiverLine['ReceiverDescription'];?> Off');"><img border="0" src="images/off.png"></a>
<span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
<a href="javascript:transmitCode('<?php echo $receiverLine['TransmitterID']; ?>','1','00','<?php echo $line['Description'];?>','<?php echo $receiverLine['ReceiverDescription'];?> On');">
<img border="0" src="images/on.png"></a>
<!--</span>-->

<?php
if ($receiverLine['ReceiverType'] == 2)
{ ?>
 <div>
 <a href="javascript:transmitCode('<?php echo $receiverLine['TransmitterID']; ?>','1','01','<?php echo $line['Description']; ?>','<?php echo $receiverLine['ReceiverDescription']; ?> Dim Value 1%');"><span class="s0"></span></a>
   <a href="javascript:transmitCode('<?php echo $receiverLine['TransmitterID']; ?>','1','06','<?php echo $line['Description']; ?>','<?php echo $receiverLine['ReceiverDescription']; ?> Dim Value 20%');"><span class="s1"></span></a>
   <a href="javascript:transmitCode('<?php echo $receiverLine['TransmitterID']; ?>','1','12','<?php echo $line['Description']; ?>','<?php echo $receiverLine['ReceiverDescription']; ?> Dim Value 40%');"><span class="s2"></span></a>
   <a href="javascript:transmitCode('<?php echo $receiverLine['TransmitterID']; ?>','1','18','<?php echo $line['Description']; ?>','<?php echo $receiverLine['ReceiverDescription']; ?> Dim Value 60%');"><span class="s3"></span></a>
   <a href="javascript:transmitCode('<?php echo $receiverLine['TransmitterID']; ?>','1','24','<?php echo $line['Description']; ?>','<?php echo $receiverLine['ReceiverDescription']; ?> Dim Value 80%');"><span class="s4"></span></a>
   <a href="javascript:transmitCode('<?php echo $receiverLine['TransmitterID']; ?>','1','31','<?php echo $line['Description']; ?>','<?php echo $receiverLine['ReceiverDescription']; ?> Dim Value 100%');"><span class="s5"></span></a>
 </div>

 <div style="clear:left;">
  <span class="zerop">0%</span>
  <span class="zerop">50%</span>
  <span>100%</span>
 </div>

<?php 
} //end if
?>
</span>
<?php
}
?>

<?php
if (mysqli_num_rows($result2)>1)
{
?>
<span class="pairgroupwhole">

<p class="Pairdesc">Whole Room</p>
<!--<span>-->
<a href="javascript:transmitQueue('<?php echo $transmitterQueue ?>','0','00','<?php echo $line['Description'];?>','<?php echo $line['Description'];?> Off');"><img border="0" src="images/off.png"></a>
<span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
<a href="javascript:transmitQueue('<?php echo $transmitterQueue ?>','1','00','<?php echo $line['Description'];?>','<?php echo $line['Description'];?> On');">
<img border="0" src="images/on.png"></a>
</span>
<?php
}
?>
</div>

<?php } ?>


</div><!--accordian-->
</div><!--body-->

<div id="deletedialog" title="Confirm Delete" style="display:none">Are you sure you want to delete <br><br><b id="deletename">xxx</b>?<br><br>  This action cannot be undone.</div> 

<div id="editdialog" title="Edit" style="display:none">
<input type="hidden" id="editId" name="editId">
<table border="0">
<tr>
<td>Description: </td>
<td><input type="text" id="editDesc" name="editDesc"></td>
</tr>
<tr>
<td>Room: </td>
<td><select name="editRoom" id="editRoom"><?php include "rooms.php";?></select></td>
</tr>
<tr>
<td>Type: </td>
<td><select name="editType" id="editType"><?php include "types.php";?></select></td>
</tr>
</table>
</div>

</body>
</html>
