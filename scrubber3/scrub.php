<?php
ob_start();
session_start();
/**
 * @file
 * A script that is called by AJAX functionality from within EXT.
 */

/**
 * Prepares a text string for scrubbing by removing words which the end user
 * requests to be remove from the string. The return from the function
 * will most likely get passed into scrub_text() later in the procedure.
 *
 * @param $text
 *  The text which will have the tags remove from it.
 *
 * @params $tags
 *  An array of elements which will be scrubbed from the text. Do not include
 *  attributes, they will automatically be removed from the text along with
 *  the elements.
 *
 * @return string
 *  A string of text which has the requested tags removed.
 *
 * @see scrub_test()
 */
function remove_stopWords($text, $stopWords) {
	if (empty($text)) {
		print("You must include some text from which to have the text removed.");
		return $text;
	}
	elseif ($stopWords == "") {
		print("Nothing to do, since there are no stopwords.");
		return $text;
	}
	else {
		$allStopWords = explode(", ", $stopWords);
		foreach($allStopWords as &$stopword){
		$stopword = "/\b" . $stopword . "\b/i";
	}
	//$allTextWords = preg_split("/\s/", $text);
	//$newText = "";
	//foreach($allTextWords as $word){

	//}
	$removedString = preg_replace($allStopWords, "", $text);

	return $removedString;

	}
}

function lemmatize($text, $lemmas) {
	if (empty($text)) {
		print("You must include some text from which to have the text lemmatized.");
		return $text;
	}
	elseif ($lemmas == "") {
		print("Nothing to do, since there are no lemmas.");
		return $text;
	}
	else {
	/** There's probably a better way to do this **/

	//$allLemmas = split(",", $lemmas);
	//$allLemmaKEYS = split(",", $lemmaKEYS);

	$allLemmas = array();
	$allLemmaKEYS = array();

	foreach(preg_split("/(\r?\n)/", $lemmas) as $line){
		$lemmaLine = explode(", ", $line);
		array_push($allLemmaKEYS, $lemmaLine[0]);
		array_push($allLemmas, $lemmaLine[1]);
	}

	if (count($allLemmas) != count($allLemmaKEYS)) {
		print("The lemma list and lexeme list need the same number of elements.");
		return $text;
	}

	foreach($allLemmaKEYS as &$nextKEY){
		$nextKEY = "/\b" . $nextKEY . "\b/i";
	}
	/*
	foreach($allLemmaKEYS as $index => $lemmaKEY){
		// Setup key-value pair in lemmaDict
		$lemmaDict[$lemmaKEY] = $allLemmas[$index]
	}
	*/
	$lemmatizedString = preg_replace($allLemmaKEYS, $allLemmas, $text);
	//print("<br />". $text);
	//print("<br /> After lemmatize <br />" . $lemmatizedString . "<br /><br />");

	return $lemmatizedString;
	}
}

function removePunctuation($text) {
	if (empty($text)) {
		print("You must include some text from which to have the punctuation removed.");
		return $text;
	}
	//$text = preg_replace("/[!-/|:-@|[-`|{-~]/", "", $text);
	//$text = preg_replace('/\W/u', " ", $text);
	$text = trim(preg_replace('#[^\p{L}\p{N}]+#u', ' ', $text));
	return $text;
}

function consolidate($text, $consolidations) {
	if (empty($text)) {
		print("You must include some text from which to have the text removed.");
		return $text;
	}
	elseif ($consolidations == "") {
		print("Nothing to do, since there are no consolidations.");
		return $text;
	}
	else {
		$consolidationKeys = array();
		$consolidationValues = array();
		foreach(preg_split("/(\r?\n)/", $consolidations) as $line){
			$consolidationLine = explode(", ", $line);
			array_push($consolidationKeys, $consolidationLine[0]);
			array_push($consolidationValues, $consolidationLine[1]);
		}

		$removedString = str_replace($consolidationKeys, $consolidationValues, $text);

		return $removedString;
	}

}

function formatSpecial($text) {
	if (empty($text)) {
		print("You must include some text from which to have the text removed.");
		return $text;
	}
	else {
		$text = str_replace("&ae;", "æ", $text);
		$text = str_replace("&AE;", "Æ", $text);

		// eth
		$text = str_replace("&d;", "ð", $text);
		$text = str_replace("&D;", "Ð", $text);
		// thorn
		$text = str_replace("&t;", "þ", $text);
		$text = str_replace("&T;", "Þ", $text);

		$text = str_replace("&e;", "e", $text);
		$text = str_replace("&amp;", "&", $text);
		return $text;
	}
}

