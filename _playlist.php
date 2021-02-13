<?php
require_once('_flock.php');
require_once('_prefs.php');
require_once('_songinfo.php');

class playlist {
    var $filename;
    var $data;
    var $playlistmtime;
    var $nextsong;
    var $dirty;
    
    function playlist($playlist='') {
        $this->setplaylist($playlist);
        settype($this->data, 'array');
        $this->dirty = TRUE;
    }
    
    function setplaylist($playlist='') {
        if($playlist) {
            $this->filename = $GLOBALS['PLAYLISTDIR'] . $playlist;
        } else {
            $this->filename = $GLOBALS['PLAYLIST'];
        }
    }

    function read() {
        unset($this->data);
        settype($this->data, 'array');

        $fp = @flopen($this->filename, 'r');
        if(!$fp) {
            error_log("Cannot open file: $this->filename");
            return;
        }

        $max = 0;
        $this->dirty = FALSE;
        $obj = new stdClass;

        while(!feof($fp)) {
            $buff = rtrim(fgets($fp, 2048));
            if(!$buff) continue;
            if($buff[0] == '#') {
                if(strpos($buff, 'SONGNUM:') == 1) {
                    $obj->num = substr($buff, 9);
                } else if(strpos($buff, 'EXTINF:') == 1) {
                    $pos = strpos($buff, ',');
                    $obj->time = substr($buff, 8, $pos - 8);
                    $obj->name = substr($buff, $pos + 1);
                }   
            } else {
                $obj->file = $buff;
                $this->data[] = $obj;
                $obj = new stdClass;
            }
        }

        flclose($fp);
    }

    function firstsong() {
        $fp = @flopen($this->filename, 'r');
        if(!$fp) {
            error_log("Cannot open file: $this->filename");
            return;
        }
        // eat all comments before first legit song
        while(!feof($fp)) {
            $t = fgets($fp, 1024);
            if(substr($t, 0, 1) != '#') {
                break;
            }
        }
        flclose($fp);
        return(rtrim(substr($t,0)));
    }

    /* cache the next song in playlist to get ready to play it
     * only bother caching if the mtime has changed
     */
    function cachenextsong() {
        clearstatcache();   
        if(filemtime($this->filename) == $this->playlistmtime) {
            return;
        }
        $this->playlistmtime = filemtime($this->filename);
        
        $fp = @flopen($this->filename, 'r');
        if(!$fp) {
            error_log("Cannot open file: $this->filename");
            return;
        }
        // eat all comments before first legit song
        while(!feof($fp)) {
            if(substr(fgets($fp, 1024), 0, 1) != '#') {
                break;
            }
        }
        while(!feof($fp)) {
            $t = fgets($fp, 1024);
            if(substr($t, 0, 1) != '#') break;
        }
        flclose($fp);
        $this->nextsong = rtrim(substr($t,0));
    }

    /* returns FALSE on error, TRUE on success */
    function add($file, $unique=0) {
        if(!preg_match($GLOBALS['FILETYPES'], $file)) {
            return FALSE;
        }

        if($unique) {
            reset($this->data);
            while(list($k) = each($this->data)) {
                if($this->data[$k]->file == $file) {
                    return;
                }
            }
        }
        $o = songinfo($file);
        if(!is_object($o)) {
            return FALSE;
        }
        $o->num = gensongnum(); 
        $this->data[] = $o;
        $this->dirty = TRUE;

        if(count($this->data) <= 1) {
            // if this is the first song in the playlist, start playback right away
            $this->write();
            include_once('_control.php');
            play();
            usleep(800000); // sleep for .8 seconds
        }
        return TRUE;
    }

    function addrandom($dir) {
        if(!$dir) $dir = $GLOBALS['MP3DIRS'];
        $da = explode(';', $dir);
        $k = array_rand($da, 1);
    
        $dir = $da[$k];
    } 

    function remove($songs) {
        settype($songs, 'array');
        foreach($songs as $song) {
            $pos = strpos($song, ',');
            $n = substr($song, 0, $pos);
            $f = substr($song, $pos + 1);
            foreach($this->data as $k => $v) {
                if(($n && $v->num == $n && $v->file == $f) || (!$n && $v->file == $f)) {
                    unset($this->data[$k]);
                    $this->dirty = TRUE;
                    break;
                }
            }
        }
    }

    function first($songs) {
        $old_data=$this->data;
        settype($old_data, 'array');
        $i=0;
        // keep the first song where it is if the music is playing
        $pr = new prefs(FALSE);
        if($pr->getval('playtime')) {
            $i++;
        }

        settype($songs, 'array');
        foreach(array_reverse($old_data,true) as $k => $v) {
            foreach($songs as $song) {
                $pos = strpos($song, ',');
                $n = substr($song, 0, $pos);
                $f = substr($song, $pos + 1);
                if(($n && $v->num == $n && $v->file == $f) || (!$n && $v->file == $f)) {
                    foreach($this->data as $j => $v) {
                        if($this->data[$j]==$old_data[$k]) unset($this->data[$j]);
                    }           
                    array_unshift($this->data,$old_data[$k]);
                    $this->dirty = TRUE;
                    if($i==0) break;                                    
                    $swap=$this->data[0];
                    $this->data[0]=$this->data[1];
                    $this->data[1]=$swap;
                    break;
                }
            }
        }
    }

