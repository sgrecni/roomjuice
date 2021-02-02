<?php
// these do jack shit for PalmOS
//header('Pragma: no-cache');
//header('Expires: 0');
setcookie('THEME', 'handheld', time() + 86400*90);

require_once('_shared.php');
require_once('_control.php');


// user entered vars
$action = $_REQUEST['action'];
$reload = $_REQUEST['reload'];

if($action) {
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
		@header('Location: '. $WEBURL .'handheld.php?reload='. $action);
		exit();
	}
}

//if(!$_REQUEST['time']) {
//    header('Location: ' $WEBURL .'handheld.php?time='. time());
//    exit();
//}
?>

<html>
<head>
<meta name="robots" content="noindex,nofollow,noarchive">
<title>Room Juice</title>
<script language=JavaScript src="util.js"></script>
</head>
<link rel="Stylesheet" href="theme/<?php echo $GLOBALS['THEME']; ?>/main.css" type="text/css">
<body>

<?php
echo '<a href="handheld.php?time='. time() .'">'.
'<img src="theme/'. $GLOBALS['THEME'] .'/refresh.gif" width="23" height="18" border="0" alt="refresh&nbsp;"></a>  ';

echo '<a href="handheld.php?action=play&time='. time() .'">'.
'<img src="theme/'. $GLOBALS['THEME'] .'/play.gif" width="23" height="18" border="0" alt="&gt;&nbsp;"></a> ';

if ($access['root']) {
	echo '<a href="handheld.php?action=pause&time='. time() .'">'.
	'<img src="theme/'. $GLOBALS['THEME'] .'/pause.gif" width="23" height="18" "border="0" alt="||&nbsp;"></a> ';

	echo '<a href="handheld.php?action=stop&time='. time().'">'.
	'<img src="theme/'. $GLOBALS['THEME'] .'/stop.gif" width="23" height="18" border="0" alt="[]&nbsp;"></a>&nbsp; ';
} 

//echo '<a href="listen.php/listen.pls">'.
//'<img src="theme/'. $GLOBALS['THEME'] .'/listen.gif" width="37" height="18" border="0" alt="listen&nbsp;"></a> ';

echo '<a href="handheld.php?action=veto&time='. time() .'">'.
'<img src="theme/'. $GLOBALS['THEME'] .'/veto.gif" width="31" height="18" border="0" alt="veto&nbsp;"></a> ';

if($access['root']) {
	echo '<a href="handheld.php?action=randomize&time='. time() .'">'.
	'<img src="theme/'. $GLOBALS['THEME'] .'/rand.gif" width="33" height="18" border="0" alt="rand&nbsp;"></a> ';

	echo '<a href="handheld.php?action=clear&time='. time() .'">'.
	'<img src="theme/'. $GLOBALS['THEME'] .'/clear.gif" width="37" height="18" border="0" alt="clear"></a> ';
}

include_once('handheld_playlist.php');
?>

</body>
</html>
