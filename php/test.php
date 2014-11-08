<?php

/**********************************************************
 *                                                        *
 * Test the transmitte hardware with a hard coded command *
 *                                                        *
 **********************************************************/

function sendCommand($id,$onoff,$dim)
{

  exec("/bin/stty -F /dev/ttyACM0 9600");

  $fp =fopen("/dev/ttyACM0", "w+");
  if( !$fp)
  {
    echo "Error Connecting!";
    print_r(error_get_last());  
    die();
  }

  $time = time();

  stream_set_timeout($fp, 2);
  fread($fp, 100);

  fwrite($fp,'#');
  fwrite($fp,$id);
  fwrite($fp,$onoff);
  fwrite($fp,$dim);
  sleep(1);
  fclose($fp);
}

?>

<?php
sendCommand('111235190237183123','1','10');
?>
<H3>Done</H3>

