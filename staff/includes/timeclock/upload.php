<?php

/* JPEGCam Test Script */
/* Receives JPEG webcam submission and saves to local file. */
/* Make sure your directory has permission to write files as your web server user! */

$filename = date('YmdHis') . '.jpg';

if (!file_exists('../../uploads/timelogs/'.date('Y').'/')) {
	mkdir('../../uploads/timelogs/'.date('Y').'/', 0777, true);
	chmod('../../uploads/timelogs/'.date('Y').'/', 0777);
} 

$result = file_put_contents('../../uploads/timelogs/'.date('Y').'/'.$filename, file_get_contents('php://input') );
if (!$result) {
	print "ERROR: Failed to write data to $filename, check permissions\n";
	exit();
}

chmod('../../uploads/timelogs/'.date('Y').'/'.$filename, 0777);

$url = 'http://careerph.tatepublishing.net/staff/uploads/timelogs/' . $filename;
print "$url\n";

?>
