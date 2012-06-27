<?php

session_start();

define( "SCRUB_DIR", '/tmp/scrubber/' );

$file = SCRUB_DIR . "temp.txt";

$writefile = fopen($file, 'w') or die("can't open file");

fwrite($writefile, $_SESSION["scrubbed"]);

$options = "\n" . "Scrubber Options:";

if ($_SESSION["POST"]["lowercasebox"] == "on") {
	$options .= " Lowercase Removed,";
}
if ($_SESSION["POST"]["punctuationbox"] == "on") {
	$options .= " Punctuation Removed,";
}
if ($_SESSION["POST"]["digitsbox"] == "on") {
	$options .= " Digits Removed,";
}
if ($_SESSION["POST"]["lowercasebox"] == "on") {
	$options .= " Tags Stripped,";
}
if ($_SESSION["POST"]["lowercasebox"] == "on") {
	$options .= " Common Words Replaced,";
}

fwrite($writefile, substr($options, 0, -1));


fclose($writefile);

xattr_set($file, 'Scrubber Options', "test");

header('Content-type: text/plain');
//open/save dialog box
header('Content-Disposition: attachment; filename=' . pathinfo($_SESSION["file"], PATHINFO_FILENAME) . '_scrubbed.txt');
//read from server and write to buffer
readfile($file);

?>
