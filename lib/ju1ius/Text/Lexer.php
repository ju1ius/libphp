<?php

namespace ju1ius\Text;

use ju1ius\Text\Lexer\Token;

abstract class Lexer
{
  const T_EOF = -1;
  const T_INVALID = 0;

  protected static $TOKEN_NAMES;

  /**
   * @var Lexer\Token the last seen token
   */
  protected $token;

  /**
   * @var integer Current lexer position in input string
   */
  protected $position = 0;

  /**
   * @var integer Current peek of current lexer position
   */
  protected $peek = 0;

  /**
   * @var array The next token in the input.
   */
  protected $lookahead;

  /**
   * @var string the source encoding
   **/
  protected $encoding;



  protected $state;

  public function __construct(Source\String $source=null)
  {
    $this->state = new Lexer\State();
    $this->getTokenNames();

    if($source) $this->setSource($source);
  }

  abstract public function nextToken();

  /**
   * Sets the input data to be tokenized.
   *
   * @param string $input The input to be tokenized.
   */
  public function setSource(Source\String $source)
  {
    $this->length = $source->getLength();
    $this->text = $source->getContents();
    $this->encoding = $source->getEncoding();
    $this->source = $source;
    $this->reset();
  }
  public function getSource()
  {
    return $this->source;
  }

  public function getEncoding()
  {
    return $this->encoding;  
  }

  /**
   * Resets the lexer.
   */
  public function reset()
  {
    $this->lookahead = null;
    $this->token = null;
    $this->peek = 0;
    $this->position = -1;
    $this->state->reset();
  }

  public function getTokenName($type)
  {
    return static::$TOKEN_NAMES[$type];
  }

  public function getLiteral(Token $token)
  {
    $name = static::$TOKEN_NAMES[$token->getType()];
    return $name . ' at position ' . $token->getPosition() . ": " . $token;
  }

  public function getTokenNames()
  {
    if(self::$TOKEN_NAMES === null) {
      $className = get_class($this);
      $reflClass = new \ReflectionClass($className);
      $constants = $reflClass->getConstants();
      static::$TOKEN_NAMES = array_flip($constants); 
    }
    return static::$TOKEN_NAMES;
  }
}
