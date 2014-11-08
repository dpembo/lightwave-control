<?php

function customError($errno, $errstr)
{
  echo "<b>Error:</b> [$errno] $errstr<br>";
  echo "Ending Script";
  header( 'Location: index.php?errno='.$errno.'&errmsg='.urlencode($errstr )) ;
  die();
} 

set_error_handler("customError");

$dbserv = $_POST["dbserv"];
$dbname = $_POST["dbname"];
$dbuser = $_POST["dbuser"];
$dbpass = $_POST["dbpass"];

$serialcom  = $_POST["serialcom"];
$serialbaud = $_POST["serialbaud"];

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

  try
  {  
    $fp = fopen("config.php", "w");
    fwrite($fp, $string);
    fclose($fp);
    header( 'Location: step3.php' ) ;

  }
  catch (Exception $e)
  {
    echo 'Error:'.$e->getMessage();
  }

}

?>
