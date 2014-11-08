<html>

<head>

<title>Install Complete</title>

<link href="/ha/css/wizard.css" rel="stylesheet">
<link rel="stylesheet" href="/ha/jquery/css/redmond/jquery-ui-1.10.4.custom.min.css">
<link rel="stylesheet" href="/ha/jquery/jquery.pnotify.default.css">
<link rel="stylesheet" href="/ha/jquery/jquery.dropdown.css">

<script src="../jquery/js/jquery-1.10.2.js"></script>
<script src="../jquery/js/jquery-ui-1.10.4.custom.js"></script>
<script src="../jquery/jquery.dropdown.min.js"></script>

<script>

function previous()
{
	document.location.replace('/ha/setup/');
}

function next()
{
        //generateNewTransmitter();
        document.location.replace('/ha/wizard/');
}

</script>
</head>
<body>
<form method="post" action="step2.php">
<div id="header">
Setup: Complete
</div>

<div id="body">
 <div id="instructions">
<b>Installation Complete.</b>  <br>Press 'Finish' to proceed to the 'Add device wizard'
</div>
</div>
<div id="buttons">
<input class="nextb" type="button" value="Start Over" onclick="previous();">
<input class="nextb" type="button" value="Finish" onclick="next();">
</div>
</form>
</body>
</html>

