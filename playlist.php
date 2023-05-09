<?php
// try and convince the client not to cache this page, as it changes often
header('Pragma: no-cache');
header('Expires: 0');

require_once('_shared.php');
require_once('_playlist.php');
require_once('_prefs.php');

// an external link, direct them to start over so they get all the frames
if($_SERVER['HTTP_REFERER'] 
&& !preg_match('/^http[s]{0,1}:\/\/'. $_SERVER['HTTP_HOST'] .'/', $_SERVER['HTTP_REFERER'])) {
	echo 'Invalid Referer: '. $_SERVER['HTTP_REFERER'] ."<p>\n";
	echo 'If you followed an external link, you will want the full framed version '.
	'of this app which can be found <a target="_top" href="index.php">here</a>'; 
	exit();
}

$prefs = new prefs(FALSE);

/* fetch the playlist */
$pl = new playlist();
$pl->read();

?>
<html>
<head>
<meta name="robots" content="noindex,nofollow,noarchive">
<title>Room Juice</title>
<script language=JavaScript src="util.js"></script>
</head>
<link rel="Stylesheet" href="theme/<?php echo $GLOBALS['THEME']; ?>/main.css" type="text/css">
<body>


<form method="post" action="playlistmod.php" enctype="multipart/form-data" name="aform">

<?php
if($access['remove']) {
?>

<table border=0 cellspacing=0 cellpadding=2>
<tr>
	<td>
        <?php if(count($pl->data) > 1) { ?>
		<input type="submit" name="remove" value="del">
        <?php if($prefs->getval('playtime')) { ?>
        <input type="submit" name="first" value="next">
        <?php } else { ?>
        <input type="submit" name="first" value="1st">
        <?php } ?>
        <input type="submit" name="up" value="up">
        <input type="submit" name="down" value="down">
        <!--input type="submit" name="last" value="last"-->

	    <script><!--
        var checkflag = true;
        document.write('<input type="button" value="check all" onClick="this.value=checkAll(document.aform)">');
	    //--></script>
        <?php } ?>
<?php
	if($access['root']) {
		echo "\t[<a href=\"playlistcontrols.php\">playlists</a>]<br><br>";
	}
?>
	</td>
</tr>
</table>

<?php
}

echo "<table cellspacing=0 cellpadding=2 border=0>\n";

$timeout = 60000;
$secsleft = 0;

if($prefs->getval('playtime') && isset($pl->data[0]) && ($o = $pl->data[0])) {
    if(isset($o->time)) {
        if($prefs->getval('pausetime')) {
            $secsleft = ($o->time - ($prefs->getval('pausetime') - $prefs->getval('playtime')));
        } else {
            $secsleft = ($o->time - (time() - $prefs->getval('playtime')));
        }
    }

    echo '<tr class="altertablebgcolor"><td colspan=3>Now Playing: ';
    echo ' ( ';
    if($prefs->getval('pausetime')) {
        echo 'paused ';
    } else {
        // this is for the javascript refresh timer that is output later
        if($secsleft) {
            $timeout = ($secsleft + 5) * 1000;
        } else {
            // can't figure out secsleft, so refresh every minute
            $timeout = (60 * 1000);
        }
    }
    if($secsleft) {
        if($prefs->getval('pausetime')) { /* it's paused, don't count down */
            echo secs2time($secsleft) .' remaining';
        } else { /* it's playing, show dynamic count down */
            echo "<script><!--\n";
            echo "document.write('<span id=timecurrent></span>&nbsp;remaining');\n";
            echo "countdown('timecurrent', $secsleft);\n";
            echo "//--></script>\n";
            echo '<noscript>'. secs2time($secsleft) .' remaining</noscript>';
        }
    } else {
        echo secs2time(time() - $prefs->getval('playtime'));
    }
    echo " )</td>\n</tr>\n";
}

$i=0; $total=0; $secsleft=0;
foreach($pl->data as $num => $o) {
	if($o->file=='' || $o->file=="\n") continue;
    if(!isset($o->time)) $o->time = 0;
    if(!isset($o->name)) $o->name = $o->file;

	echo "<tr";
    if(!($i % 2)) echo ' class="altertablebgcolor"';
    echo ">\n\t<td valign=top>";
    if($i>0 && $access['remove']) {
        $checked = '';

        if(isset($_POST['CHECK_'. $o->num])) {
           $checked = ' CHECKED'; 
        }
//		echo "<input type=\"checkbox\" name=\"CHECK_$num\" value=\"$o->num,$o->file\">";
    	echo "<input type=\"checkbox\" name=\"CHECK_$o->num\" value=\"$o->num,$o->file\" $checked>";
    }	
    echo "</td>\n\t<td valign=top>";
	if($o->time > 0) {
		if($i == 0) {
			$total += $secsleft;
		} else {
			$total += $o->time;
		}
		echo secs2time($o->time);
	}
	//echo "</td>\n\t<td valign=top nowrap width=100%>";
	echo "</td>\n\t<td valign=top width=100%>";
    echo "<a title=\"Display details about this song\" ";
    echo 'target=rjbrowse href=display.php?filename='. rawurlencode($o->file) .'>';
    $o->name = str_replace('_', ' ', $o->name);
    if($o->name) {
        echo str_replace('--', ' -- ', $o->name);
    } else {
        $o->name = substr($o->file, (strrpos($o->file,'/') + 1));
        $o->name = substr($o->name, 0, (strrpos($o->name, '.')));
        echo preg_replace('/(---?| - )/', ' -- ', $o->name);
    }
    echo '</a>';	
	echo "</td>\n</tr>\n";
	$i++;
}

if($total > 0) {
    echo "<tr>\n\t<td colspan=3><br>Time Remaining: ";
    if($prefs->getval('pausetime')) { /* it's paused, don't count down */
        echo secs2time($total);
    } else {
        echo "<script><!--\n";
        echo "document.write('<span id=timetotal></span>');\n";
        echo "countdown('timetotal', $total);\n";
        echo "//--></script>\n";
        echo '<noscript>'. secs2time($total) .'</noscript>';
    }
    echo "</td>\n</tr>\n";
}
echo "</table>\n\n";

// reload the playlist after the current song is over
if($timeout > 0) {
	echo "<script language=\"JavaScript\"><!--\n";
	echo "function mytimeout() {\n";
	echo "	top.rjplaylist.location = '". $WEBURL ."playlist.php';\n";
	echo "}\n";
	echo "setTimeout(\"mytimeout()\",$timeout);\n";
	echo "//--></script>\n";
}
?>

</form>
</body>
</html>
