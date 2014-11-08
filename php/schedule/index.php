<?php include "../config.php"; ?>
<html>
<?php

$sunsetTime = date_sunset(time(), SUNFUNCS_RET_STRING, 52.87, -1.66, 90, -0.5);

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

//Get the transmitters into an array from the query
$ids   = array();
$descs = array();

$conn = mysqli_connect($dbserv, $dbuser, $dbpass);
if(!$conn)
{
  die('Could not connect: ' . mysql_error($conn));

}
//echo 'Connected successfully';
mysqli_select_db($conn,$dbname); //or die('Could not select database');

// SQL query to get transmitters
$query = 'SELECT
pair.ID,
pair.Notes,
rec.Description as ReceiverDescription,
rtype.Description as TypeDescription,
trans.TransmitterID as transID,
room.Description as RoomDescription
FROM pairing as pair,
receiver as rec,
ReceiverTypes as rtype,
room as room,
transmitter as trans
WHERE
rec.ID = pair.ReceiverID AND
rtype.ID = rec.TypeID
and pair.TransmitterID = trans.ID
and rec.RoomID = room.ID
ORDER BY room.ID, pair.ID
';

if(!$query)die('Could not query: ' . mysql_error($conn));
$list = mysqli_query($conn,$query); //or die('Query failed: ' . mysql_error());

while ($options = mysqli_fetch_array($list))
{
  $ids[] =  $options['ID'].'-'.$options['transID'];
  $descs[] = $options['RoomDescription'].' / '.$options['ReceiverDescription'].'('.$options['TypeDescription'].') / '.$options['Notes'];
}


// _______________________________________________________________________________

$query = 'SELECT * from schedule';

if(!$query)die('Could not query: ' . mysql_error($conn));
$list = mysqli_query($conn,$query); //or die('Query failed: ' . mysql_error());


?>
<head>

<title>Schedule</title>

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
      
  var topid = document.getElementById("scheduleCount").value;
  for(i=1;i<=topid;i++)
  {
     document.getElementById('mins' + i).disabled=false;
     document.getElementById('hours' + i).disabled=false;
  }
  document.getElementById('minsNEW').disabled=false;
  document.getElementById('hoursNEW').disabled=false;

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

function timeToggle(id,sunsetcheck)
{
  if(sunsetcheck.checked)
  {
    //alert('is checked');
    //disable the time fields
    //alert(id);
    document.getElementById('mins' + id).disabled=true;
    document.getElementById('hours' + id).disabled=true;
  }
  else
  {
    document.getElementById('mins' + id).disabled=false;
    document.getElementById('hours' + id).disabled=false;
  }


}

</script>
</head>
<body>
<form method="post" action="step2.php">
<div id="header">
<a data-dropdown="#dropdown-1" class="menuicon" href="#">  &#9776;</a> Schedule
</div>

<?php include '../menu.php'; ?>

<div id="body">
 <div id="instructions">
The schedule is configured as follows:
</div>



<!-- =================== EXISTNG SCHEDULE ======================== -->

<?php
$schedId = 0;

while ($options = mysqli_fetch_array($list))
{
$schedId++;

$ID=$options['ID'];

?>
<input type="hidden" name="id<?php echo $schedId;?>" value="<?php echo $ID;?>">

<div class="<?php if($options['enabled']=='1')echo "enabledGroupHeader" ?> scheduleGroupHeader" onclick="showHide('schedule<?php echo $schedId;?>','icon<?php echo $schedId;?>')">
<span class="scheduleGroupIcon" id="icon<?php echo $schedId;?>">+</span>
<?php echo $schedId;?>: <?php echo $options['Notes']; ?>
</div>
<Table class="<?php if($options['enabled']=='1')echo "enabledGroup"?> scheduleGroup" id="schedule<?php echo $schedId;?>" style="display:none;">

<tr>
<td class="scheduleTable">Enabled</td>
<td>
<?php writeCheckbox(1,'enabled'.$schedId,$options['enabled']);?>
</td>
</tr>

<tr>
<td class="scheduleTable">Delete</td>
<td>
<?php writeCheckbox(1,'delete'.$schedId,0);?>
</td>
</tr>

<tr>
<td class="scheduleTable">Time</td>
<td>
<!-- time -->

<select <?php if($options['Hour']=='99') echo 'disabled="disabled"'; ?> name="hours<?php echo $schedId;?>" id="hours<?php echo $schedId;?>">
<?php
$counter=0;

for($counter=0;$counter<=23;$counter++)
{
 echo '<option';
 if($counter==$options['Hour']) echo ' selected';
 echo '>'.$counter.'</option>';
}?>
</select>:<select <?php if($options['Hour']=='99') echo 'disabled="disabled"'; ?> name="mins<?php echo $schedId;?>" id="mins<?php echo $schedId;?>">

<option<?php if($options['Minute']==0)echo ' selected';?>>0</option>
<option<?php if($options['Minute']==15)echo ' selected';?>>15</option>
<option<?php if($options['Minute']==30)echo ' selected';?>>30</option>
<option<?php if($options['Minute']==45)echo ' selected';?>>45</option>
</select>
or Sunset: <input type="checkbox" value="true" id="sunset<?php echo $schedId;?>" name="sunset<?php echo $schedId;?>" onclick="timeToggle('<?php echo $schedId;?>',this)" <?php if($options['Hour']=='99') echo 'checked'; ?>>
(<?php echo $sunsetTime ?>)

</td>
</tr>

<tr>
<td class="scheduleTable">Day of Week</td>
<td>
<select name="day<?php echo $schedId;?>">
<option value="">Please Select...</option>
<?php writeOption('*','Everyday',$options['dow']);?>
<?php writeOption('0,6','Weekend (Sat/Sun)',$options['dow']);?>
<?php writeOption('1,2,3,4,5','Weekdays (M/T/W/TH/F) ',$options['dow']);?>
<optgroup label="-----------------"></optgroup>
<?php writeOption('1','Monday',$options['dow']);?>
<?php writeOption('2','Tuesday',$options['dow']);?>
<?php writeOption('3','Wednesday',$options['dow']);?>
<?php writeOption('4','Thursday',$options['dow']);?>
<?php writeOption('5','Friday',$options['dow']);?>
<?php writeOption('6','Saturday',$options['dow']);?>
<?php writeOption('0','Sunday',$options['dow']);?>
</select>
</td>
</tr>

<tr>
<td class="scheduleTable">Transmitter</td>
<td>
<select name="transmitter<?php echo $schedId;?>">
<option value="">Please Select...</option>
<?php
$i = 0;
foreach ($ids as $v) {

$idtoCheck=$options['pairId'].'-'.$options['transmitter'];
writeOption($v, $descs[$i],$idtoCheck);
$i++;
}
?>

</select>
</td>
</tr>
<tr>
<td class="scheduleTable">On/Off/Dim</td>
<td>
<select name="command<?php echo $schedId;?>">

<optgroup label="on/off"></optgroup>
<optgroup label="-----------------"></optgroup>
<?php writeOption('000','Off',$options['command']);?>
<?php writeOption('100','On', $options['command']);?>

<optgroup label="Dim"></optgroup>
<optgroup label="-----------------"></optgroup>
<?php writeOption('101','Dim 1%',  $options['command']);?>
<?php writeOption('106','Dim 20%', $options['command']);?>
<?php writeOption('112','Dim 40%', $options['command']);?>
<?php writeOption('118','Dim 60%', $options['command']);?>
<?php writeOption('124','Dim 80%', $options['command']);?>
<?php writeOption('131','Dim 100%',$options['command']);?>

</select>
</td>
</tr>
<tr>
<td class="scheduleTable">Notes</td>
<td>
<input type="text" name="notes<?php echo $schedId;?>" size="80" value="<?php echo $options['Notes']; ?>">
</td>
</tr>
</table>
<br>

<?php
}
?>

