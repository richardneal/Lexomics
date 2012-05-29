<?php

require_once("../includes/scrub2.php");
require_once("../modules/text.php");
require_once("../modules/login.php");

session_start();
login();

//Faking call from JavaScript
/*
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
*/
//Sam calls into here.
/*
//$unserialized = unserialize($_GET['LEXOMICS']);
//$new_array = array_map(create_function('$key, $value', 'return $key.":".$value." # ";'), array_keys($_FILES), array_values($_FILES));
$tid = $_POST['textid'];
$text = $_SESSION['user']['texts'][$tid];
$textstr = $text->get_text();
//$infile = "{$_FILES['file']['tmp_name']}";
//$test = openfile( $infile );
error_log($textstr, 3, "/home/rase/Desktop/php.log");
//error_log($_POST["type"], 3, "/home/rase/Desktop/php.log");
$unserialized = unserialize($_POST);

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
*/
$errors = null;
error_log($_FILES['file']['size'], 3, "/home/rase/Desktop/php.log");

// if the file is too big, can't finish the rest of the script
if ( $_FILES['file']['size'] == 0 )
{
    $errors[] = "File is too big.";
    trigger_error( "File too big." );
}
else
{

    $newtext = new Text();
    // ensure id doe not exist
    $id = $oid = Text::id_from_name( $_POST['name'] );
    //error_log( "=======================><><><><" . $id );
    $i = 1;
    while ( array_key_exists( $id, $_SESSION['user']['texts'] ) )
    {
        $id = $oid . "_$i";
        $i++;
    }

    $errors = $newtext->set_data( $_POST, $_FILES['file'], 
        $_SESSION['user']['dir'], $id );
}

$message = Array();

if ( !$errors )
{
    $_SESSION['user']['texts'][$id] = $newtext;

	$message['success'] = true;
	$message['textpath'] = "texts/" . $id;
}
else
{
	$message['success'] = false;
	$message['errors']  = $errors;
}

echo json_encode( $message );

?>
