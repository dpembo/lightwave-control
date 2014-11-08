<?php include "../config.php"; ?>

<html>

<head>

<link href="/ha/css/wizard.css" rel="stylesheet">

<?php
// Connecting, selecting database
$conn = mysqli_connect($dbserv, $dbuser, $dbpass);
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

?>


<title><?php echo $ids; ?> Setup Wizard</title>
<style>
#allnibbles
{
	display:none;
}

.statusText
{
	background-color:#DDDDDD;
	height:50px;
	border-top:2px solid #444444;
	margin-top: 10px;
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
	history.back();
}
function next()
{
	//generateNewTransmitter();
	document.forms[0].submit();
}

</script>
</head>
<body>
<form method="post" action="step3.php">
<input type="hidden" name="roomId" value="<?php echo $_POST["room"];?>">
<input type="hidden" name="roomDescription" value="<?php echo $_POST["roomDescription"];?>">
<div id="header">
Step 2: Select/Create Receiver
</div>
<div id="body">
 <div id="instructions">
 Select an existing Lightwave RF device from the selected room, or create a new 
device
 </div>

<Table>
<tr class="headerrow"><td class="headerrowtext" colspan="2">Existing Lightwave RF Receivers</td></tr>

<?php
// Performing SQL query
$query = 'SELECT * FROM receiver where RoomID='.$_POST["room"];
if(!$query)die('Could not query: ' . mysql_error($conn));
$result = mysqli_query($conn,$query); //or die('Query failed: ' . mysql_error());

if(!$result)
{
?>
<tr><td colspan="2">
No existing receivers
</td></tr>
<?php 
}

?>

<?php
while ($line = mysqli_fetch_array($result)) 
{ ?>
<tr>
<td><input type="radio" name="receiver" value="<?php echo $line['ID'];  ?>"></td>
<td><?php echo $line['Description'];  ?>
</td>
<td>
(<?php
$i = 0; 
foreach ($ids as $v) {
?>
<?php  
if($v==$line['TypeID']){echo $descs[$i];}
$i++;
}
?>
)
</td>

</tr>
<?php } ?>
<tr class="headerrow"><td class="headerrowtext" colspan="2">New Reciever</td></tr>

<tr>
<td><input type="radio" name="receiver" value="new"></td>
<td><input type="text" class="descinput" name="receiverDescription"></td>
<td>
<select name="typenew" id="typenew">
<Option value="">Please Select...</Option>
<?php
$i = 0;
foreach ($ids as $v) {
?>
<Option value="<?php echo $v;?>">
<?php echo $descs[$i]; ?> </Option>
<?php
$i++;
}
?>
</select>

</td>
</tr>
</Table>
 </div>
<div id="buttons">
<input class="prevb" type="button" value="<< Previous" onclick="previous();">
<input class="nextb" type="button" value="Next >>" onclick="next();">
</div>
</form>
</body>
</html>
