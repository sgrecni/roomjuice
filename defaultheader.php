<html>
<head>
<meta name="robots" content="noindex,nofollow,noarchive">
<title>Room Juice</title>
<script language=JavaScript src="util.js"></script>
</head>
<link rel="Stylesheet" href="theme/<?php echo $GLOBALS['THEME']; ?>/main.css" type="text/css">
<body>

<table cellspacing=0 cellpadding=5 border=0>
<tr>
    <td>
<?php
if(!isset($dir)) $dir = '';
if(!isset($files)) $files = array();
if(!isset($words)) $words = '';

if(!isset($hideheader)) {
	echo "[<a href=\"prefs.php\" title=\"Room Juice Preferences\">prefs</a>]\n";

	if($dir || $words || isset($showback)) echo "-\n";

    if(count($files) > 50) {
    }
	if($words) {
		if($access['root']) {
			echo '[<a target=rjplaylist href="playlistmod.php?appenddir=1'. 
			'&words='. rawurlencode($words).
			'" title="Enqueues the current search results and all '.
			'subdirectories"';
            if(count($files) > 50) {
				echo ' onClick="return confirm(\'Are you absolutely '.
				'sure you want to do this? This will add ALL the search results to the '.
				'playlist and can take quite a long time.\');"';
			}
			echo ">enqueue ALL results</a>]\n";
		}
	
		echo '[<a target=rjplaylist href="playlistmod.php?appenddir=1'.
		'&words='. rawurlencode($words). '&songs=5" title="Enqueues 5 random songs from the '.
		"search results\">5 random songs</a>]\n";
	} else if($dir) {
		if($access['root']) {
			echo '[<a target=rjplaylist href="playlistmod.php?appenddir='. rawurlencode($dir) .
			'" title="Enqueues the current directory and all '.
			'subdirectories"';
			if(count($dirs) > 1 || count($files) > 50) {
				echo ' onClick="return confirm(\'Are you absolutely '.
				'sure you want to do this? This will add the current directory and all '.
				'subdirectories to the playlist, and and can take quite a long time.\');"';
			}
			echo ">enqueue directory</a>]\n";
		}
	
		echo '[<a target=rjplaylist href="playlistmod.php?appenddir='. rawurlencode($dir).
		'&songs=5" title="Enqueues 5 random songs from the '.
		"current directory and all subdirectories\">5 random songs</a>]\n";
	}


	if(isset($showback) && $showback==1) {
		echo "[<a href=\"javascript:history.go(-1);\" title=\"Back to the Previous Page\">back</a>]\n";
	}

	function dir_autolink($input) {
		$masked = preg_replace($GLOBALS['DIRMASK'], '', $input);
		$mask = preg_replace('|'. addcslashes($masked, '[]()') .'|', '', $input);

		$a = explode('/', $masked);
        $tr = '';
		while(list(,$t) = each($a)) {
			if($t == '') continue;
			$mask .= $t .'/';
			$tr .= '<a href="browse.php?dir='. rawurlencode($mask) .'">'.
			htmlspecialchars($t) ."</a>/";
		}

		return '<a href="browse.php">root</a>/'. $tr;
	}

	echo "<br>\n<b><span class=directoryname>". dir_autolink($dir). "</span></b><br>\n";
	echo '<div style="border-top: solid thin grey"></div><div style="height: 5px"></div>';
}
?>

<!-- END DEFAULT HEADER -->



