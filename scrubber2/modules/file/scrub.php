<?php

require_once("../texts/scrub2.php");

//Faking call from JavaScript

$a['type'] = 'default';
$a['text'] = "<b>Mary</b> had The <em>little</em> Margaret a their lamb They're.";
$a['stopWords'] = "a, the";
$a['lemmaKeys'] = "Mary,little";
$a['lemmas'] = "Margaret,tiny";

print("Before serialized, type is: " . $a['type'] . "\n");

$encoded = json_encode($a);

print($encoded . "\n\n\n");

$serialized = serialize($encoded);

print($serialized . "\n\n\n");


$_GET['LEXOMICS'] = $serialized;

//Sam calls into here.

$unserialized = unserialize($_GET['LEXOMICS']);
$decoded = json_decode($unserialized);
//print_r($decoded);
$type = $decoded->type;
$text = $decoded->text;
$stopWords = $decoded->stopWords;
$lemmaKeys = $decoded->lemmaKeys;
$lemmas = $decoded->lemmas;

print("Before scrub_text, type is: " . $type . "\n");

scrub_text($text, $stopWords, $lemmaKeys, $lemmas, $type);

print("After scrub_text.\n");

?>
