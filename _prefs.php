<?php
require_once('_flock.php');
define('DBG', 0);

class prefs {
	var $file;
	var $lock;
	var $fp;
	var $data;
	var $dirty;

	function prefs($lock = TRUE, $file = '_state') {
		$this->file = $file;
		$this->lock = $lock;
		$this->dirty = FALSE;
	}

	function read() {
		$this->fp = flopen($GLOBALS['PLAYLISTDIR'] . $this->file, ($this->lock) ? 'r+' : 'r');

		if(!$this->fp) {
			return FALSE;
		}

		settype($this->data, 'array');
		while(!feof($this->fp)) {
			$t = fgets($this->fp, 1024);
            if(!$t) continue;
	        list($v, $d) = explode('=', $t);
			if($v && $d) $this->data[trim($v)] = trim($d);
		}
		if(!$this->lock) {
			flclose($this->fp);
		}
	}

	function clear() {
		if(!is_array($this->data)) $this->read();
		$this->data = array();
		$this->dirty = TRUE;
	}

	function close() {
		if($this->fp && $this->lock) flclose($this->fp);
	}

	function write() {
		if(DBG) echo "called write()\n";

		if(!$this->fp && $this->lock) {
			error_log('you need to lock the prefs before writing');
			return FALSE;
		}

		if($this->dirty) {
			if(DBG) echo "data is dirty\n";
			fseek($this->fp, 0);
			ftruncate($this->fp, 0);

			foreach($this->data as $k => $v) {
				if($k && $v) fwrite($this->fp, "$k=$v\n");
			}
		}

		flclose($this->fp);
		return TRUE;
	}

	function getval($key) {
		if(!is_array($this->data)) $this->read();

        if(!isset($this->data[$key])) return false;
		return $this->data[$key];
	}

	function setval($key, $val = '') {
		if(!is_array($this->data)) $this->read();

        if(!isset($this->data[$key])) $this->data[$key] = '';
		if($this->data[$key] != $val) {
			$this->data[$key] = $val;
			$this->dirty = TRUE;
		}

	}

	function getinc($key, $step = 1) {
		if(!is_array($this->data)) $this->read();

		$this->dirty = TRUE;
		return $this->data[$key] += $step;
	}
}

?>
