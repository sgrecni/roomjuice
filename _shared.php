<?php
unset($HOSTS);
require_once('_config.php');

// required for RedHat (and other dists?)
putenv('HOME=/tmp');

$request_scheme = $_SERVER['REQUEST_SCHEME'];
if(!$request_scheme) $request_scheme = 'https';
$WEBURL = $request_scheme .'://'. $_SERVER['HTTP_HOST'] .$INSTALLDIR;
$PLAYLIST = $PLAYLISTDIR . 'playlist.m3u';
$PLAYTIME = $PLAYLISTDIR . 'playtime';

$WEBDIR = '';
// do everything relative but if that doesn't work (why?), uncomment this
// $WEBDIR = $_SERVER['DOCUMENT_ROOT']. $INSTALLDIR;
if(!isset($_COOKIE['THEME'])) {
	$THEME = 'default';
    $_COOKIE['THEME'] = $THEME;
} else {
	$THEME = $_COOKIE['THEME'];
}

$access = array();
$FILETYPES = '';

reset($MUSIC);
foreach(array_keys($MUSIC) as $key) {
	$FILETYPES .= $MUSIC[$key]->filetypes.'|';
	$MUSIC[$key]->filetypes='/('. $MUSIC[$key]->filetypes .')$/i';
}
$FILETYPES='/('. substr($FILETYPES, 0, -1) .')$/i';

$tok = strtok($HOSTS,";\n\r\t ");
while($tok !== FALSE) {
	$p = strpos($tok,':');
	$wchost = substr($tok, 0, $p);
	$p2 = strpos($wchost, '*');
	if($p2 !== FALSE) { //wildcard entry
        // autocomplete the wildcard host starting at * pos of wildcard host with remote ip
        $wchost = substr($wchost, 0, $p2) . substr($_SERVER['REMOTE_ADDR'], $p2);
	} else {
        $wchost = substr($wchost, 0, $p);
        if (!preg_match('/\d{1,3}(\d{1,3}\.){3}/', $wchost)) {
            $wchost = gethostbyname($wchost);
        }

    }
	// if ip matches
	if($wchost == $_SERVER['REMOTE_ADDR']) {
		$perm = strtok(substr($tok,$p+1),',');
		while($perm !== FALSE) {
			switch($perm) {
				case 'add':
					$access['add']=1; break;
				case 'control':
					$access['control']=1; break;
				case 'remove':
					$access['remove']=1; break;
				case 'view':
					$access['view']=1; break;
				case 'root':
					$access['add']=1; $access['control']=1; $access['remove']=1;
					$access['view']=1; $access['root']=1;
					break;
				case 'deny':
					$access['deny']=1; break;
				default:
			}
			$perm = strtok(',');
		}
		// after this, we're done
		break;
	}
	$tok = strtok(";\n");
}

if((!$access['view'] || isset($access['deny'])) && isset($_SERVER['HTTP_HOST'])) {
	echo 'access denied';
	exit();
}

/*
 * Stops execution and displays error message
 */
function barf($input) {
	$hideheader = 1;
	include_once('defaultheader.php');
	echo "<p><b>$input</b>";
	include_once('defaultfooter.php');
	exit();
}

if(!function_exists('logger')) {
    function logger($input) {
        echo "$input\n";
    }
}

/*
 * Feed it a number of bytes, it gives you back a human readable size
 */
function humanreadable($input) {
	$input /= 1024;
	if ($input < 1024) {
		return(sprintf("%.1f",$input) .'KiB');
	}
	$input /= 1024;
	if ($input < 1024) {
		return(sprintf("%.1f",$input) .'MiB');
	}
	$input /= 1024;
	return(sprintf("%.1f",$input) .'GiB');
}

/*
 *  Checks to see if a file/dir is in one of the configured directories
 *  retuns the correct error message if access is denied, null on access granted
 */
function notvalidfd($input) {
	if(preg_match('/(\/\.\.\/|~)/', $input)) {
		return 'Invalid file/directory: '. $input; 
	}
 	if(!@file_exists($input)) {
		return 'Nonexistent file/directory: '. $input;
	}

	// check to make sure the file is in one of the configured directories
	$valid = 0;
	$tok = strtok($GLOBALS['MP3DIRS'] .';sounds/', ';');
	while($tok) {
		if(substr($input, 0, strlen($tok)) == $tok) {
			// access granted, file/dir is in the configged dirs
			return '';
		}
		$tok = strtok(';');
	}
	return 'Access denied.';
}

/*
 * feed it number of seconds, gives you back time in HH:MM:SS format
 */
function secs2time($secs) {
	if($secs < 0) $secs = 0; // no negative seconds, mmkay?

    $hours = floor($secs / 3600);
    $mins = floor(($secs % 3600) / 60);
    $secs = floor(($secs % 60));
    if ($hours) {
        return sprintf("$hours:%02d:%02d", $mins, $secs);
//    } else if ($mins) {
    } else {
        return sprintf("$mins:%02d", $secs);
//    } else {
//		return $secs;
	}
}

/*
 * feed it time in HH:MM:SS format, gives you back number of seconds
 */
function time2secs($time) {
	if(preg_match('/^([0-9]{,3})+:([0-9]{2}):([0-9\.]{2,})$/', $time, $regs)) {
			return (($regs[1]*3600) + ($regs[2]*60) + round($regs[3]));
	} else if (preg_match('/^([0-9]{1,2}):([0-9\.]{2,})$/', $time, $regs)) {
			return ($regs[1]*60) + (round($regs[2]));
	} else if (preg_match('/^[0-9\.]{1,}$/', $time)) {
			return round($time);
	}
	return FALSE;
}

function isHandheld() {
    if(strpos($_SERVER['HTTP_USER_AGENT'], 'PalmOS') !== false
    || strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mini') !== false
    || strpos($_SERVER['HTTP_USER_AGENT'], 'Windows CE') !== false) {
        return true;
    }
    return false;
}


function hasData($intput) {
    if(!isset($input)) return false;
    return $input;
}

?>
