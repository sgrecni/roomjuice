<?php

/*
 * Input: $output array of files and directories (passed as reference)
 * Output: text stats about the search
 *         sets global arrays:  $files and $dirs
 * Purpose:  Makes sure file or directory is in one of the configured directories,
 *   and is one of the sanctioned file types
 */
function search_check(&$output) {
    global $files, $dirs;
    $dirs = array();
    $files = array();
    $mode = ''; // not sure what this was supposed to be for...

	//make sure file/dir is in one of the conf dirs
	$confdirs = explode(';',$GLOBALS['MP3DIRS']);
    foreach($output as $t) {
        foreach($confdirs as $s) {
			if(substr($t, 0, strlen($s)) == $s) {
				if(is_dir($t)) {
					// add the directory
					$dirs[] = $t .'/';
				} else if(preg_match($GLOBALS['FILETYPES'], $t)
				|| preg_match($GLOBALS['TXTTYPES'], $t)
				|| preg_match($GLOBALS['GFXTYPES'], $t)) {
					// add the file
					$files[] = $t;
				}
			}
		}
	}
	return '<li><b>found '. count($dirs) .' directories and '. count($files) .
	" files matching '". $GLOBALS['words']. "'</b>.<br>\n";
}


/*
 * Input: $words
 * Output: text stats about the search
 *   also sets global arrays:  $files and $dirs
 * Purpose:  calls locate to find the given files we're searching for
 */
function song_search($words) {
	global $files, $dirs;
	$files = array();
    $dirs = array();
    $first = '';
    $add = '';
    $err = '';
	$count = 0;

	// stripslashes, trim, and replace whitespace with a single space (to explode later)
	$words = preg_replace('/\s+/', ' ',trim(stripslashes($words)));
	$wa = explode(' ', $words);
    foreach($wa as $t) {
		if(preg_match('/^[\S]{2,}$/i', $t)) {
			if(!$first) {
				$first = $t;
				$words = "'$t'";
			} else {
				$add .= " | grep -i '". escapeshellarg($t) ."'";
				$words .= " AND '$t'";
			}
			$count++;
		} else {
			$err .= '<li>\''. $t ."' is an invalid word and will be ignored.\n";
		}
	}
	if($count < 1) {	
		return $err . "<li>You didn't enter in enough search terms.\n";
	}

	// execute the locate command, this whole search is a hack, yes :)
	exec("locate -i '". escapeshellarg($first) ."'". $add, $output, $returnval);
	if($returnval && !file_exists(rtrim(`which locate`))) {
		return "Error searching!  Do you have locate installed?\n";
	}
	
	$err .= search_check($output);
    return $err;
}


/*
 * Input: $dir
 * Output: text stats about the search
 *   also sets global arrays:  $files and $dirs
 * Purpose:  calls custom function rdir to find all the files in the given directory
 */
function song_dir($dir) {
    $output = array();
	
	function rdir($appenddir, &$output) {
		$dp = dir($appenddir);
        $files = array();
        $dirs = array();
		while($t = $dp->read()) {
			if($t != '.' && $t != '..') {
				if(is_dir($appenddir . $t)) {
					$dirs[] = $t;
				} else {
					$files[] = $t;
				}
			}
		}
		$dp->close();

	    natcasesort($dirs);
		natcasesort($files);

        foreach($dirs as $t) {
            rdir($appenddir . $t .'/', $output);
        }
        foreach($files as $t) {
			$output[] .= $appenddir . $t;
		}
	}

	//exec("locate -i -r ^". escapeshellarg($dir), $output, $returnval);
	rdir($dir, $output);

	global $files, $dirs;
    $files = array();
    $dirs = array();
	return search_check($output);
}

?>
