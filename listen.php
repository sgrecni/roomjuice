<?php
header('Content-Type: audio/x-scpls');
//header('Content-Type: text/plain');

$a[1] = array("http://". $_SERVER['HTTP_HOST'] .":8000/roomjuice", "Room Juice (mp3)");
$a[2] = array("http://". $_SERVER['HTTP_HOST'] .":8000/roomjuice.ogg", "Room Juice (ogg)");

echo "[playlist]\n";
echo "numberofentries=". count($a) ."\n";
for($i = 1; $i <= count($a); $i++) {
	echo "File$i=". $a[$i][0] ."\n";
	echo "Title$i=". $a[$i][1] ."\n";
	echo "Length1=-1\n";
}
?>
