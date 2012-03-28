<?php
require_once __DIR__.'/../autoload.php';

$data = array(
  'foo' => array(
    'bar' => 'baz',
    'bling' => array('blong')
  ),
  'humpty' => 'dumpty'
);

$params = new ju1ius\Collections\ParameterBag($data);
$params->dump();

echo "\n===================\n";
$params->set('foo.bar', array('baz'=>'bing'));
$params->set('foo.bling.0', 'blang');
$params->set('foo.bling.1', 'blung');
$params->dump();

echo "\n===================\n";
$params->set('foo.bling.*', 'w00t');
$params->dump();
