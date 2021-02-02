<?php
set_time_limit(300);
require_once('_shared.php');
require_once('_playlist.php');

// an external link, direct them to start over so they get all the frames
if($_SERVER['HTTP_REFERER'] 
&& !preg_match('/^http[s]{0,1}:\/\/'. $_SERVER['HTTP_HOST'] .'/', $_SERVER['HTTP_REFERER'])) {
	echo 'Invalid Referer: '. $_SERVER['HTTP_REFERER'] ."<p>\n";
	echo 'If you followed an external link, you will want the full framed version '.
	'of this app which can be found <a target="_top" href="index.php">here</a>'; 
	exit();
}

// user entered vars
$append = strip_tags(stripslashes($_REQUEST['append'] ?? null));
$appenddir = strip_tags(stripslashes($_REQUEST['appenddir'] ?? null));
$words = strip_tags(stripslashes($_REQUEST['words'] ?? null));
$remove = strip_tags(stripslashes($_REQUEST['remove'] ?? null));
$songs = strip_tags(stripslashes($_REQUEST['songs'] ?? null));
$playlist = strip_tags(stripslashes($_REQUEST['playlist'] ?? null));
$first = strip_tags(stripslashes($_REQUEST['first'] ?? null));
$up = strip_tags(stripslashes($_REQUEST['up'] ?? null));
$down = strip_tags(stripslashes($_REQUEST['down'] ?? null));
$last = strip_tags(stripslashes($_REQUEST['last'] ?? null));

if($append && $playlist && $access['root']) {
	if($t = notvalidfd($append)) {
		barf($t);
	}
	$pl = new playlist($playlist .'.m3u');
	$pl->read();
	$pl->add($append);
	$pl->write();
} else if($append && !$playlist && $access['add']) {
	if($t = notvalidfd($append)) {
		barf($t);
	}

	/* is a valid filetype and it exists, then add it to the playlist */
	$pl = new playlist();
	$pl->read();
	$pl->add($append);
	$pl->write();
} else if ($appenddir && $access['add'] && preg_match('/^[0-9]+$/', $songs)) {
	/* add $songs random songs from $appenddir and above */
	$pl = new playlist();
	$pl->read();

	include_once('_search.php');
	if($words) {
		song_search($words);
	} else {
		if($t = notvalidfd($appenddir)) barf($t);
		song_dir($appenddir);
	}
	if(count($files) <= 1) {
		barf($err);
	}

	/* only pick $songs songs from $output
     * keep track of how many songs left so we don't loop forever :) */
	mt_srand((double)microtime()*1000000);
	$size = count($files);
	$left = $size;
	for($i = 1; $i <= $songs && $left > 0; ) {
		$random = mt_rand() % $size;
		if($files[$random] != '') {
			if($pl->add($files[$random], 1)) {
				$i++;
			}
			$left--;
			$files[$random] = '';
		}
	}
	$pl->write();
} else if ($appenddir && $access['root']) {
	/* add all the songs in $appenddir or all search results if $words */

	$pl = new playlist();
	$pl->read();
	include_once('_search.php');
	if($words) {
		song_search($words);
		
		// sort the songs
		@reset($files); @natcasesort($files);
	} else {
		song_dir($appenddir);
	}
	
	while(list(,$t) = @each($files)) {
		$pl->add($t);
	}
	$pl->write();
} else if ($remove && $access['remove']) {
	/* no validation required. just remove the requested data.
	 * if the file doesn't exist in the playlist, then no sweat off my sack. */

	$pl = new playlist();
	$pl->read();

	$toremove = array();
	// loop through all checkboxes
	while(list($k, $v) = @each($_POST)) {
		if(preg_match('/^CHECK_/', $k)) {
			$toremove[] = stripslashes($v);
		}
	}
	$pl->remove($toremove);
	$pl->write();
} else if ($first && $access['remove']) {
	/* move song to first pos */

	$pl = new playlist();
	$pl->read();

	$tomove = array();
	// loop through all checkboxes
	while(list($k, $v) = @each($_POST)) {
		if(preg_match('/^CHECK_/', $k)) {
			$tomove[] = stripslashes($v);
		}
	}
	$pl->first($tomove);
	$pl->write();
} else if ($up && $access['remove']) {
	/* move song up */

	$pl = new playlist();
	$pl->read();

	$tomove = array();
	// loop through all checkboxes
	while(list($k, $v) = @each($_POST)) {
		if(preg_match('/^CHECK_/', $k)) {
			$tomove[] = stripslashes($v);
		}
	}
	$pl->up($tomove);
	$pl->write();
} else if ($down && $access['remove']) {
	/* move song down */

	$pl = new playlist();
	$pl->read();

    $tomove = array();
	// loop through all checkboxes
	while(list($k, $v) = @each($_POST)) {
		if(preg_match('/^CHECK_/', $k)) {
			$tomove[] = stripslashes($v);
		}
	}
	$pl->down($tomove);
	$pl->write();
} else if ($last && $access['remove']) {
	/* move song to last pos */

	$pl = new playlist();
	$pl->read();

	$tomove = array();
	// loop through all checkboxes
	while(list($k, $v) = @each($_POST)) {
		if(preg_match('/^CHECK_/', $k)) {
			$tomove[] = stripslashes($v);
		}
	}
	$pl->last($tomove);
	$pl->write();
}

if(isHandheld()) {
    header('Location: '. $WEBURL .'handheld.php');
} else {
    header('Location: '. $WEBURL .'playlist.php');
}
/* this is a hack to make netscape 4.* work, since it seems to be ignoring
 * header redirects after form posts */
//echo '<meta http-equiv="Refresh" content="0; url='. $WEBURL .'playlist.php">';
?>
