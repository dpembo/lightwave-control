<?php include 'config.php';?>

<?php
// Connecting, selecting database
$editconn = mysqli_connect($dbserv, $dbuser, $dbpass);
if(!$editconn)
{
  die('Could not connect: ' . mysql_error($editconn));

}
//echo 'Connected successfully';
mysqli_select_db($editconn,$dbname); //or die('Could not select database');

// Performing SQL query
$editquery = 'SELECT * FROM room';
if(!$editquery)die('Could not query: ' . mysql_error($editconn));
$editresult = mysqli_query($editconn,$editquery); //or die('Query failed: ' . mysql_error());

if(!$editresult)
{
?>
<!--no rooms found--><?php
}

//while ($line = mysqli_fetch_array($roomresult)) 
{ ?>
<option value="<?php echo $roomline['ID'];?>"><?php echo $roomline['Description'];?></option>
<?php } ?>
