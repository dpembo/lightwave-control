<?php include "config.php"; ?>
<?php
// Connecting, selecting database
$typeconn = mysqli_connect($dbserv, $dbuser, $dbpass);
if(!$typeconn)
{
  die('Could not connect: ' . mysql_error($typeconn));

}
//echo 'Connected successfully';
mysqli_select_db($typeconn,$dbname); //or die('Could not select database');


$ids = array();
$descs = array();

// Performing SQL query
$typequery = 'SELECT * FROM ReceiverTypes';
if(!$typequery)die('Could not query: ' . mysql_error($typeconn));
$typeList = mysqli_query($typeconn,$typequery); //or die('Query failed: ' . mysql_error());

while ($options = mysqli_fetch_array($typeList))
{
  $ids[] =  $options['ID'];
  $descs[] = $options['Description'];
}

?>
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
