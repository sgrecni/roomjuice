<?php
require_once('_shared.php');

function startlog() {
    ini_set('error_log', $GLOBALS['PLAYLISTDIR'] . 'queue.log');
    ini_set('log_errors', true);
    ini_set('log_errors_max_len', 4096);
    ini_set('display_errors', false);
    ini_set('html_errors', false);
}

function log_close() {
    ini_set('log_errors', false);
    ini_set('error_log', null);
    ini_set('display_errors', false);
}

function logger($m) {
    //$GLOBALS['mark'] = time();
    error_log($m);
}

function error($s, $b) {
    error_log("[***ERROR***] $s\n$b");
}

?>