<input type="hidden" id="scheduleCount" name="scheduleCount" value="<?php echo $schedId?>">

<!-- ================= NEW SCHEDULE ITEM ========================== -->
<?php
$ID='NEW';
$schedId='NEW';
?>

<div class="scheduleGroupHeader" onclick="showHide('schedule<?php echo $schedId;?>','icon<?php echo $schedId;?>')">
<span class="scheduleGroupIcon" id="icon<?php echo $schedId;?>">-</span>
New Schedule Entry?
</div>
<Table class="newschedule" id="schedule<?php echo $schedId;?>">

<tr>
<td colspan="2">New Schedule Item:</td>
<input type="hidden" name="enabledNEW" value="1">
</tr>

<tr>
<td class="scheduleTable">Time</td>
<td>
<!-- time -->
<select  id="hours<?php echo $ID;?>" name="hours<?php echo $ID;?>">
<?php
$counter=0;

for($counter=0;$counter<=23;$counter++)
{
 echo '<option';
 echo '>'.$counter.'</option>';
}?>
</select>:<select  id="mins<?php echo $ID;?>" name="mins<?php echo $ID;?>">

<option>0</option>
<option>15</option>
<option>30</option>
<option>45</option>
</select>
or Sunset: <input type="checkbox" id="sunset<?php echo $ID;?>" name="sunset<?php echo $ID;?>" onclick="timeToggle('<?php echo $ID;?>',this)">
</td>
</tr>

<tr>
<td class="scheduleTable">Day of Week</td>
<td>
<select name="day<?php echo $ID;?>">
<option value="">Please Select...</option>
<?php writeOption('*','Everyday','');?>
<?php writeOption('0,6','Weekend (Sat/Sun)','');?>
<?php writeOption('1,2,3,4,5','Weekdays (M/T/W/TH/F) ','');?>
<optgroup label="-----------------"></optgroup>
<?php writeOption('1','Monday','');?>
<?php writeOption('2','Tuesday','');?>
<?php writeOption('3','Wednesday','');?>
<?php writeOption('4','Thursday','');?>
<?php writeOption('5','Friday','');?>
<?php writeOption('6','Saturday','');?>
<?php writeOption('0','Sunday','');?>
</select>
</td>
</tr>

<tr>
<td class="scheduleTable">Transmitter</td>
<td>
<select name="transmitter<?php echo $ID;?>">
<option value="">Please Select...</option>
<?php
$i = 0;
foreach ($ids as $v) {

writeOption($v, $descs[$i],'');
$i++;
}
?>

</select>
</td>
</tr>
<tr>
<td class="scheduleTable">On/Off/Dim</td>
<td>
<select name="command<?php echo $ID;?>">

<optgroup label="on/off"></optgroup>
<optgroup label="-----------------"></optgroup>
<?php writeOption('000','Off','');?>
<?php writeOption('100','On','');?>

<optgroup label="Dim"></optgroup>
<optgroup label="-----------------"></optgroup>
<?php writeOption('101','Dim 1%',  '');?>
<?php writeOption('106','Dim 20%', '');?>
<?php writeOption('112','Dim 40%', '');?>
<?php writeOption('118','Dim 60%', '');?>
<?php writeOption('124','Dim 80%', '');?>
<?php writeOption('131','Dim 100%','');?>

</select>
</td>
</tr>

<tr>
<td class="scheduleTable">Notes</td>
<td>
<input type="text" name="notes<?php echo $ID;?>" size="80" value="">
</td>
</tr>


</Table>

<br>

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

