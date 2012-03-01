<?php

require_once("../includes/scrub.php");

$_GET['SCRUB'] = "{
    'glossary': {
        'title': 'example glossary',
     }
";

/**
 * This function accepts a serialized JSON array
 */
function scrub_and_return($json) {
  $unserialized_json = unserialize($json);
  $decoded_json = json_decode($unserialized_json);

  $decoded_json['string'];
  $decoded_json['tags'];
  $decoded_json['lemmas'];
  $decoded_json['type'];

  foreach ($decoded_json['lemmas'] as $lexome => $lemma) {
    // bla
  }

  /** Do something here **/
  $scrubbed = scrub_text($string, $tags, $lemmas, $type);

  // $return is the result of the scrubbed text
  return $json;
}

print scrub_and_return($_GET['SCRUB']);
