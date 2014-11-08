<?php include 'config.php';?>

<?php
// Connecting, selecting database
$roomconn = mysqli_connect($dbserv, $dbuser, $dbpass);
if(!$roomconn)
{
  die('Could not connect: ' . mysql_error($roomconn));

}
//echo 'Connected successfully';
mysqli_select_db($roomconn,$dbname); //or die('Could not select database');

// Performing SQL query
$roomquery = 'SELECT * FROM room';
if(!$roomquery)die('Could not query: ' . mysql_error($roomconn));
$roomresult = mysqli_query($roomconn,$roomquery); //or die('Query failed: ' . mysql_error());

if(!$roomresult)
{
?>
<!--no rooms found--><?php
}

while ($roomline = mysqli_fetch_array($roomresult)) 
{ ?>
<option value="<?php echo $roomline['ID'];?>"><?php echo $roomline['Description'];?></option>
<?php } ?>
