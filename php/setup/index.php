<html>

<head>

<title>Install</title>

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
<a data-dropdown="#dropdown-1" class="menuicon" href="#">  &#9776;</a> Installation
</div>

<?php include '../menu.php'; ?>

<div id="body">
 <div id="instructions">
<span class="errorText">It looks like you've just installed the Lightwave RF controller, or there is a problem with the configuration or active database</span>
<br><br>
Please provide the database settings and press continue.
</div>

<Table>

<tr class="headerrow"><td colspan="2" class="headerrowtext">MySQL Database Settings</td></tr>

<tr>
<td>Database Server</td>
<td><input type="text" name="dbserv" class="descinput" value="localhost"></td>
</tr>

<tr>
<td>Database Name</td>
<td><input type="text" name="dbname" class="descinput" value="lightwaverf">
</td>
</tr>

<tr>
<td></td>
<td> <input type="checkbox" name="drop"> Replace existing DB?
</td>
</tr>

<tr>
<td>Database User</td>
<td><input type="text" name="dbuser" class="descinput" value="root"></td>
</tr>

<tr>
<td>Database Passord</td>
<td><input type="password" name="dbpass" class="descinput"></td>
</tr>

<tr class="headerrow"><td colspan="2" class="headerrowtext">Arduino Settings</td></tr>

<tr>
<td>COM Port</td>
<td><input type="text" name="serialcom" class="descinput" value="/dev/ttyACM0"></td>
</tr>

<tr>
<td>COM Port Speed</td>
<td><input type="text" name="serialbaud" value="9600"></td>
</tr>
</table>

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
<input class="nextb" type="button" value="Next >>" onclick="next();">
</div>
</form>
</body>
</html>

