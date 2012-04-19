<?php

require_once 'Benchmark/Timer.php';
require_once 'autoload.php';

$timer = new Benchmark_Timer();
$nb_iterations = 1000000;
$timer->start();

$str = "Sàéœpïô¬";
$pad = "ð";

for ($i = 0; $i < $nb_iterations; $i++) {
  $result = ju1ius\Text\MultiByte::str_pad($str, 60, $pad, STR_PAD_LEFT);
}
var_dump($result);
$timer->setMarker('mb_strpad');


echo $timer->getOutput();
 

