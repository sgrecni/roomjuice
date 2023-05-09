<?php
require_once('_shared.php');

function dirmask($input) {
	return htmlspecialchars(preg_replace($GLOBALS['DIRMASK'], '', $input));
}


// an external link, direct them to start over so they get all the frames
if($_SERVER['HTTP_REFERER'] 
&& !preg_match('/^http[s]{0,1}:\/\/'. $_SERVER['HTTP_HOST'] .'/', $_SERVER['HTTP_REFERER'])) {
	echo 'Invalid Referer: '. $_SERVER['HTTP_REFERER'] ."<p>\n";
	echo 'If you followed an external link, you will want the full framed version '.
	'of this app which can be found <a target="_top" href="index.php">here</a>'; 
	exit();
}

// user entered vars
$dir = '';
$words = '';
if(isset($_REQUEST['dir'])) $dir = stripslashes($_REQUEST['dir']);
if(isset($_REQUEST['words'])) $words = stripslashes($_REQUEST['words']);
$words = strip_tags($words);
$dir = strip_tags($dir);

$confdircount = count(explode(';', $GLOBALS['MP3DIRS']));
if($confdircount > 1) $virtualroot = 1;

if($words) {
	// search calling the locate command
	include_once('_search.php');
	$search = song_search($words);
} else {
	// walk through the directory, $dir,  listing dir/files
	include_once('_browse.php');
	directory_browse($dir);
}

include_once('defaultheader.php');

if(isset($search)) echo $search;


reset($dirs);
reset($files);
natcasesort($dirs);
natcasesort($files);

/* Display first image in directory, don't show if it's search results */
if($DISP_IMAGE==1 && !$words) {
    foreach($files as $t) {
		if(preg_match($GFXTYPES, $t)) {
			list($w,$h) = getimagesize($dir . $t);
			
			/* resize image */
			$max_w = 600;
			$max_h = 300;
			$resized = false;
			if($w > $max_w) {
				$h=$h*$max_w/$w;
				$w=$max_w;
				$resized = true;
				if($h>$max_h) {
					$w=$w*$max_h/$h;
					$h=$max_h;
				}
			} else if($h > $max_h) {
				$resized = true;
				$w=$w*$max_h/$h;
				$h=$max_h;
			}
			$w=round($w);
			$h=round($h);

			if($resized) {
				echo "<a href=\"javascript:display('". addslashes($dir . $t) ."')\">";
			}
			echo "<img width=$w height=$h src=\"display.php?filename=". rawurlencode($dir . $t) ."\" border=0>";
			if($resized) {
				echo "</a>";
			}
			echo "\n";
			break;
		}
	}
}
reset($files);

echo "<table cellspacing=0 cellpadding=1 border=0>\n";

/*
 *  list all directories 
 */
foreach($dirs as $t) {
	if($dir=='') {
		$link_target = rawurlencode($t);
	} else {
		if($t=='..') { // previous dir
			if($dir == $GLOBALS['MP3DIRS']) {
				continue;
			} else { 
				$link_target = rawurlencode(substr($dir,0, (strrpos(substr($dir,0,-1),"/") + 1)));
			}
		} else {
			$link_target = rawurlencode($dir . $t .'/');
			if(substr($t, -1) != '/') $t .= '/';
		}
	}

	echo "<tr>\n\t<td valign=bottom><a href=\"browse.php?dir=$link_target\">".
		"<img src=\"theme/". $GLOBALS['THEME'] ."/dir.png\" alt=\"+\" border=\"0\">".
		dirmask($t) ."</a></td>\n</tr>\n";
}


if(isset($ICECASTSTATSLOG) && $dir=='' && !$words) {
	echo "<p>\n";
	if($fp = fopen($ICECASTSTATSLOG, 'r')) {
		while(!feof($fp)) {
			$buff = fgets($fp, 1024);
			if(preg_match('/^Client [0-9]+(.+)\. Type: listener/', $buff, $regs)) {
				$buff = $regs[1];
				if(preg_match('/([0-9]+) bytes/', $buff,  $regs)) {
					$buff = str_replace($regs[0], humanreadable($regs[1]), $buff);
				}
				echo $buff ."<br>\n";
			}
		}
		fclose($fp);
	} else {
		echo 'Couldn\'t open: '. $ICECASTSTATSLOG .'!  If you don\'t use icecast '.
		'then comment out $ICECASTSTATSLOG in _config.php to quell this error message. '.
		'If you do use icecast and want this page to show what ip addresses are '.
		'connected then check to make sure the web server has permission '.
		'to read this file.';
	}
}


/*
 * list all files
 */
foreach($files as $t) {
	$s = stat($dir. $t);

	if(preg_match($FILETYPES, $t)) {
		$link_target = rawurlencode($dir . $t);
		echo "<tr>\n\t<td valign=bottom nowrap><a title=\"Add this song to the queue\" ".
            "target=\"rjplaylist\" href=\"playlistmod.php?append=". rawurlencode($dir . $t) .
			"\"><img src=\"theme/". $GLOBALS['THEME'] ."/binary.png\" alt=\"-\" border=\"0\">".
			dirmask($t) ."</a></td>\n";
		echo "\t<td valign=bottom>". humanreadable($s[7]) ."</td>\n";
		echo "\t<td valign=bottom>&nbsp;<a title=\"Display details about this song\" href=\"display.php?filename=". 
			rawurlencode($dir . $t) ."\">info";
	} else {
		if(preg_match($GFXTYPES, $t)) {
			$icon = 'image2.png';
		} else {
			$icon = 'text.png';
		}

		echo "<tr>\n\t<td><a title=\"Display this file\" ".
		"href=\"javascript:display('". addslashes($dir . $t) .
		"')\"><img src=\"theme/". $GLOBALS['THEME'] ."/$icon\" border=\"0\" alt=\"view\">".
		dirmask($t) ."</a>";

		echo "\t<td valign=bottom>". humanreadable($s[7]) ."</td>\n";
	}
	if(!is_readable($dir . $t)) {
		echo ' <span class=bad>no read permission!</span>';
	}
	echo "</td>\n<tr>\n";
}

echo "</TABLE>\n";

include_once('defaultfooter.php');
?>
