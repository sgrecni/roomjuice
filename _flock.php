<?php

/*
 * fopen wrapper, r initiates a shared lock, r+ initiates and exclusive one
 * if file DNE, try to create it
 */
function flopen($file, $mode) {
    if(!$file) return false;
	$fp = fopen($file, $mode);
	if(!$fp) {
//		echo "error accessing $file, creating it and trying again\n";
		touch($file);
		$fp = fopen($file, $mode);
		if(!$fp) return FALSE;
	}
	if($mode=='r') {
		flock($fp, LOCK_SH);
	} else if ($mode == 'r+' || $mode == 'w') {
		flock($fp, LOCK_EX);
	}
	return $fp;
}

function flclose($fp) {
	flock($fp, LOCK_UN);
	fclose($fp);
}

?>
