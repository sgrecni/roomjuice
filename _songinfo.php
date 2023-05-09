<?php

class Song {
    var $album;
    var $artist;
    var $bitrate;
    var $comment;
    var $genre;
    var $time;
    var $title;
    var $track;
    var $samplerate;
    var $mod;
}

/* give it a filename, it returns a playlist object */
function songinfo($file, $full=false) {
	if(preg_match('/\.mod|\.s3m|\.it|\.xm|\.far|\.mtm|\.669$/i', $file)) {
		exec($GLOBALS['MODINFO'] .' '. escapeshellarg($file), $output);
        $o = songinfo_parsemod($output, $full);
    } else {
        if($GLOBALS['TAGINFO']) {
		    exec($GLOBALS['TAGINFO'] .' '. escapeshellarg($file), $output);
        }

        if(count($output) <= 1) { /* fall back on old method if taginfo isn't installed */
            if(isset($GLOBALS['OGGINFO']) && preg_match('/\.ogg$/i', $file)) {
                exec($GLOBALS['OGGINFO'] .' '. escapeshellarg($file), $output);
            } else if(isset($GLOBALS['MP3INFO']) && preg_match('/\.mp3|\.mp2$/i', $file)) {
                exec($GLOBALS['MP3INFO'] .
                ' -p "ALBUM=%l\nTRACK=%n\nARTIST=%a\nTITLE=%t\nLENGTH=%S\nBITRATE=%r\nSAMPLERATE=%Q\n" '.
                 escapeshellarg($file), $output);
            }
        }

        $o = songinfo_parse($output);
    }    
    $o->size = filesize($file);
    $o->type = strtolower(substr($file, strrpos($file, '.') + 1));
	$o->file = $file;

	if($o->artist && $o->title) {
		$o->name = $o->artist .'--'. $o->title;
	} else {
		if($o->artist) {
			$title = substr($o->file, (strrpos($o->file, '/') + 1));
			$title = substr($title, 0, strrpos($title, '.'));
			
			$o->name = $o->artist .'--'. $title;
		} else {
			$o->name = $o->title;
		}
	}
	return $o;
}


function songinfo_parsemod($output, $full) {
    foreach($output as $t) {
        if(preg_match('/^Estimated time : ([min0-9\.]+)s$/', $t, $r)) {
            $o->time = time2secs(str_replace('min', ':', $r[1]));
        }
        if($full) {
            if($t == "can't reset terminal!") continue;
            $o->mod .= $t ."\n";
        }
    }
    return $o;
}


function songinfo_parse($output) {
    $o = new Song();
    foreach($output as $t) {
        $t = trim($t);
        if(preg_match('/Playback length: ([0-9:m\.]+)s/', $t, $r)) {
            $o->time = round($r[1]);
            continue;
        }

        $p = strpos($t, '=');
        if($p === FALSE) continue;
        $key = strtoupper(substr($t, 0, $p));
        $val = substr($t, $p+1);
        $val = trim($val);

        if(preg_match('/^[\"]?(.*?)[\"]?$/', $val, $r)) {
            $val = $r[1];
        }
        switch($key) {
            case 'ALBUM': $o->album  = $val; break;
            case 'ARTIST': $o->artist = $val; break;
            case 'BITRATE': $o->bitrate = $val; break;
            case 'COMMENT': $o->comment = $val; break;
            case 'GENRE': $o->genre = $val; break;
            case 'LENGTH': $o->time = round($val); break;
            case 'TITLE': $o->title = $val; break;
            case 'TRACK': $o->track = $val; break;
            case 'SAMPLERATE': $o->samplerate = $val; break;
            default:
        }
    }
    return $o;    
}

?>
