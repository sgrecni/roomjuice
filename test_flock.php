<?php
/* used to test the file locking, run it twice, 2nd instance should hang until
 * the first one is finished, the sleep 20 makes it sleep while the file
 * is open.  run this via command line as a user
 */
require('_prefs.php');
require('_file.php');

echo "before prefs_read()\n";
$prefs=prefs_read('_state', 1);
echo "after prefs_read() we should have a lock on the _state file now, sleeping for 20 seconds\n";

while($t = @each($prefs)) {
	echo "data: $t[0] $t[1]\n";
}

$prefs['time'] = time();
sleep(20);

echo "before prefs_write()\n";
prefs_write('_state', $prefs);
echo "after prefs_write(), file lock should have been released, that's all folks\n";

?>
