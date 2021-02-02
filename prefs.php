<?php
if(isset($_POST['save']) && $_POST['save']) {
    $theme = 'theme/'. $_POST['NEWTHEME'];
    if(is_dir($theme)) {
        // set a cookie for 90 days
        setcookie('THEME', $_POST['NEWTHEME'], time() + 86400*90);
        $_COOKIE['THEME'] = $_POST['NEWTHEME'];
        echo "<html>
            <script><!--
                top.location = 'index.php';
            --></script>
        <noscript>no javascript?  <a href=index.php>go here</a></noscript>
        </html>";
    //	echo "<meta target=_top http-equiv=\"Refresh\" content=\"0; url=.\">";
        exit();
    }
}
require('_shared.php');

include_once('defaultheader.php');
?>

<a target="_blank" href="http://grecni.com/roomjuice/">Room Juice Homepage</a>

<?php
if($access['root']) {
	echo " -- <a href=check.php>check config for errors</a>\n";
}
?>

<p>
<form method="post">

Theme:
<select name="NEWTHEME">
<?php
$dir = 'theme';
if(!($dp = @dir($dir))) {
	barf('Can\'t open directory.');
}
// walk through the dir
$themes = array();
while($t = $dp->read()) {
	if($t=='.' || $t == '..') continue;
	if(is_dir("$dir/$t")) {
        $themes[$t] = $t;
	}
}
ksort($themes);
foreach($themes as $v) {
    echo "<option value=\"$v\"";
    if($v == $THEME) echo ' SELECTED';
    echo ">$v\n";
}
?>
</select><p>

<input type="submit" name="save" value="Save">
</form>

<?php
include_once('defaultfooter.php');
?>
