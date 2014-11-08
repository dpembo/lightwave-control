<?php include "../config.php"; ?>
<html>
<?php

function writeOption($val,$desc,$selectedVal)
{
	echo '<option value="'.$val.'"';
	if($val==$selectedVal)echo ' selected';
	echo'>';
	echo $desc;
	echo '</option>';
}

function writeCheckbox($val,$name,$selectedVal)
{
	echo '<input type="checkbox" value="1" name="'.$name.'"';
	if($val==$selectedVal)echo ' checked';
	echo '>';
	//.$desc;
}


$conn = mysqli_connect($dbserv, $dbuser, $dbpass);
if(!$conn)
{
  die('Could not connect: ' . mysql_error($conn));

}
//echo 'Connected successfully';
mysqli_select_db($conn,$dbname); //or die('Could not select database');


// _______________________________________________________________________________


?>
<head>

<title>Maintenance</title>

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

function showHide(inId,iconId)
{
	elem = document.getElementById(inId);
	icon = document.getElementById(iconId);
//alert(icon);
//alert(icon.innerHTML);

	vis = elem.style.display;
	if(vis=='none')
	{
		elem.style.display='block';

		icon.innerHTML="-";

	}
	else
	{
		elem.style.display='none';
		icon.innerHTML="+";
	}
}


</script>
</head>
<body>
<form method="post" action="step2.php">
<div id="header">
<a data-dropdown="#dropdown-1" class="menuicon" href="#">  &#9776;</a> Maintenance
</div>

<?php include '../menu.php'; ?>

<div id="body">
 <div id="instructions">
Please Select a maintenance option below:
 </div>

<div class="menubuttons">
<input type="button" class="optionbutton" value="Rooms" onclick="document.location.href='rooms.php';">
<input type="button" class="optionbutton" value="Receivers" onclick="document.location.href='recievers.php';">
<input type="button" class="optionbutton" value="Receiver Types" onclick="document.location.href='receiverTypes.php';">
<input type="button" class="optionbutton" value="Transmitters" onclick="document.location.href='transmitters.php';">
<input type="button" class="optionbutton" value="Pairing" onclick="document.location.href='pairing.php';">
<!--<input type="button" class="optionbutton" value="Schedule" onclick="document.location.href='schedule.php';">-->
</div>

</div>






</div>
<div id="buttons">
<input disabled="disabled" class="prevb" type="button" value="<< Previous" onclick="previous();">
<input class="nextb" type="button" value="Next >>" onclick="next();">
</div>
</form>
</body>
</html>

