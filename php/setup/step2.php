<?php

function customError($errno, $errstr)
{
  //echo "<b>Error:</b> [$errno] $errstr<br>";
  //echo "Ending Script";
  header( 'Location: index.php?errno='.$errno.'&errmsg='.urlencode($errstr )) ;
  die();
} 

function doQuery($conn,$sql,$dbname,$desc)
{
  mysqli_select_db($conn,$dbname);
  if(mysqli_query($conn,$sql))
  {
        //Created
  }
  else
  {
        trigger_error("Error ".$desc.": ".mysqli_error($conn),E_USER_ERROR);
  }
}

set_error_handler("customError");


/* -------------------------------------------------------------------------------
 * Create the config file
 * ------------------------------------------------------------------------------- */
$dbserv = $_POST["dbserv"];
$dbname = $_POST["dbname"];
$dbuser = $_POST["dbuser"];
$dbpass = $_POST["dbpass"];

$serialcom  = $_POST["serialcom"];
$serialbaud = $_POST["serialbaud"];

if (!isset($_POST["drop"]))
{
	$ckdrop='off';
}
else
{
	$ckdrop='on';
}
if (isset($dbserv)) 
{

$string = '<?php 
//Config Settings
//------------------------------------------------
$dbserv = "'. $dbserv. '";
$dbname = "'. $dbname. '";
$dbuser = "'. $dbuser. '";
$dbpass = "'. $dbpass. '";
$serialcom = "'. $serialcom. '";
$serialbaud= "'. $serialbaud. '";
//------------------------------------------------
?>';

  $fp = fopen("../config.php", "w");
  fwrite($fp, $string);
  fclose($fp);

}

/* ----------------------------------------------------------------------------
 * Check the serial port
 * ---------------------------------------------------------------------------- */

include "../php_serial.class.php";
$serial = new phpSerial;
$serial->deviceSet($serialcom);
$serial->confBaudRate($serialbaud);
$serial->confParity("none");
$serial->confCharacterLength(8);
$serial->confStopBits(1);
$serial->deviceOpen();
$startTime=time();
$timeout=2;
while(1==1)
{
  $read = $serial->readPort();
  if(time() > $startTime + $timeout)
  {
     break;
  }
}



/* ----------------------------------------------------------------------------
 * Now check the database settings
 * ---------------------------------------------------------------------------- */

include "../config.php";
//echo '<BR>Loaded config';

$conn = mysqli_connect($dbserv, $dbuser, $dbpass);

if(!$conn)
{
  die('Could not connect: ' . mysql_error($conn));
  trigger_error(mysql_error($conn),E_USER_ERROR);
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
        trigger_error("Error Querying Datbase: ".mysqli_error($conn),E_USER_ERROR);
}


while ($options = mysqli_fetch_array($typeList))
{
  $reccount =  $options['reccount'];
}
$total = $reccount + 0;

if($total>0)
{
	if($ckdrop!='on')
	{
	  trigger_error("Database Already exists.  Please change the database name, or select the replace option",E_USER_ERROR);
        }
	else
	{
	  //trigger_error("overwite on",E_USER_ERROR);
	  //DROP THE EXISTING DATABASE
	  $sql='DROP Database '.$dbname;
	  doQuery($conn,$sql,$dbname,"Dropping Database");
	}
}

//---------------------------------------------------------------------------
//Create the db ...
//---------------------------------------------------------------------------

$sql='CREATE DATABASE '.$dbname;
mysqli_select_db($conn,$dbname);

if(mysqli_query($conn,$sql))
{
	//Created
}
else
{
	trigger_error("Error Creating Datbase: ".mysqli_error($conn),E_USER_ERROR);
}

//---------------------------------------------------------------------------
//Create the tables
//---------------------------------------------------------------------------

