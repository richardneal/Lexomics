<?php

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


ob_start();
session_start();
if(isset($_POST)){
	// save the posted data in the session
	$_SESSION["POST"] = $_POST;
}

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
		$allStopWords = array();
		foreach(preg_split("/(\r?\n)/", $stopWords) as $line){
			$eachStopWord = explode(", ", $line);
			foreach($eachStopWord as $stopword){
				array_push($allStopWords, "/\b" . $stopword . "\b/iu");
			}
		}
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

	$allLemmas = array();
	$allLemmaKEYS = array();

	foreach(preg_split("/(\r?\n)/", $lemmas) as $line){
		$lemmaLine = explode("\t", $line);
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

	$lemmatizedString = preg_replace($allLemmaKEYS, $allLemmas, $text);

	return $lemmatizedString;
	}
}

function removePunctuation($text) {
	if (empty($text)) {
		print("You must include some text from which to have the punctuation removed.");
		return $text;
	}
	$text = str_replace("-", "", $text);
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
			$consolidationLine = explode(" \t", $line);
			array_push($consolidationKeys, $consolidationLine[0]);
			array_push($consolidationValues, $consolidationLine[1]);
		}

		$removedString = str_replace($consolidationKeys, $consolidationValues, $text);

		return $removedString;
	}

}

function formatSpecial($text, $formatspecial, $specials, $common, $lowercase) {
	if (empty($text)) {
		print("You must include some text from which to have the text removed.");
		return $text;
	}
	else {
		if ($formatspecial == "on") {
			$allSpecials = array();
			$allSpecialKEYS = array();

			foreach(preg_split("/(\r?\n)/", $specials) as $line){
				$specialline = explode("\t", $line);
				array_push($allSpecialKEYS, $specialline[0]);
				array_push($allSpecials, $specialline[1]);
			}

			foreach($allSpecialKEYS as &$nextKEY){
				$nextKEY = "/\b" . $nextKEY . "\b/i";
			}

			$text = preg_replace($allSpecialKEYS, $allSpecials, $text);
		}
		
		if ($common == "on") {

			$commonchararray = array("&ae;", "&d;", "&t;", "&e;", "&AE;", "&D;", "&T;");
			$commonuniarray = array("æ", "ð", "þ", "e", "Æ", "Ð", "Þ");
			$text = str_replace($commonchararray, $commonuniarray, $text);
		}
		
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
function scrub_text($string, $formatting, $tags, $punctuation, $digits, $removeStopWords, $lemmatize, $consolidate, $formatspecial, $lowercase, $common, $stopWords = "", $lemmas = "", $consolidations = "", $specials = "", $type = 'default') {
	switch ($type) {
		case 'default':
			// Make the string variable a string with the requested elements removed.
			utf8_encode($string);
			$string = html_entity_decode($string, ENT_QUOTES, 'UTF-8');
			print("<br /> Before lowercase <br />" . substr($string, 0, 1000) . "<br />");
			if($lowercase == "on") {
				$string = strtolower($string);
				$caparray = array("Æ", "Ð", "Þ");
				$lowarray = array("æ", "ð", "þ");
				$string = str_replace($caparray, $lowarray, $string);
			}
			print("<br /> After lowercase, before special characters <br />" . substr($string, 0, 1000) . "<br />");
			if ($formatspecial == "on" or $common == "on") {
				$string = formatSpecial($string, $formatspecial, $specials, $common, $lowercase);
			}
			print("<br /> After special characters, before strip tags <br />" . substr($string, 0, 1000) . "<br />");
			if ($formatting == "on") {
				if($tags=="keep"){
					$string = strip_tags($string);
				}
				else {
					$string = preg_replace ( "'<(.*?)>(.*?)</(.*?)>'U", "", $string);
				}
			}
			print("<br /> After strip tags, before remove punctuation <br />" . substr($string, 0, 1000) . "<br />");
			if ($punctuation == "on") {
				$string = removePunctuation($string);
			} 
			print("<br /> After remove punctuation, before remove digits <br />" . substr($string, 0, 1000) . "<br />");
			if ($digits == "on") {
				$string = str_replace(range(0, 9), '', $string);
			} 
			print("<br /> After remove digits, before remove stopwords <br />" . substr($string, 0, 1000) . "<br />");
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
			print("<br /> After consolidation <br />" . substr($string, 0, 1000) . "<br />");

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
$digits = "";
$removeStopWords = "";
$lemmatize = "";
$consolidate = "";
$lowercase = "";
$formatspecial = "";
$common = "";


if(isset($_POST["formattingbox"]))
	$formatting = $_POST["formattingbox"];
	$tags = $_POST["tags"];
if(isset($_POST["punctuationbox"]))
	$punctuation = $_POST["punctuationbox"];
if(isset($_POST["digitsbox"]))
	$digits = $_POST["digitsbox"];
if(isset($_POST["stopwordbox"]))
	$removeStopWords = $_POST["stopwordbox"];
if(isset($_POST["lemmabox"]))
	$lemmatize = $_POST["lemmabox"];
if(isset($_POST["consolidationbox"]))
	$consolidate = $_POST["consolidationbox"];
if(isset($_POST["lowercasebox"]))
	$lowercase = $_POST["lowercasebox"];
if(isset($_POST["specialbox"]))
	$formatspecial = $_POST["specialbox"];
if(isset($_POST["commonbox"]))
	$common = $_POST["commonbox"];

$file = file_get_contents($_SESSION["file"]);
$stopwords = file_get_contents($_SESSION["stopwords"]);
$lemmas = file_get_contents($_SESSION["lemmas"]);
$consolidations = file_get_contents($_SESSION["consolidations"]);
$specials = file_get_contents($_SESSION["specials"]);
$_SESSION["scrubbed"] = scrub_text($file, $formatting, $tags, $punctuation, $digits, $removeStopWords, $lemmatize, $consolidate, $formatspecial, $lowercase, $common, $stopwords, $lemmas, $consolidations, $specials);

header('Location: ' . "display.php");
die();

ob_flush();
?>