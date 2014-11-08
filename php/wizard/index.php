<html>

<head>

<title>New Transmitter</title>

<link href="/ha/css/wizard.css" rel="stylesheet">
<link rel="stylesheet" href="/ha/jquery/css/redmond/jquery-ui-1.10.4.custom.min.css">
<link rel="stylesheet" href="/ha/jquery/jquery.pnotify.default.css">

<link rel="stylesheet" href="/ha/jquery/jquery.dropdown.css">



<script src="../jquery/js/jquery-1.10.2.js"></script>
<script src="../jquery/js/jquery-ui-1.10.4.custom.js"></script>
<script src="../jquery/jquery.dropdown.min.js"></script>

<script>
function next()
{
	//generateNewTransmitter();
	document.forms[0].submit();
}

</script>
</head>
<body>
<form method="post" action="step2.php">
<div id="header">
<a data-dropdown="#dropdown-1" class="menuicon" href="#">  &#9776;</a>
Step 1: Select/Create Room
</div>

<?php include '../menu.php';?>
<?php include '../config.php';?>

<div id="body">
 <div id="instructions">
 Select an existing room where the Lightwave RF device is situated, or create a new one if the room does not already exist
 </div>

<Table>
<tr class="headerrow"><td colspan="2" class="headerrowtext">Existing Room</td></tr>
<?php
// Connecting, selecting database
$conn = mysqli_connect($dbserv, $dbuser, $dbpass);
if(!$conn)
{
  die('Could not connect: ' . mysql_error($conn));

}
//echo 'Connected successfully';
mysqli_select_db($conn,$dbname); //or die('Could not select database');

// Performing SQL query
$query = 'SELECT * FROM room';
if(!$query)die('Could not query: ' . mysql_error($conn));
$result = mysqli_query($conn,$query); //or die('Query failed: ' . mysql_error());

if(!$result)
{
?>
<tr>
<td colspan="2">No existing rooms found</td>
</tr>
<?php
}

while ($line = mysqli_fetch_array($result)) 
{ ?>
<tr>
<td><input type="radio" name="room" value="<?php echo $line['ID'];  ?>"></td>
<td><?php echo $line['Description'];  ?>
</td>
</tr>
<?php } ?>

<tr class="headerrow"><td colspan="2" class="headerrowtext">New Room</td></tr>

<tr>
<td><input type="radio" name="room" value="new"></td>
<td><input type="text" name="roomDescription" class="descinput"></td>
</tr>
</Table>



</div>
<div id="buttons">
<input disabled="disabled" class="prevb" type="button" value="<< Previous" onclick="previous();">
<input class="nextb" type="button" value="Next >>" onclick="next();">
</div>
</form>
</body>
</html>
