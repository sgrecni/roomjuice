<?php
require_once('_flock.php');

/*
 * reads in $filename, returns filename of song on success, null on error
 */
function playlist_firstsong($filename) {
	$fp = @flopen($filename, 'r');
	if(!$fp) {
		echo 'Cannot open file: '. $filename ."\n";;
		return(0);
	}
	// eat all comments before first legit song
	while(!feof($fp)) {
		$t = fgets($fp, 1024);
		if(substr($t, 0, 1) != '#') break;
	}
	flclose($fp);
	return(substr($t,0,-1));
}

/*
 * reads in $filename into an array
 * returns array on success, 0 on error
 */
function playlist_read($filename) {
	$fp = @flopen($filename, 'r');
	if(!$fp) {
		return(0);
	}

	settype($filedata, 'array');
	$pos=0;
	while(!feof($fp)) {
		$t = fgets($fp, 2048);
		if(!$t) continue;
		if($t[0] == '#') {
			if(substr($t, 1, 7) == 'EXTINF:') {
				$obj->time = substr($t,8, (strpos($t, ',')-8));
				$obj->name = substr($t,(strpos($t, ',') + 1), -1);
			}
		} else {
			$obj->file = substr($t, 0, -1);
			$filedata[$pos++] = $obj;
			unset($obj);
		}
	}

	flclose($fp);
	return($filedata);
}

/*
 * mode is either replace, remove, write
 * writes $filedata to $filename, starting at position 1 of array
 * return 0 on error, 1 on success
 * pad: add a dummy line at top of playlist
 * remove: removes all occurances of $song array
 * write:  no action, just write out data, $song is not used
 */
function playlist_write($mode, $filename, $filedata=array(), $song='') {
	settype($filedata, 'array');
	$fp = @flopen($filename, 'w');
	if(!$fp) {
		return(0);
	}
	if($mode=='pad') fputs($fp,"<!--pad-->\n");

	$num = count($filedata);
	for($i=0; $i < $num; $i++) {
		if($filedata[$i]=='skip') continue;
		if($mode=='remove' && $i != 0) {
			@reset($song);
            foreach(array_keys($song) as $j) {
				if(!strcmp($filedata[$i]->file,$song[$j])) {
					// found a song to remove, so skip writing this entry
					unset($song[$j]);
					continue(2);
				}
			}
		}
		fputs($fp, '#EXTINF:'. $filedata[$i]->time .','. $filedata[$i]->name ."\n");
		fputs($fp, $filedata[$i]->file ."\n");
	}
	flclose($fp);
	return(1);
}

?>
