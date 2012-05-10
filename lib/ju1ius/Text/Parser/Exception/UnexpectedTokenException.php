<?php

namespace ju1ius\Text\Parser\Exception;

class UnexpectedTokenException extends ParseException
{
  protected
    $token;

  public function __construct($actual, $expected, $file, $line, $col)
  {
    $this->token = $actual; 
    $msg = sprintf("Unexpected token %s, expected %s", $actual, $expected);
    parent::__construct($msg, $file, $line, $col); 
  }

  public function getToken()
  {
    return $this->token;
  }
}
