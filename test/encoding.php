<?php

require_once 'Benchmark/Timer.php';

$timer = new Benchmark_Timer();
$nb_iterations = 10000;
$timer->start();

for($i=0; $i < $nb_iterations; $i++)
{
  $result = getCompatibleEncodings();
}
$timer->setMarker('getCompatibleEncodings');
var_dump($result);

for($i=0; $i < $nb_iterations; $i++)
{
  $result = getCompatibleEncodings_2();
}
$timer->setMarker('getCompatibleEncodings_2');


echo $timer->getOutput();



function getCompatibleEncodings()
{
  $compatible_encodings = array();

  $ascii_chars = '';
  foreach(range(0,127) as $ord) {
    $ascii_chars .= chr($ord);
  }

  foreach (mb_list_encodings() as $encoding) {
    $encoded = mb_convert_encoding($ascii_chars, $encoding);
    if($encoded === $ascii_chars) {
      $compatible_encodings[] = strtolower($encoding);
      array_splice($compatible_encodings, -1, 0, array_map('strtolower', mb_encoding_aliases($encoding)));
    }
  }
  return $compatible_encodings;
}
function getCompatibleEncodings_2()
{
  $compatible_encodings = array();

  $ascii_chars = '';
  foreach(range(0,127) as $ord) {
    $ascii_chars .= chr($ord);
  }

  foreach(mb_list_encodings() as $encoding) {
    $encoded = mb_convert_encoding($ascii_chars, $encoding);
    if($encoded === $ascii_chars) {
      $compatible_encodings[] = strtolower($encoding);
      foreach(mb_encoding_aliases($encoding) as $alias) {
        $compatible_encodings[] = strtolower($alias);
      }
    }
  }
  return $compatible_encodings;
}
