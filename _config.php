<?php
/*
 * 5 different permissions, view, add, remove, control, and root
 * view - view, if no access, client gets "access denied" message
 * add - add a file/directory
 * remove - remove file(s) 
 * control - veto and play
 * root - total access -- in addition to above permission, also allows pause, stop, clear,
 *        randomize the playlist, and plalist save/load/del
 * deny - deny access, explicitly deny an ip
 *
 * client gets all the permissions of the first match, ORDER MATTERS
 * so make sure the default wildcard (*) comes last (or not at all)
 */
$HOSTS="
192.168.*:root
127.*:root
*:add,control,view
";

/* directory that you installed the app to in your web root
 * this is basically appended to the hostname for header redirects */
$INSTALLDIR='/roomjuice/';

/* semicolon delimited list of directories where your songs are at
 * each directory MUST end in a forward slash */
//$MP3DIRS='/home/shared/mp3/';
$MP3DIRS='/morespace/shared/mp3/;/morespace/shared/incoming/;/morespace/shared/lyrics/;/morespace/shared/mods/;/morespace/shared/brokenmoon/mp3s/';

/* mask this from the user when browsing songs, regex */
//$DIRMASK='#^/home/shared/mp3/#';
$DIRMASK='#^/morespace/shared/#';


/* set this to the location where command line php is installed */
$PHP='/usr/bin/php';

/* set to the absolute aumix or smixer path depending on which one you have
 * installed comment it out if you don't want roomjuice to manage your volume */
$VOLUME='/usr/bin/amixer';
//$VOLUME='/usr/bin/aumix -d /dev/mixer';
//$VOLUME='/usr/local/bin/smixer';

/* volume slider to control, set to either pcm, pcm2, or vol for the main slider */
//$VOLUME_SLIDER_TITLE = 'Front';
$VOLUME_SLIDER_TITLE = 'Headphone';
//$VOLUME_SLIDER_TITLE = 'pcm';

/* Set this to 1 if you want to display the volume in text */
$SHOW_VOL_TEXT=1;

/* Set this to 1 if you want to display the first image in each directory
 *  (useful for displaying album covers, pictures of bands, band logos, etc.
 *  Set to 0 for no inline image display */
$DISP_IMAGE=1;

/****** hopefully you won't have to edit below this line ******/

/* uncomment this if you use icecast 1.x, don't think 2.x has a scoreboard yet :( */
//$ICECASTSTATSLOG='/var/log/icecast/stats.log';
//http://192.168.42.1:8000/admin/listclients?mount=/roomjuice.ogg

$PLAYLISTDIR='playlists/';

//$OGGINFO = '/usr/bin/ogginfo';
//$MP3INFO = '/usr/bin/mp3info 2>/dev/null';
$TAGINFO = '/usr/local/bin/taginfo';
$MODINFO = '/usr/bin/xmp 2>&1 -v --load-only';

$MUSIC = array();
$MUSIC['mp3'] = new stdClass;
//$MUSIC['mp3']->player='/usr/bin/mpg123 -q -a /dev/dsp';
//$MUSIC['mp3']->player='/usr/bin/madplay -q -o /dev/dsp';
//$MUSIC['mp3']->player='/usr/bin/madplay';
//$MUSIC['mp3']->player='/usr/bin/mpg123 -q';
$MUSIC['mp3']->player='/usr/bin/mpg123 -q -o pulse'; /* pulse output seems to fix the problem the beginning of songs being cut off */
$MUSIC['mp3']->filetypes='\.mp3|\.mp2';

$MUSIC['m3u'] = new stdClass;
//$MUSIC['m3u']->player='/usr/bin/mpg123.bin -q -a /dev/dsp --list';
$MUSIC['m3u']->player='/usr/bin/mpg123.bin -q --list';
$MUSIC['m3u']->filetypes='\.m3u';

$MUSIC['ogg'] = new stdClass;
//$MUSIC['ogg']->player='/usr/bin/ogg123 -q -d oss -o dsp:/dev/dsp ';
$MUSIC['ogg']->player='/usr/bin/ogg123 -q';
$MUSIC['ogg']->filetypes='\.ogg|\.flac';

$MUSIC['wma'] = new stdClass;
//$MUSIC['wma']->player='/usr/bin/mplayer -really-quiet -ao oss:/dev/dsp';
$MUSIC['wma']->player='/usr/bin/mplayer -really-quiet';
$MUSIC['wma']->filetypes='\.wma|\.wav|\.ram|\.ra';

$MUSIC['mod'] = new stdClass;
/* xmp seems to work/sound better with most modules, but has trouble with some .mods */
$MUSIC['mod']->player='/usr/bin/xmp -q';
$MUSIC['mod']->filetypes='\.s3m|\.it|\.xm|\.far|\.mtm|\.669|\.mod';

//$MUSIC['mod']->player='/usr/local/bin/gmodplay >/dev/null 2>&1';
//$MUSIC['mod']->filetypes='\.s3m|\.it|\.xm|\.far|\.mtm|\.669\.mod';

/* seems to play some .mod files better than xmp, but has troubles with certain .it files */
//$MUSIC['mod2']->player='/usr/bin/mikmod -q -p0 -i --hqmixer';
//$MUSIC['mod2']->filetypes='\.mod';

/* for commodore sid files */
$MUSIC['sid'] = new stdClass;
$MUSIC['sid']->player='/usr/local/bin/sidplayer >/dev/null';
$MUSIC['sid']->filetypes='\.sid';

/* for midi files */
$MUSIC['midi'] = new stdClass;
$MUSIC['midi']->player='/usr/bin/timidity >/dev/null';
$MUSIC['midi']->filetypes='\.midi?';

/* wav to play when someone veto's a song, leave blank for no sound */
$VETOWAV='sounds/klaxon.mp3';

/* fun regex, only show these files types */
$TXTTYPES='/(\.txt|\.nfo)$/i';
$GFXTYPES='/(\.jpg|\.jpeg|\.gif|\.png)$/i'
?>
