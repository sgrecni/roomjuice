<?php
require_once('_prefs.php');
require_once('_playlist.php');

function veto() {
	// veto, this takes the playlist, removes the current song, add a 1st
	// position dummy song, 2nd song is the veto mp3, then the rest of the
	// playlist is written out
	$pl = new playlist();
	$pl->read();

	if($pl->data[0]->file != $GLOBALS['VETOWAV']
	&& (!isset($pl->data[1]) || $pl->data[1]->file != $GLOBALS['VETOWAV'])) {
        $obj = new stdClass();
        $obj->time = 4;
		$obj->name = 'Veto in progress...';
		$obj->file = $GLOBALS['VETOWAV'];
        $obj->num = gensongnum(); 
		$pl->data[0] = $obj;
		$pl->dirty = TRUE;
		$pl->write('pad');
	}

	// kill current song, dummy song is eaten by queue.php as usual after
	// a song is over, and then playback continues
	$pr = new prefs(FALSE);
	killall($GLOBALS['MUSIC'][$pr->getval('songtype')]->player);
	$pr->close();
}

// plays the music if the music is not playing
// uses the $prefs array if it exists, if it doesn't it globally creates it
function play() {
	$pr = new prefs();
    if($pr->getval('pausetime')) {
        if($pr->getval('playtime')) {
            /* it's paused, so unpause it */
            $pr->close();
            pause();
            return;
        } else { /* a paustime, but not play time?  invalid state, clear it */
           $pr->clear(); 
        }
	}

    if(!$pr->getval('playtime')) {
		$pr->setval('playtime', time());
		// start up queue.php which loops through all songs in the playlist.m3u file
		shell_exec($GLOBALS['PHP'] .' -q queue.php 1>/dev/null &');
	}
	$pr->write();
}

// stop playback
// kill queue.php script, kill music player, and clear all prefs
function stop() {
	$pr = new prefs();
	killall($GLOBALS['PHP'] .' -q queue.php');
    if(isset($GLOBALS['MUSIC'][$pr->getval('songtype')])) {
    	killall($GLOBALS['MUSIC'][$pr->getval('songtype')]->player, 9);
    }
	$pr->clear();
	$pr->write();
	return TRUE;
}
// pause and unpause playback
function pause() {
	$pr = new prefs();
	if($pr->getval('pausetime')) {
		// unpause the music by issuing -CONT kill flag
		killall($GLOBALS['MUSIC'][$pr->getval('songtype')]->player, 18);
		// take into account how long the song was paused
		$pr->setval('playtime', ($pr->getval('playtime') + (time() - $pr->getval('pausetime'))));
		$pr->setval('pausetime', '');
	} else if($pr->getval('playtime')) {
		// pause the music by issuing -STOP kill flag, and set the time we paused
		killall($GLOBALS['MUSIC'][$pr->getval('songtype')]->player, 19);
		$pr->setval('pausetime', time());
	} else {
        $pr->close();
        return;
    }
	$pr->write();
}

function clear() {
	stop();
	$pr = new prefs();
	$pr->clear();
	$pr->write();

	$pl = new playlist();
	$pl->write();
}

function randomize() {
	/*
	// randomize the playlist, keep the first song
	$data = playlist_read($PLAYLIST);
	$size = sizeof($data);

	$i = 0;
	// keep the first song where it is if the music is playing
	if($prefs['playtime']) {
		$newdata[0] = $data[0];
		$data[0] = '';
		$i++;
	}

	// randomize the playlist
	while($i < $size) {
		$random = mt_rand(0, ($size - 1));
		if ($data[$random]!='') {
			$newdata[$i] = $data[$random];
			$data[$random] = '';
			$i++;
		}
	}
	playlist_write('write', $PLAYLIST, $newdata);
	*/
	// randomize the playlist, keep the first song
	$pr = new prefs(FALSE);

	$pl = new playlist();
	$pl->read();
	$size = count($pl->data);

	$i = 0;
	// keep the first song where it is if the music is playing
	if($pr->getval('playtime')) {
		$n[] = $pl->data[0];
		unset($pl->data[0]);
		$i++;
	}
	$pr->close();

	// randomize the playlist
	while($i < $size) {
		$random = mt_rand(0, ($size - 1));
		if (isset($pl->data[$random])) {
			//echo "$i $random<br>";
			$n[] = $pl->data[$random];
			unset($pl->data[$random]);
			$i++;
		}
	}
	$pl->data = $n;
	$pl->dirty = TRUE;
	$pl->write();
}

function killall($match, $signal = 15) {
	$killed = 0;
	if($match=='') return 'no pattern specified';
	$match = trim(str_replace('>/dev/null', '', $match));
	$match = escapeshellarg($match);
//    echo "$match, $signal\n";
	exec("ps x|grep $match|grep -v grep|awk '{print $1}'", $output, $ret);
	if($ret) return 'you need ps, grep, and awk installed for this to work';
	foreach($output as $t) {
		if(preg_match('/^([0-9]+)/', $t, $r)) {
			//system("kill -$signal ". $r[1], $k);
			if(!posix_kill($r[1], $signal)) {echo "posix_kill() failed"; exit();}
			$killed = 1;
		}
	}
	if($killed) {
		return '';
	} else {
		return "$match: no process killed";
	}
}

?>
