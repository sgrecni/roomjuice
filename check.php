<?php
require_once('_shared.php');

if(!$access['root']) {
	barf('access denied');
}

function check_installed($input, $program='') {
	if($p = strpos($input, ' ')) { // ignore command line switches
		$input = substr($input, 0, $p);
	}
	if(!$program) $program=$input;
	echo "<p>\n\nchecking to see if $program is correctly configured, installed, and executable...";
	if(is_executable($input)) {
		echo '<span class=good>YES</span>';
		return 0;
	} else {
		echo '<span class=bad>NO</span>';
		return 1;
	}
}

function check_running($input) {
	echo "<p>\n\nchecking to see if $input is running... ";
	exec('ps ax|grep '. $input .'|grep -v grep', $output, $returnval);
	if(count($output) > 0 && !$returnval) {
		echo '<span class=good>YES</span>';
		return 0;
	} else {
		echo '<span class=bad>NO</span>';
		return 1;
	}
}

include_once('defaultheader.php');

/*
 * Check to make sure the php version is kosher
 */
echo 'checking php version... '. phpversion() .' <span class=';
$p = explode('.', phpversion());
if(($p[0]<4) || ($p[0]==4 && $p[1]<1)) {
	echo 'bad>FAILED</span> You MUST have php >= 4.1.0, although the latest '.
	'stable version is strongly recommended. '.
	' <b>Visit <a target="_new" href=http://www.php.net/>www.php.net</a> '.
	'for more info.</b>';
} else {
	echo 'good>OK</span>'; 
}

/*
 * We gots to have to have a 'php-cgi' command
 */
if(check_installed($PHP, 'php-cgi')) {
	putenv('PATH='. $_ENV['PATH'] .':/usr/local/bin');
	$t = trim(`which php`);
	if(!$t) $t = trim(`which php4`);
	if(!$t) {
		echo ' <b>You need cgi/command line php '.
		'installed for Room Juice to work, please see the README file for '.
		'instructions on how to do this.</b>';
	} else {
		echo " <b> \$PHP in _config.php is incorrectly set, please set it to '$t'</b>";
	}
}

/*
 * Check _config.php:$MP3DIRS
 */
$confdirs = explode(';', $GLOBALS['MP3DIRS']);
echo "<p>\n\nchecking \$MP3DIRS variable...";
$err=0;
while(list(,$dir) = each($confdirs)) {
	if(substr($dir, -1, 1) != '/') {
		echo "<span class=bad>FAILED</span> All directories set in \$MP3DIRS must end with a '/'.";
		$err=1;
		break;
	} else if(!is_dir($dir)) {
		echo "<span class=bad>FAILED</span> '$dir' is not a valid directory.";
		$err=1;
		break;
	} else {
		$dp = dir($dir);
		if(!$dp) {
			echo "<span class=bad>FAILED</span> '$dir' is not readable by the web server.";
			$err=1;
			break;
		}
		while($t = $dp->read()) {
			if($t=='.') continue;
			if(!is_readable($dir . $t)) {
				echo "<span class=bad>FAILED</span> '". $dir . $t ."' is not readable by the web server.";
				$err=1;
				break(2);
			}
		}
		$dp->close();
	}
}
if(!$err) echo ' <span class=good>OK</span>';


// check permissions on the playlist dir
echo "<p>\n\nchecking permissions on the playlists directory... ";
if(!is_writeable($PLAYLISTDIR) || !is_readable($PLAYLISTDIR)) {
	echo '<span class=bad>FAILED</span> Check to make sure that the web user can read and write to the \'playlist\' directory';
} else {
	echo '<span class=good>OK</span>';
}

echo "<p>\n\nchecking permissions on all files in the playlist directory... ";
// open up the directory the user wants, cause it's valid
if($dp = @dir($PLAYLISTDIR)) {
	// walk through the dir
    $bad = false;
	while($t = $dp->read()) {
		if($t=='..' || $t=='.') continue;
		$t = $PLAYLISTDIR . $t;
		if((!is_writable($t) || !is_readable($t))) {
			$bad = true; break;
		}
	}
	$dp->close();
	if($bad) {
		echo '<span class=bad>FAILED</span> <b>Wrong permissions set on \''.
		$t .'\'.  Please give the web user permission to read and write '.
		'to this file.</b>';
	} else {
		echo '<span class=good>OK</span>';
	}
} else {
	echo 'Can\'t open directory.';
}


if(is_file('/dev/dsp')) {
    // check permissions on the audio device
    echo "<p>\n\nchecking permissions on /dev/dsp... ";
    if(!is_writeable('/dev/dsp') || !is_readable('/dev/dsp')) {
        echo '<span class=bad>FAILED</span> Check to make sure that the web user can'.
        ' read and write to /dev/dsp';
    } else {
        echo '<span class=good>OK</span>';
    }

    // check permissions on the mixing device
    echo "<p>\n\nchecking permissions on /dev/mixer... ";
    if(!is_writeable('/dev/mixer') || !is_readable('/dev/mixer')) {
        echo '<span class=bad>FAILED</span> Check to make sure that the web user can'.
        ' read and write to /dev/mixer';
    } else {
        echo '<span class=good>OK</span>';
    }
}

if($VOLUME) {
	if(strstr($VOLUME, 'aumix') || strstr($VOLUME, 'smixer') || strstr($VOLUME, 'amixer')) {
		$c = check_installed($VOLUME);
		if($c) {
			echo "<p>\n\n<b>Edit _config.php and comment out the missing program, or ".
			'install the missing program if you desire that functionality</b>';
		}
	} else {
		echo '<p> <span class=bad>You have the $VOLUME variable in _config.php '.
		'must be set to aumix or smixer.</span>';
	}
} else {
	echo '<p> You don\'t have a volume control program configured, so the '.
	'volume control will be hidden.';
}

// check to see if locate is installed
if(check_installed(trim(`which locate`), 'locate')) {
	echo ' The installation of locate is strongly suggested, but not strictly required.  Without it, the search and the add 5 random files functions will not work.';
}

// loop through all configured players, making sure they exist
reset($MUSIC);
$c = 0;
while(list($f,$t) = each($MUSIC)) {
	$c += check_installed($t->player);
	if(isset($t->info) && $t->info) $c += check_installed($t->info);
}

if($c) {
	echo "<p>\n\n<b>Edit _config.php and comment out the missing program, or ".
	'install the missing program if you desire that functionality</b>';
}

if(check_running('icecast')) { 
	echo " The icecast server is not running, you will not be able to stream your music over the internet, but you can still play it locally.\n";
} else if(check_running('darkice')) {
	echo " Darkice is not running, you will not be able to stream your music over the internet, but you can still play it locally\n";
}

include_once('defaultfooter.php');
?>
