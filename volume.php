<?php
require_once('_shared.php');
if(!$access['root']) {
	echo "access denied";
	exit();
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

<?php
if(isset($_GET['vol'])) $vol = intval($_GET['vol']);

/* how much does the volume change for each step in the volume bar? */
$increment = 7; 

if (strstr($VOLUME, 'aumix')) {
    if (isset($vol)) {
        switch($VOLUME_SLIDER_TITLE) {
            case 'pcm': $switch = '-w'; break;
            case 'pcm2': $switch = '-W'; break;
            case 'vol': $switch = '-v'; break;
            default: $switch = '-w';
        }
        exec($VOLUME .' '. $switch .' '. escapeshellarg($vol));
    }
    // find out the current volume, and parse it
    $output = shell_exec($VOLUME ." -q 2>&1 |grep -i '$VOLUME_SLIDER_TITLE '");
    if(!$output) barf("can't find volume, is aumix installed?<br> aumix returned: $output");
    list(,$vol) = explode(" ", str_replace(",", "", $output));
} else if (strstr($VOLUME, 'smixer')) {
    if (isset($vol)) {
        shell_exec("echo vol $VOLUME_SLIDER_TITLE ". escapeshellarg($vol) .' | '. $VOLUME .' -a');
    }
    // find out the current volume, and parse it
    $output = trim(shell_exec($VOLUME ." -p 2>&1 |grep -i '$VOLUME_SLIDER_TITLE ' "));
    if(!$output) barf("can't find volume, is smixer installed?<br> smixer returned:  $output");
    $vol = trim(substr($output, -4, 3));
} else if (strstr($VOLUME, 'amixer')) {
    if (isset($vol)) {
        //echo ($VOLUME ." set $VOLUME_SLIDER_TITLE ". escapeshellarg($vol) .'%');
        shell_exec($VOLUME ." set $VOLUME_SLIDER_TITLE ". escapeshellarg($vol) .'%');
    }
    // find out the current volume, and parse it
    $output = trim(shell_exec($VOLUME ." get $VOLUME_SLIDER_TITLE | grep 'Front Right: Playback' | awk '{print $5}'"));
    //echo($VOLUME ." get $VOLUME_SLIDER_TITLE | grep 'Front Right: Playback' | awk '{print $5}'");
    if(!$output) barf("can't find volume, is amixer installed?<br> amixer returned:  $output");
    $vol = trim($output);
    $vol = str_replace('[', '', $vol);
    $vol = str_replace(']', '', $vol);
    $vol = str_replace('%', '', $vol);
} else {
    barf('Invalid mixer, check _config.php');
}

if($vol < 0 || $vol > 100) {
    barf('I got a bizarre response back from the mixer which was not what I '.
         'was expecting:'. $output);
}

echo '<nobr><a href="volume.php?vol='. ($vol-$increment) .'"><img src="theme/'. $GLOBALS['THEME'] .
    '/minus.png" border="0" alt="-"></a>'. "\n";

/* Get the image size of the slider bar */
$sizebox = getimagesize("theme/". $GLOBALS['THEME'] ."/box.png");
	
echo '<a href="volume.php?vol=0"><img src="theme/'. $GLOBALS['THEME'] .
    '/left.png" border="0" alt="("></a>';
for ($i=0; $i < 101; $i=$i+$increment) {
    if ($vol<$i || $vol >= $i+$increment) {
        echo '<a href="volume.php?vol='. $i .'"><img width=10 height='. $sizebox[1] .' src="theme/'.
        $GLOBALS['THEME'] .'/box.png" border=0 alt="-"></a>';
    } else {
        echo '<a href="volume.php?vol='. $i .'"><img src="theme/'. $GLOBALS['THEME'] .
        '/box2.png" border="0" alt="X"></a>';
    }
}

echo '<a href="volume.php?vol=100"><img src="theme/'. $GLOBALS['THEME'] .
	'/right.png" border=0 alt=")"></a> ';
echo '<a href="volume.php?vol='. ($vol+$increment) .'"><img src="theme/'. $GLOBALS['THEME'] .
	'/plus.png" border=0 alt="+"></a> ';
if ($SHOW_VOL_TEXT) echo $vol;

?></nobr>

    </td>
</tr>
</table>

</body>
</html>
