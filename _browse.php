<?php

$files = array();
$dirs = array();

/*
 * Input $dir
 * Output: sets global arrays:  $files[], $dirs[]
 */
function directory_browse(&$dir) {
    global $files, $dirs;
    $files = array();
    $dirs = array();

	/* check if $dir is a valid configured directory */
	if($t = notvalidfd($dir)) {
		$dir = '';
	}

	/* if only one directory, go into that directory right away */
	if(!$dir && !strpos($GLOBALS['MP3DIRS'], ';')) {
		$dir = $GLOBALS['MP3DIRS'];
	}


	if($dir=='') {  // show list of config dirs, and no files
		$confdirs = explode(';', $GLOBALS['MP3DIRS']);
		reset($confdirs);
        foreach($confdirs as $t) {
			$dirs[]=$t;
		}
	} else {
		// open up the directory the user wants, cause it's valid
		if(!($dp = @dir($dir))) {
			barf('Can\'t open directory.');
		}

		// walk through the dir
		while($t = $dp->read()) {
			if($t=='.') continue;
			if(@is_dir($dir .'/'. $t)) { // is a dir
				$dirs[]=$t;
			} else { // is a file
				if(preg_match($GLOBALS['FILETYPES'], $t)
				|| preg_match($GLOBALS['TXTTYPES'], $t)
				|| preg_match($GLOBALS['GFXTYPES'], $t))
					$files[]=$t;
			}
		}
		$dp->close();
	}
	return;
}

?>
