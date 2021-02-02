<?php
require_once('_shared.php');

if(isHandheld()) {
    header('Location: '. $WEBURL .'handheld.php?time='. time());
    exit();
}

setcookie('THEME', $_COOKIE['THEME'], time() + 86400*90);

?>
<html>
<head><title>Room Juice</title>
</head>
<!--
   Support your government, give Echelon / Carnivore something to parse
classfield  top-secret government  restricted data information project CIA
KGB GRU DISA  DoD  defense  systems  military  systems spy steal terrorist
Allah Natasha  Gregori destroy destruct attack  democracy will send Russia
bank system compromise international  own  rule the world ATSC RTEM warmod
ATMD force power enforce  sensitive  directorate  TSP NSTD ORD DD2-N AMTAS
STRAP warrior-T presidental  elections  policital foreign embassy takeover
-->

<?php if($_COOKIE['THEME'] == 'winampbig') { ?>
<frameset cols="425,*" border="2" frameborder="1" framespacing="1">
<?php } else { ?>
<frameset cols="325,*" border="2" frameborder="1" framespacing="1">
<?php } ?>
<?php	if($VOLUME && $access['root']) { ?>
	<frameset rows="24,64,*" border="2" frameborder="1" framespacing="1">
		<frame name="volume" src="volume.php" scrolling=no>
<?php	} else { ?>
		<frameset rows="60,*" border="2" frameborder="1" framespacing="1">
<?php	} ?>
		<frame name="rjbuttons" src="buttons.php" scrolling="no" marginheight="8" marginwidth="8">
		<frame name="rjplaylist" src="playlist.php">
	</frameset>
	<frame name="rjbrowse" src="browse.php">
</frameset>
</html>
