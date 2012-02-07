<?php


$lemmas = array(
  'kings' => 'king',
  'kyngs' => 'king',
  'queens' => 'queen',
);

$text = 'Book queens kyngs school college fun computers kings';

function lemma_replace($lemmas, $text) {
  if (isset($lemmas) && isset($text)) {
    foreach ($lemmas as $key => $lemma) {
      $text = str_replace($key, $lemma, $text);
    }
  }
  return $text;
}