$receiverTypes="
CREATE TABLE IF NOT EXISTS `ReceiverTypes` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Description` varchar(100) CHARACTER SET latin1 NOT NULL,
  `count` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=ascii AUTO_INCREMENT=3 ;
";

$receiverTypesData="
--
-- Dumping data for table `ReceiverTypes`
--

INSERT INTO `ReceiverTypes` (`ID`, `Description`, `count`) VALUES
(1, 'Socket', 1),
(2, 'Dimmer', 1),
(3, 'Relay' , 1);
";

$pairing="
CREATE TABLE IF NOT EXISTS `pairing` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `TransmitterID` int(11) NOT NULL,
  `ReceiverID` int(11) NOT NULL,
  `Notes` varchar(160) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `ReceiverID` (`ReceiverID`),
  KEY `TransmitterID` (`TransmitterID`)
) ENGINE=InnoDB  DEFAULT CHARSET=ascii AUTO_INCREMENT=6 ;

";

$receiver="
CREATE TABLE IF NOT EXISTS `receiver` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Description` varchar(150) CHARACTER SET latin1 NOT NULL,
  `TypeID` int(11) NOT NULL,
  `RoomID` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `RoomID` (`RoomID`),
  KEY `TypeID` (`TypeID`)
) ENGINE=InnoDB  DEFAULT CHARSET=ascii AUTO_INCREMENT=6 ;
";

$room="
CREATE TABLE IF NOT EXISTS `room` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Description` varchar(150) CHARACTER SET latin1 NOT NULL,
  `Order` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `Order` (`Order`)
) ENGINE=InnoDB  DEFAULT CHARSET=ascii AUTO_INCREMENT=1 ;
";

$transmitter="
CREATE TABLE IF NOT EXISTS `transmitter` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `TransmitterID` varchar(18) CHARACTER SET latin1 NOT NULL,
  `Notes` varchar(255) NOT NULL,
  `Enabled` tinyint(1) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `TransmitterID` (`TransmitterID`),
  KEY `Enabled` (`Enabled`)
) ENGINE=InnoDB  DEFAULT CHARSET=ascii AUTO_INCREMENT=5 ;
";

$schedule="
CREATE TABLE IF NOT EXISTS `schedule` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Hour` int(11) NOT NULL,
  `Minute` int(11) NOT NULL,
  `dow` varchar(20) NOT NULL,
  `pairId` int(11) NOT NULL,
  `transmitter` varchar(18) NOT NULL,
  `command` varchar(3) NOT NULL,
  `Notes` varchar(500) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`ID`),
  KEY `pairId` (`pairId`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=13 ;
";

$constraints1="
--
-- Constraints for table `pairing`
--
ALTER TABLE `pairing`
  ADD CONSTRAINT `pairing_ibfk_1` FOREIGN KEY (`TransmitterID`) REFERENCES `transmitter` (`ID`),
  ADD CONSTRAINT `pairing_ibfk_2` FOREIGN KEY (`ReceiverID`) REFERENCES `receiver` (`ID`);
";

$constraints2="
--
-- Constraints for table `receiver`
--
ALTER TABLE `receiver`
  ADD CONSTRAINT `receiver_ibfk_1` FOREIGN KEY (`TypeID`) REFERENCES `ReceiverTypes` (`ID`) ON DELETE CASCADE
";

$constraints3="
ALTER TABLE `receiver`
  ADD CONSTRAINT `receiver_ibfk_2` FOREIGN KEY (`RoomID`) REFERENCES `room` (`ID`) ON DELETE CASCADE;
";


doQuery($conn,$receiverTypes,     $dbname, "Creating Receiver Types Table");
doQuery($conn,$receiverTypesData, $dbname, "Inserting Receiver Types Data");
doQuery($conn,$pairing,           $dbname, "Creating Pairings Table");
doQuery($conn,$receiver,          $dbname, "Creating Receiver Table");
doQuery($conn,$room,              $dbname, "Creating Room Table");
doQuery($conn,$transmitter,       $dbname, "Creating Transmitter Table");
doQuery($conn,$schedule,          $dbname, "Creating Transmitter Table");
doQuery($conn,$constraints1,      $dbname, "Adding Pairing Constraints");
doQuery($conn,$constraints2,      $dbname, "Adding Receiver Constraints 1");
doQuery($conn,$constraints3,      $dbname, "Adding Receiver Constraints 2");

header( 'Location: success.php') ;


?>


