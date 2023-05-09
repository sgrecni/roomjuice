<?php

function microtime_diff($a, $b) {
   list($a_dec, $a_sec) = explode(" ", $a);
   list($b_dec, $b_sec) = explode(" ", $b);
   return $b_sec - $a_sec + $b_dec - $a_dec;
}

function metadata($file, $full) {
	$obj->file = $file;
	if(preg_match('/\.ogg$/i', $file)) {
		exec($GLOBALS['OGGINFO'] .' '. escapeshellarg($file), $output);
        foreach($output as $t) {
			if(!$artist && preg_match('/artist=(.+)/i', $t, $r)) {
				$artist = $r[1];
			} else if(!$title && preg_match('/title=(.+)/i', $t, $r)) {
				$title = $r[1];
			} else if(preg_match('/Playback length: ([0-9:m\.]+)s$/', $t, $r)) {
				$obj->time = time2secs(str_replace('m', '', $r[1]));
				break;
			} else if(preg_match('/length=([0-9\.]+)$/', $t, $r)) {//  this is for ogginfo pre 1.0
				$obj->time = round($r[1]);
				break;
			}
		}
	} else if(preg_match('/\.mp3|\.mp2$/i', $file)) {
		exec($GLOBALS['MP3INFO'] .' '. escapeshellarg($file), $output);
//        foreach($output as $t) {
//		}
		
		$artist = $output[0];
		$title = $output[1];
		$obj->time = $output[2];
	} else if(preg_match('/\.mod|\.s3m|\.it|\.xm|\.far|\.mtm|\.669$/i', $file)) {
		exec($GLOBALS['MODINFO'] .' '. escapeshellarg($file), $output);
        foreach($output as $t) {
			if(preg_match('/^Estimated time : ([min0-9\.]+)s$/', $t, $r)) {
				$obj->time = time2secs(str_replace('min', ':', $r[1]));
				break;
			}
		}
	}

	if($artist && $title) {
		$obj->name = $artist .'--'. $title;
	} else {
		if($artist) {
			$title = substr($file, (strrpos($file, '/') + 1));
			$title = substr($title, 0, strrpos($title, '.'));
			
			$obj->name = $artist .'--'. $title;
		} else {
			$obj->name = $title;
		}
	}
	return $obj;
}

$start_time = microtime();
var_dump(metadata('/home/shared/incoming/maintheme.mp3'));
$duration = microtime_diff($start_time, microtime());
$duration = sprintf("%0.3f", $duration);
echo ("Processing took $duration seconds");

?>
