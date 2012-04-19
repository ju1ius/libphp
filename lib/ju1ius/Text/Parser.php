<?php

namespace ju1ius\Text;

use ju1ius\Text;
use ju1ius\Text\Lexer\Token;
use ju1ius\Text\Parser\Exception\ParseException;
use ju1ius\Text\Parser\Exception\UnexpectedTokenException;

/**
 * 
 */
abstract class Parser
{
  /**
   * @var ju1ius\Text\Lexer
   **/
  protected $lexer;

  /**
   * @var array lookahead buffer 
   **/
  protected $lookaheads;

  /**
   * @var integer The current position in the lookahead buffer
   **/
  protected $position;

  public function setLexer(Text\Lexer $lexer)
  {
    $this->lexer = $lexer;
  }

  abstract protected function consume();
  abstract protected function current();
  abstract protected function LA($offset=1);
  abstract protected function LT($offset=1);

  protected function reset()
  {
    $this->position = 0;
  }

  protected function match($type)
  {
    $this->ensure($type);
    $this->consume();
  }

  protected function ensure($type)
  {
    $token = $this->LT();

    $match = false;
    if(is_array($type)) {
      $match = $token->isOneOfTypes($type);
    } else {
      $match = $token->isOfType($type);
    }

    if (!$match) {
      $this->_unexpectedToken($token, $type);
    }

  }

  protected function _parseException($msg, Token $token)
  {
    $source = $this->lexer->getSource();
    $file = $source instanceof Source\File ? $source->getUrl() : 'internal_string';
    $position = $token->getPosition();
    $line = $source->getLine($position);
    $column = $source->getColumn($position, $line);

    throw new ParseException($msg, $file, $line, $column);
  }

  protected function _unexpectedToken(Token $actual, $expected)
  {
    $source = $this->lexer->getSource();
    $file = $source instanceof Source\File ? $source->getUrl() : 'internal_string';
    $position = $actual->getPosition();
    $line = $source->getLine($position);
    $column = $source->getColumn($position, $line);
    
    $name = $this->lexer->getTokenName($actual->getType());
    $name .= ' ('.print_r($actual->getValue(), true).')';
    if (is_array($expected)) {
      $types = array();
      foreach ($expected as $type) {
        $types[] = $this->lexer->getTokenName($type);
      }
      $expected = implode(', ', $types);
    } else {
      $expected = $this->lexer->getTokenName($expected);
    }
    throw new UnexpectedTokenException($name, $expected, $file, $line, $column);
  }

}
