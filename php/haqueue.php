<?php echo '<'.'?xml version="1.0"?'.'>';?>
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

function sendCommand($id,$onoff,$dim,$com,$baud)
{
$serial = new phpSerial;
$serial->deviceSet($com);
$serial->confBaudRate($baud);
$serial->confParity("none");
$serial->confCharacterLength(8);
$serial->confStopBits(1);
$serial->deviceOpen();
$startTime=time();
$timeout=3;
while(1==1)
{
  $read = $serial->readPort();
  if(time() > $startTime + $timeout)
  {
     break;
  }
}

$serial->sendMessage("+"); //111235190237183123110");
$serial->sendMessage($onoff);
if (isset($_GET['room']))    $room    = $_GET['room'];
$serial->sendMessage(urldecode($room));
$serial->sendMessage(";");
$serial->sendMessage($id);
$serial->sendMessage("*");
$serial->deviceClose();
sleep(1);
}

if (isset($_GET['id']))      $id      = $_GET['id'];
if (isset($_GET['onoff']))   $onoff   = $_GET['onoff'];
if (isset($_GET['dim']))     $dim     = $_GET['dim'];
if (isset($_GET['message'])) $message = $_GET['message'];
if (isset($_GET['room']))    $room    = $_GET['room'];

if (strlen($id)<=0)
{
?>

<?php
}
else
{
        sendCommand($id,$onoff,$dim,$serialcom,$serialbaud);
}

header('Content-type: application/xml');

?>
<transmission>
<status>Complete</status>
<device>
  <id><?php echo $id;?></id>
  <room><?php echo $room;?></room>
  <desc><?php echo $message;?></desc>
  <onoff><?php echo $onoff;?></onoff>
  <dim><?php echo $dim;?></dim>
</device>
</transmission>
