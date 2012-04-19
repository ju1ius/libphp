<?php

namespace ju1ius\Text\Parser\Exception;

class UnexpectedTokenException extends ParseException
{
  public function __construct($actual, $expected, $file, $line, $col)
  { 
    $msg = sprintf("Unexpected token %s, expected %s", $actual, $expected);
    parent::__construct($msg, $file, $line, $col); 
  }
}
