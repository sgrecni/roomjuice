<?php
require_once('_shared.php');
require_once('_prefs.php');
require_once('_control.php');
require_once('_playlist.php');

// user entered vars
if(isset($_REQUEST['action'])) $action = $_REQUEST['action'];
if(isset($_REQUEST['reload'])) $reload = $_REQUEST['reload'];

if(isset($action)) {
	if($action=='veto' && $access['control']) {
		veto();
	} else if ($action=='play' && $access['control']) {
		play();
	} else if($action=='stop' && $access['root']) {
		stop();
	} else if($action=='pause' && $access['root']) {
		pause();
	} else if ($action=='randomize' && $access['root']) {
		randomize();
	} else if ($action=='clear' && $access['root']) {
		clear();
	}

	if($action) {
		// header redirect, so if the user hits refresh, the action won't be processed again
		@header('Location: '. $WEBURL .'buttons.php?reload='. $action);
		exit();
	}
}

?>
<html>
<head>
<meta name="robots" content="noindex,nofollow,noarchive">
<title>Room Juice</title>
</head>
<link rel="Stylesheet" href="theme/<?php echo $GLOBALS['THEME']; ?>/main.css" type="text/css">
<body>

<table cellspacing=0 cellpadding=3 border=0>
<tr>
    <td>

<table cellpadding=0 cellspacing=0 border=0>
<tr>
	<td nowrap>
<?php
echo '<a target="rjplaylist" href="playlist.php">'.
'<img src="theme/'. $GLOBALS['THEME'] .'/refresh.png" border="0" alt="refresh&nbsp;"></a>';

echo '<a href="buttons.php?action=play">'.
'<img src="theme/'. $GLOBALS['THEME'] .'/play.png" border="0" alt="&gt;&nbsp;"></a>';

if ($access['root']) {
	echo '<a href="buttons.php?action=pause">'.
	'<img src="theme/'. $GLOBALS['THEME'] .'/pause.png" border="0" alt="||&nbsp;"></a>';

	echo '<a href="buttons.php?action=stop">'.
	'<img src="theme/'. $GLOBALS['THEME'] .'/stop.png" border="0" alt="[]&nbsp;"></a>';
} 

echo '<a href="listen.php/listen.pls">'.
'<img src="theme/'. $GLOBALS['THEME'] .'/listen.png" border="0" alt="listen&nbsp;"></a> ';

echo '<a href="buttons.php?action=veto">'.
'<img src="theme/'. $GLOBALS['THEME'] .'/veto.png" border="0" alt="veto&nbsp;"></a>';

if($access['root']) {
	echo '<a href="buttons.php?action=randomize">'.
	'<img src="theme/'. $GLOBALS['THEME'] .'/rand.png" border="0" alt="rand&nbsp;"></a>';

	echo '<a href="buttons.php?action=clear">'.
	'<img src="theme/'. $GLOBALS['THEME'] .'/clear.png" border="0" alt="clear"></a>';
}
?>
	</td>
</tr>
<tr>
	<td>
	<form target="rjbrowse" method="get" action="browse.php">
	<input type="text" name="words" value="" size=16 maxlength=64><input type="submit" name="SEARCH" value="search">
	</form>
	</td>
</tr>
</table>

    </td>
</tr>
</table>

<?php
if(isset($reload)) {
	// reload the playlist frame if need be, waiting a bit so stuff can
	// get written to disk properly
	if($reload=='veto') { $timeout=4000; } else { $timeout=500; }

	echo "<script language=\"JavaScript\"><!--\n";
	echo "function mytimeout() {\n";
	echo "	top.rjplaylist.location = '". $WEBURL ."playlist.php';\n";
	echo "}\n";
	echo "setTimeout(\"mytimeout()\",$timeout);\n";
	echo "//--></script>\n";
}

?>

</body>
</html>
