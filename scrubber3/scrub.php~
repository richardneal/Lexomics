<?php

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
  	$allStopWords = split(",", $stopWords);
  	foreach($allStopWords as &$stopword){
  		$stopword = "/\b" . $stopword . "\b/i";
  	}
    //$allTextWords = preg_split("/\s/", $text);
    //$newText = "";
    //foreach($allTextWords as $word){

    //}
    $removedString = preg_replace($allStopWords, "", $text);
    //print("\n". $text);
    //print("\n After stopwords \n" . $removedString . "\n\n");

    // Removes extra spaces
    $cleanedString = preg_replace("/\s\s+/", " ", $removedString);
    return $cleanedString;

  }
}

function lemmatize($text, $lemmaKEYS, $lemmas) {
  if (empty($text)) {
    print("You must include some text from which to have the text lemmatized.");
    return $text;
  }
  elseif ($lemmas == "") {
    print("Nothing to do, since there are no lemmas.");
    return $text;
  }
  elseif ($lemmaKEYS == "") {
    print("Nothing to do, since there are no lexemes.");
    return $text;
  }
  else {
    /** There's probably a better way to do this **/

    $allLemmas = split(",", $lemmas);
    $allLemmaKEYS = split(",", $lemmaKEYS);
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
    //print("\n". $text);
    //print("\n After lemmatize \n" . $lemmatizedString . "\n\n");

    return $lemmatizedString;
  }
}

function removePunctuation($text) {
  if (empty($text)) {
    print("You must include some text from which to have the punctuation removed.");
    return $text;
  }
  //regex is broken
  //$text = preg_replace("/[!-/|:-@|[-`|{-~]/", "", $text);
  //$text = preg_replace('/\W/u', " ", $text);
  $text = trim(preg_replace('#[^\p{L}\p{N}]+#u', ' ', $text));
  return $text;
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
function scrub_text($string, $stopWords = "", $lemmaKeys = "", $lemmas = "", $type = 'default') {
	switch ($type) {
		case 'default':
			// Make the string variable a string with the requested elements removed.
      utf8_encode($string);
      print("\n\n Before strip tags \n" . $string);
      $string = strip_tags($string);
      print("\n After strip tags, before remove punctuation \n" . $string);   
      $string = removePunctuation($string);
      print("\n After remove punctuation, before remove stopwords \n" . $string);
	$string = remove_stopWords($string, $stopWords);
      print("\n After remove stopwords, before lemmatize \n" . $string);
      $string = lemmatize($string, $lemmaKeys, $lemmas);
      print("\n After lemmatize \n" . $string . "\n\n\n");
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

// Define the POST values into regular instance variables.
//$string = $_POST['string'];
//$type = $_POST['type'];
/*
 $string = "<b>Mary</b> had The <em>little</em> Margaret a their lamb They're. %C5%A0";
 $tags = "";
 $stopWords = "a, the";
 $lemmaKEYS = "Mary,little";
 $lemmas = "Margaret,tiny";
if (isset($string)) {
	return scrub_text($string, $stopWords, $lemmaKEYS, $lemmas);
}
*/

$file = file_get_contents($_SESSION["file"]);
$stopwords = file_get_contents($_SESSION["stopwords"]);
$lemmas = file_get_contents($_SESSION["stopwords"]);
$_SESSION["scrubbed"] = scrub_text($file, $stopwords);
header('Location: ' . "display.php");
die();

?>
