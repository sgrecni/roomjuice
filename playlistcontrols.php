<?php
require_once('_shared.php');

if(!$access['root']) {
	echo 'access denied';
	exit();
}

umask(0133);

// user entered vars 
$load = '';      if(isset($_REQUEST['load'])) $load = stripslashes($_REQUEST['load']);
$delete = '';    if(isset($_REQUEST['delete'])) $delete = stripslashes($_REQUEST['delete']);
$overwrite = ''; if(isset($_REQUEST['overwrite'])) $overwrite = stripslashes($_REQUEST['overwrite']);
$filename = '';  if(isset($_REQUEST['filename'])) $filename = stripslashes($_REQUEST['filename']);
$out = '';       if(isset($_REQUEST['out'])) $out = stripslashes($_REQUEST['out']);
$err = '';
$stop = false;

//see if we really want to overwrite the playlist
if($filename) {
    // add .m3u file extention if it doesn't exist
    if($filename && substr($filename, -4) != '.m3u') {
        $filename .= '.m3u';
    }

	//save a new playlist. check for overwriting.
	if(file_exists($WEBDIR . $PLAYLISTDIR . $filename) && !$overwrite) {
		$out .= "filename already exists. overwrite?<br>";
		$out .= "[<a href=playlistcontrols.php?overwrite=yes&filename=$filename>yes</a>] ";
		$out .= "[<a href=playlistcontrols.php>no</a>]";
		$stop = true;
	} else {
		$overwrite='yes';
	}
	if($overwrite=='yes') {
		if(copy($PLAYLIST, $WEBDIR . $PLAYLISTDIR . $filename)) {
			$err = "playlist saved";
		} else {
			$err = "error saving playlist";
		}
	}
} else if($load) { //load new playlist
	include_once('_control.php');
	stop();

	if(@copy($WEBDIR . $PLAYLISTDIR . $load, $PLAYLIST)) {
		play();
		header('Location: '. $WEBURL .'playlist.php');
		exit();
	} else {
		$err = 'error loading playlist.';
	}
} else if($delete && $overwrite) {
	if($overwrite == 'yes') {
		if(unlink($WEBDIR . $PLAYLISTDIR . $delete)) {
			$err = 'playlist deleted';
		} else {
			$err = 'error deleting file';
		}
	}
} else if($delete) {
	$out .= "really delete?<br>";
	$out .= '[<a href="playlistcontrols.php?overwrite=yes&delete='.
	rawurlencode($delete) .'">yes</a>] ';
	$out .= "[<a href=playlistcontrols.php>no</a>]";
	$stop = true;
}

if($err) {
	header('Location: '. $WEBURL .'playlistcontrols.php?out='. rawurlencode($err));
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

<?php if(isHandheld()) { ?>
[<a href="handheld.php">back to playlist</a>]
<?php } else { ?>
[<a href="playlist.php">back to playlist</a>]
<?php } ?>
<form method="get" action="playlistcontrols.php">
<input type="text" name="filename" value="" size=14 maxlength=64>
<input type="submit" name="save" value="save playlist">
</form>
<p>

<?php
if($out) echo $out .'<p>';

if($stop) {
	include_once('defaultfooter.php');
	exit();
}

//list available playlists
echo "saved playlists:<br>\n";
if(!($dir = @opendir ($WEBDIR . $PLAYLISTDIR))) {
	barf('failed opening directory<br>');
	
}

while($file = readdir ($dir)) {
	$name = substr($file, 0, -4);
	$ext = substr($file, -4);
	//don't show playlist.m3u, but show all other m3u files
	if($ext == '.m3u' && $name != 'playlist') {
		$foundone = 1;
		echo '<a href="playlistcontrols.php?load='. rawurlencode($file) .'">'.
		htmlspecialchars($name) .'</a> '.
		'[<a href=playlistcontrols.php?delete='. rawurlencode($file) .">X</a>]<br>\n";
	}
}

if(!$foundone) {
	echo '<li>no playlists found';
}

closedir($dir);

include_once('defaultfooter.php');
?>