    function up($songs) {
        settype($songs, 'array');
        foreach($this->data as $k => $v) {
            foreach($songs as $song) {
                $pos = strpos($song, ',');
                $n = substr($song, 0, $pos);
                $f = substr($song, $pos + 1);
                if(($n && $v->num == $n && $v->file == $f) || (!$n && $v->file == $f)) {
                    //if($i!=0 && $k==1)break;
                    if($k!=0 && $k==1)break;
                    $swap=$this->data[$k];
                    $this->data[$k]=$this->data[$k-1];
                    $this->data[$k-1]=$swap;
                    $this->dirty = TRUE;
                    break;
                }
            }
        }
    }

    function down($songs) { //rev
        settype($songs, 'array');
        foreach(array_reverse($this->data,true) as $k => $v) {
            foreach($songs as $song) {
                $pos = strpos($song, ',');
                $n = substr($song, 0, $pos);
                $f = substr($song, $pos + 1);
                if(($n && $v->num == $n && $v->file == $f) || (!$n && $v->file == $f)) {
                    if($k>=count($this->data)-1)break;
                    $swap=$this->data[$k];
                    $this->data[$k]=$this->data[$k+1];
                    $this->data[$k+1]=$swap;
                    $this->dirty = TRUE;
                    break;
                }
            }
        }
    }

    function last($songs) {
        settype($songs, 'array');
        foreach($this->data as $k => $v) {
            foreach($songs as $song) {
                $pos = strpos($song, ',');
                $n = substr($song, 0, $pos);
                $f = substr($song, $pos + 1);
                if(($n && $v->num == $n && $v->file == $f) || (!$n && $v->file == $f)) {
                    $this->data[] = $this->data[$k];                
                    unset($this->data[$k]);
                    $this->dirty = TRUE;
                    break;
                }
            }
        }
    }

    function write($opt = '') {
        if(!$this->dirty) return FALSE;  // only write if dirty

        $fp = @flopen($this->filename, 'w');
        if(!$fp) {
            error_log("Cannot open file: $this->filename");
            return FALSE;
        }
        //if($opt=='pad') fputs($fp, " ");
        if($opt=='pad') {
            fputs($fp, "#SONGNUM:1\n");
            fputs($fp, "#EXTINF:0,XXX\n");
            fputs($fp, "sounds/empty.mp3\n");
        }
        foreach($this->data as $v) {
            if(isset($v->num)) {
                fputs($fp, "#SONGNUM:$v->num\n");
            }
            //logger('file='. $v->file .' time='. $v->time . ' name='. $v->name);
            if(isset($v->time) && isset($v->name)) {
                fputs($fp, '#EXTINF:'. $v->time .','. $v->name ."\n");
            }
            fputs($fp, $v->file ."\n");
        }
        $this->dirty = FALSE;
        flclose($fp);
        return TRUE;
    }
}


///* give it a filename, it returns a playlist object */
//function songinfo($file) {
//  $obj->file = $file;
////    if(preg_match('/\.ogg$/i', $file)) {
////        exec($GLOBALS['OGGINFO'] .' '. escapeshellarg($file), $output);
////        while(list(,$t) = @each($output)) {
////            if(!$artist && preg_match('/artist=(.+)/i', $t, $r)) {
////                $artist = $r[1];
////            } else if(!$title && preg_match('/title=(.+)/i', $t, $r)) {
////                $title = $r[1];
////            } else if(preg_match('/Playback length: ([0-9:m\.]+)s$/', $t, $r)) {
////                $obj->time = time2secs(str_replace('m', '', $r[1]));
////                break;
////            } else if(preg_match('/length=([0-9\.]+)$/', $t, $r)) {//  this is for ogginfo pre 1.0
////                $obj->time = round($r[1]);
////                break;
////            }
////        }
////    } else if(preg_match('/\.mp3|\.mp2$/i', $file)) {
////        exec($GLOBALS['MP3INFO'] .' -p "%a\n%t\n%S" '.
////            escapeshellarg($file), $output);
////        $artist = $output[0];
////        $title = $output[1];
////        $obj->time = $output[2];
//  if(preg_match('/\.mod|\.s3m|\.it|\.xm|\.far|\.mtm|\.669$/i', $file)) {
//      exec($GLOBALS['MODINFO'] .' '. escapeshellarg($file), $output);
//      while(list(,$t) = @each($output)) {
//          if(preg_match('/^Estimated time : ([min0-9\.]+)s$/', $t, $r)) {
//              $obj->time = time2secs(str_replace('min', ':', $r[1]));
//              break;
//          }
//      }
////    } else if(preg_match('/\.ogg|\.mp3|\.mp2$/i', $file)) {
//    } else {
//      exec($GLOBALS['TAGINFO'] .' '. escapeshellarg($file), $output);
//      while(list(,$t) = @each($output)) {
//          if(!$artist && preg_match('/ARTIST=(.+)/', $t, $r)) {
//              $artist = $r[1];
//          } else if(!$title && preg_match('/TITLE=(.+)/', $t, $r)) {
//              $title = $r[1];
//          } else if(preg_match('/LENGTH=([0-9\.]+)$/', $t, $r)) {
//              $obj->time = round($r[1]);
//              break;
//          }
//      }
//    }
//
//  if($artist && $title) {
//      $obj->name = $artist .'--'. $title;
//  } else {
//      if($artist) {
//          $title = substr($file, (strrpos($file, '/') + 1));
//          $title = substr($title, 0, strrpos($title, '.'));
//          
//          $obj->name = $artist .'--'. $title;
//      } else {
//          $obj->name = $title;
//      }
//  }
//  return $obj;
//}

function gensongnum() {
    //list($usec, $sec) = explode(" ", microtime());
    //return ((float)$usec + (float)$sec);
    $t = microtime(true);
    return str_replace('.', '_', $t);
}

?>
