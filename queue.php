<?php
if(isset($_SERVER['HTTP_HOST'])) exit();  //don't allow calling from the web
set_time_limit(0);  // live forever
posix_setsid();  // make this process a session leader, disassociate with a tty

require_once('_log.php');
require_once('_shared.php');
require_once('_playlist.php');
require_once('_prefs.php');

startlog();

// a quick hack so only one of these bitches is running at a time
// stfu, it works
unset($output);
exec('ps x|grep queue.php|grep -v grep', $output, $returnval);
if(count($output) > 1) {
	logger("queue.php is already running!");
	exit();
}

$pr = new prefs();
$pl = new playlist();
do {
	if($song = $pl->firstsong()) {
        /* check if $dir is a valid configured directory */
	    if($t = notvalidfd($song)) {
            logger("song=$song not in one of the configured directories");
            sleep(1);
    	} else {
            $pr->read();
            
            $pr->setval('playtime', time());
            $pr->setval('songtype');
            $pr->setval('pausetime');

            reset($MUSIC);
            while(list($t) = each($MUSIC)) {
                if($MUSIC[$t]->filetypes && preg_match($MUSIC[$t]->filetypes, $song)) {
                    $pr->setval('songtype', $t);
                    break;
                }
            }

            if(!$pr->write()) {
                logger('cannot write to _state file, sleeping and looping');
                sleep(2);
                continue;
            }

            if($pr->getval('songtype')) {
                logger('executing '.  $MUSIC[$pr->getval('songtype')]->player .' '. escapeshellarg($song));
                exec($MUSIC[$pr->getval('songtype')]->player .' '. escapeshellarg($song) .' 2>&1 1>/dev/null');
                //logger('exec'. $MUSIC[$pr->getval('songtype')]->player .' '. escapeshellarg($song) .' 2>&1 1>'. $PLAYLISTDIR .'/queue_exec.log');
                //exec($MUSIC[$pr->getval('songtype')]->player .' '. escapeshellarg($song) .' 2>&1 1>'. $PLAYLISTDIR .'/queue_exec.log');
            } else {
                logger($song .': cannot find songtype, ignoring this file and continuing on with next file');
            }
        }
	} else {
		logger('cannot fetch the first song from playlist, sleeping and looping');
		sleep(2);
	}

	$pl->read();
	unset($pl->data[0]);
	$pl->dirty = TRUE;
	if(!$pl->write()) {
		logger('cannot write to playlist file, sleeping and looping');
		sleep(2);
	}
} while (count($pl->data) > 0);

$pr->read();
$pr->clear();
$pr->write();

?>