/**
 * Provides and abstraction to the strip_tags function with some additional
 * case switching for the type of file that is being parsed.
 *
 * @param $string
 *	A string with tags in it (or not) to be parsed and have the tags stripped.
 *
 * @param $tags
 *	The tags which will be passed into remove_elements().
 *
 * @param $type
 *	The type of file from which the tags are being stripped.
 *
 * @return string
 *	A string of scrubbed text. Generally returned via AJAX instead
 *	of a direct call.
 *
 * @see remove_elements()
 *
 */
function scrub_text($string, $formatting, $tags, $punctuation, $removeStopWords, $lemmatize, $consolidate, $lowercase, $special, $stopWords = "", $lemmas = "", $consolidations = "", $type = 'default') {
	switch ($type) {
		case 'default':
			// Make the string variable a string with the requested elements removed.
			utf8_encode($string);
			print("<br /> Before special characters <br />" . substr($string, 0, 1000) . "<br />");
			if ($special == "on") {
				$string = formatSpecial($string);
			}
			print("<br /> After special characters, before strip tags <br />" . substr($string, 0, 1000) . "<br />");
			if ($formatting == "on") {
				print($tags);
				if($tags=="keep"){
					$string = strip_tags($string);
				}
				else {
					$string = preg_replace ( "'<[^>]+>'U", "", $string);
				}
			}
			print("<br /> After strip tags, before remove punctuation <br />" . substr($string, 0, 1000) . "<br />");
			if ($punctuation == "on") {
				$string = removePunctuation($string);
			} 
			print("<br /> After remove punctuation, before remove stopwords <br />" . substr($string, 0, 1000) . "<br />");
			if ($removeStopWords == "on") {
				$string = remove_stopWords($string, $stopWords);
			}
			print("<br /> After remove stopwords, before lemmatize <br />" . substr($string, 0, 1000) . "<br />");
			if ($lemmatize == "on") {
				$string = lemmatize($string, $lemmas);
			}
			print("<br /> After lemmatize, before consolidation <br />" . substr($string, 0, 1000) . "<br />");
			if ($consolidate == "on") {
				$string = consolidate($string, $consolidations);
			}
			print("<br /> After consolidation, before lowercase <br />" . substr($string, 0, 1000) . "<br />");
			if($lowercase == "on") {
				$string = strtolower($string);
			}
			// Clean extra spaces
			$string = preg_replace("/\s\s+/", " ", $string);
			return $string;
			
			break;
		case 'xml':
			// Make the string variable a string with the requested elements removed.
			$string = remove_stopWords($string, $stopWords);
			strip_tags($string);
			break;
		case 'sgml':
			// Make the string variable a string with the requested elements removed.
			$string = remove_stopWords($string, $stopWords);
			strip_tags($string);
			break;
	}
}

$formatting = "";
$punctuation = "";
$removeStopWords = "";
$lemmatize = "";
$consolidate = "";
$lowercase = "";
$special = "";


if(isset($_POST["formatting"]))
	$formatting = $_POST["formatting"];
	$tags = $_POST["tags"];
if(isset($_POST["punctuation"]))
	$punctuation = $_POST["punctuation"];
if(isset($_POST["stopwords"]))
	$removeStopWords = $_POST["stopwords"];
if(isset($_POST["lemmas"]))
	$lemmatize = $_POST["lemmas"];
if(isset($_POST["consolidations"]))
	$consolidate = $_POST["consolidations"];
if(isset($_POST["lowercase"]))
	$lowercase = $_POST["lowercase"];
if(isset($_POST["special"]))
	$special = $_POST["special"];

$file = file_get_contents($_SESSION["file"]);
$stopwords = file_get_contents($_SESSION["stopwords"]);
$lemmas = file_get_contents($_SESSION["lemmas"]);
$consolidations = file_get_contents($_SESSION["consolidations"]);
$_SESSION["scrubbed"] = scrub_text($file, $formatting, $tags, $punctuation, $removeStopWords, $lemmatize, $consolidate, $lowercase, $special, $stopwords, $lemmas, $consolidations);
//header('Location: ' . "display.php");
//die();

ob_flush();
?>