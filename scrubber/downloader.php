<?php

session_start();

require_once("uploader.php");

$file = SCRUB_DIR . "temp.txt";

$writefile = fopen($file, 'w') or die("can't open file");

fwrite($writefile, $_SESSION["scrubbed"]);

fclose($writefile);

header('Content-type: text/plain');
//open/save dialog box
header('Content-Disposition: attachment; filename=' . pathinfo($_SESSION["file"], PATHINFO_FILENAME) . '_scrubbed.txt');
//read from server and write to buffer
readfile($file);

?>
