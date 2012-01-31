<?php

$lemmas = array(
  'kings' => 'king',
  'kyngs' => 'king',
  'queens' => 'queen',
);

$text = 'Book queens kyngs school college fun computers kings';

function lemma_find($lemmas, $text) {
  foreach ($lemmas as $key => $lemma) {
    $text = str_replace($key, $lemma, $text);
  }
  return $text;
}

print lemma_find($lemmas, $text);
