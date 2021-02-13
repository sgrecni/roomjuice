<?php
require_once('_shared.php');

// user entered vars
$filename = stripslashes($_GET['filename']);
$filename = strip_tags($filename);
if($t = notvalidfd($filename)) {
	barf($t);
}

if (preg_match($GFXTYPES, $filename)) {
	$fp = fopen($filename, 'r');
	if(!$fp) barf('Cannot open file');
	header('Content-type: image/jpeg');
	while(!feof($fp)) {
		echo fread($fp, 4096);
	}
	fclose($fp);
	exit();
} else if (preg_match($TXTTYPES, $filename)) {
	echo '<xmp>';
	$fp = fopen($filename, 'r');
	if(!$fp) barf('Cannot open file');
	while(!feof($fp)) {
		echo rtrim(fgets($fp, 4096)) ."\n";
	}
	fclose($fp);
	echo '</xmp>';
	exit();
}

$showback = 1;
include_once('defaultheader.php');
include_once('_songinfo.php');

if(preg_match($FILETYPES, $filename)) {

    $o = songinfo($filename, true);

    echo "<table cellspacing=0 cellpadding=4 border=1>\n";

    $f = substr($filename, strrpos($filename, '/') + 1);
    echo "<tr><td colspan=5><b>File Info for:</b> $f</td></tr>\n";
    if($o->title || $o->track) {
        echo "<tr>\n";
        if($o->title) {
            $colspan = (($o->track) ? 4 : 5);
            echo "<td colspan=$colspan><b>Title:</b> $o->title</td>\n";
        }
        if($o->track) {
            echo "<td width=60 nowrap><b>Track:</b> $o->track</td>\n";
        }
        echo "</tr>\n";
    }

    if($o->artist) {
        echo "<tr>\n";
        echo "<td colspan=5><b>Artist:</b> $o->artist</td>\n";
        echo "</tr>\n";
    }
    if($o->album) {
        echo "<tr>\n";
        echo "<td colspan=5><b>Album:</b> $o->album</td>\n";
        echo "</tr>\n";
    }
    if($o->genre) {
        echo "<tr>\n";
        echo "<td colspan=5><b>Genre:</b> $o->genre</td>\n";
        echo "</tr>\n";
    }
    if($o->comment) {
        echo "<tr>\n";
        echo "<td colspan=5><b>Comment:</b> $o->comment</td>\n";
        echo "</tr>\n";
    }

    if($o->time || $o->size || $o->type || $o->bitrate || $o->samplerate) {
        echo "<tr>\n";
        if($o->time) {
            echo "<td><b>Time:</b> ". secs2time($o->time) ."</td>\n";
        }
        if($o->size) {
            echo "<td nowrap><b>Size:</b> ". humanreadable($o->size) ."</td>\n";
        }
        if($o->type) {
            echo "<td>$o->type</td>\n";
        }
        if($o->bitrate) {
            echo '<td>'. $o->bitrate ."Kibit/s</td>\n";
        }
        if($o->samplerate) {
            echo '<td>'. $o->samplerate ."hz</td>\n";
        }
        echo "</tr>\n";
    }

    if(isset($o->mod)) {
        echo "<tr>\n";
        echo "<td colspan=5><pre>$o->mod</pre></td>\n";
        echo "</tr>\n";
    }


    echo "</table><p>\n";

//	echo '<pre>';
//    var_dump($o);	
//	echo '</pre>';
?>

<form action="playlistmod.php" method="post" target="rjplaylist">
<input type="hidden" name="append" value="<?php echo $filename?>">

	
<table cellspacing=0 cellpadding=3 border=0>
<tr>
	<td>
<script language="javascript"><!--
	function populate(f) {
		if(f.form.playlist_sel.value == "") {
			document.getElementById("newplaylist").style.visibility="visible";
		} else {
			document.getElementById("newplaylist").style.visibility="hidden";
		}
		f.form.playlist.value = f.form.playlist_sel.value;
	}
	document.write("<select name=\"playlist_sel\" onchange=\"populate(this)\">\n");
	document.write("<option value=\"\">new (enter name to the right)\n");
<?php
$dir = 'playlists';
$dp = @dir($dir);
// walk through the dir
while($t = $dp->read()) {
	if($t[0]=='.' || $t[0] == '_') continue;
	if(is_file("$dir/$t")) {
        if(substr($t, -4) != '.m3u') continue;
		$t = str_replace('.m3u', '', $t);
		echo "\t";
		echo 'document.write("\t<option value=\"'. $t .'\">'. $t .'\n")';
		echo "\n";
	}
}
?>
	document.write("</select>\n");
	//--></script>
	<!--script type="text/html">
	Hello
	</script-->
	</td>

	<td>
	<div id="newplaylist" style="visible:hidden; z-index=1;">
	<input type="text" name="playlist" value"" size="16" maxlength="32">
	</div>
	</td>

	<td>
	<input type="submit" name="button" value="add to playlist">
	</td>
</tr>
</table>
	</form><p>
<?php
} else {
	echo 'Invalid file type.';
}

include_once('defaultfooter.php');
?>
