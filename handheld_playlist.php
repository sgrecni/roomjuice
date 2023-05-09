<form method="post" action="playlistmod.php" enctype="multipart/form-data" name="aform">
<input type="hidden" name="time" value="<?php echo time();?>">

<?php
if($access['remove']) {
?>

<table border=0 cellspacing=0 cellpadding=2>
<tr>
	<td>
		<input type="submit" name="remove" value="remove">
	    <script><!--
        var checkflag = true;
        document.write('<input type="button" value="check all" onClick="this.value=checkAll(document.aform)">');
	    //--></script>
<?php
	if($access['root']) {
		echo "\t[<a href=\"playlistcontrols.php\">playlists</a>]";
	}
?>
	</td>
</tr>
</table>

<?php
}

echo "<table cellspacing=0 cellpadding=2 border=0>\n";

// display the current playlist
$pl = new playlist();
$pl->read();

$pr = new prefs(FALSE);

if($pr->getval('playtime') && ($o = $pl->data[0])) {
    if($o->time) {
        if($pr->getval('pausetime')) {
            $secsleft = ($o->time - ($pr->getval('pausetime') - $pr->getval('playtime')));
        } else {
            $secsleft = ($o->time - (time() - $pr->getval('playtime')));
        }
    }

    echo '<tr class="altertablebgcolor"><td colspan=3>Now Playing: ';
    echo ' ( ';
    if($pr->getval('pausetime')) {
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
        if($pr->getval('pausetime')) { /* it's paused, don't count down */
            echo secs2time($secsleft) .' remaining';
        } else { /* it's playing, show dynamic count down */
//            echo "<script><!--\n";
//            echo "document.write('<span id=timecurrent></span>&nbsp;remaining');\n";
//            echo "countdown('timecurrent', $secsleft);\n";
//            echo "//--></script>\n";
//            echo '<noscript>'. secs2time($secsleft) .' remaining</noscript>';
            echo secs2time($secsleft). ' remaining';
        }
    } else {
        echo secs2time(time() - $pr->getval('playtime'));
    }
    echo " )</td>\n</tr>\n";
}


$i=0; $total=0;
foreach($pl->data as $num => $o) {
	if($o->file=='' || $o->file=="\n") continue;

	echo "<tr";
    if(!($i % 2)) echo ' class="altertablebgcolor"';
    echo ">\n\t<td valign=top>";
	if($i>0 && $access['remove']) {
		echo "<input type=\"checkbox\" name=\"CHECK_$num\" value=\"$o->num,$o->file\">";
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
//    echo "<a title=\"Display details about this song\" ";
//    echo 'target=rjbrowse href=display.php?filename='. rawurlencode($o->file) .'>';
    $o->name = str_replace('_', ' ', $o->name);
    if($o->name) {
        echo str_replace('--', ' -- ', $o->name);
    } else {
        $o->name = substr($o->file, (strrpos($o->file,'/') + 1));
        $o->name = substr($o->name, 0, (strrpos($o->name, '.')));
        echo preg_replace('/(---?| - )/', ' -- ', $o->name);
    }
	
//    echo '</a>';	
	echo "</td>\n</tr>\n";
	$i++;
}
if($total > 0) {
    echo "<tr>\n\t<td colspan=3><br>Time Remaining: ";
    if($pr->getval('pausetime')) { /* it's paused, don't count down */
        echo secs2time($total);
    } else {
//        echo "<script><!--\n";
//        echo "document.write('<span id=timetotal></span>');\n";
//        echo "countdown('timetotal', $total);\n";
//        echo "//--></script>\n";
        echo secs2time($total);
    }
    echo "</td>\n</tr>\n";
}
echo "</table>\n\n";

// reload the playlist after the current song is over
if($timeout > 0) {
	echo "<script language=\"JavaScript\"><!--\n";
	echo "function mytimeout() {\n";
	echo "	top.location = '". $WEBURL ."handheld.php';\n";
	echo "}\n";
	echo "setTimeout(\"mytimeout()\",$timeout);\n";
	echo "//--></script>\n";
}
?>

</form>
