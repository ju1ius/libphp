<?php
namespace ju1ius\Text\Parser\Exception;

/**
 * @package Text\Parser
 * @subpackage Exception
 **/
class ParseException extends \RuntimeException
{

  public function __construct($msg, $file, $line, $column)
  {
    $msg = sprintf(
      "%s in %s on line %s, column %s",
      $msg, $file, $line, $column
    );
    parent::__construct($msg);
  }

}
