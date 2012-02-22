<?php

require_once("../includes/scrub.php");

function scrub_and_return($string, $tags, $lemmas, $type) {
  return scrub_text($string, $tags, $lemmas, $type);
}

print scrub_and_return($_GET['SCRUB_STRING'], $_GET['SCRUB_TAGS'], $_GET['SCRUB_LEMMAS'], $_GET['SCRUB_TYPE']);
