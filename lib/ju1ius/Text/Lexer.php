<?php
/* vim: set fdm=marker : */

namespace ju1ius\Text;

use ju1ius\Text\Lexer\Token;

abstract class Lexer
{
  const T_EOL = -2;
  const T_EOF = -1;
  const T_INVALID = 0;

  protected static $TOKEN_NAMES;

  /**
   * @var Source\String the source object
   **/
  protected $source;

  /**
   * @var \SplFixedArray The lines of the source object
   **/
  protected $lines;

  /**
   * @var integer The number of lines in the source
   **/
  protected $numlines;

  /**
   * @var integer The current source line
   **/
  protected $lineno;

  /**
   * @var string the current source line's text
   **/
  protected $text;

  /**
   * @var integer the current source line's length
   **/
  protected $length;

  /**
   * @var integer Current lexer position in input string (in number of characters)
   */
  protected $position = 0;

  /**
   * @var integer Current lexer position in input string (in number of bytes)
   */
  protected $bytepos = 0;

  /**
   * @var array The next character in the input.
   */
  protected $lookahead;

  /**
   * @var string the source encoding
   **/
  protected $encoding;

  /**
   * @var Lexer\State state of the Lexer
   **/
  protected $state;

  public function __construct(Source\String $source=null)
  {/*{{{*/
    $this->state = new Lexer\State();
    $this->getTokenNames();
    if($source) $this->setSource($source);
  }/*}}}*/

  abstract public function nextToken();

  /**
   * Sets the input data to be tokenized.
   *
   * @param string $input The input to be tokenized.
   */
  public function setSource(Source\String $source)
  {/*{{{*/
    $this->source = $source;
    $this->encoding = $source->getEncoding();
    $this->is_ascii = Encoding::isSameEncoding($this->encoding, 'ascii');
    mb_regex_encoding($this->encoding);
    $this->lines = $source->getLines();
    $this->numlines = $source->getNumLines();
    $this->reset();
  }/*}}}*/

  public function getSource()
  {/*{{{*/
    return $this->source;
  }/*}}}*/

  public function getEncoding()
  {/*{{{*/
    return $this->encoding;  
  }/*}}}*/

  /**
   * Resets the lexer.
   */
  public function reset()
  {/*{{{*/
    $this->setLine(0);
    $this->state->reset();
  }/*}}}*/

  public function setLine($num)
  {/*{{{*/
    if ($num > $this->numlines-1) return;
    $this->lineno = $num;
    $this->text = $this->lines[$num];
    $this->length = $this->is_ascii ? strlen($this->text) : mb_strlen($this->text, $this->encoding);
    $this->position = -1;
    $this->bytepos = -1;
    $this->lookahead = null;
  }/*}}}*/

  public function nextLine()
  {/*{{{*/
    $this->setLine($this->lineno + 1);
  }/*}}}*/

  public function getTokenName($type)
  {/*{{{*/
    return static::$TOKEN_NAMES[$type];
  }/*}}}*/

  public function getLiteral(Token $token)
  {/*{{{*/
    $name = static::$TOKEN_NAMES[$token->type];
    return sprintf(
      "%s (%s) on line %s, column %s.",
      $name, $token, $token->line, $token->column
    );
  }/*}}}*/

  public function getTokenNames()
  {/*{{{*/
    if(self::$TOKEN_NAMES === null) {
      $className = get_class($this);
      $reflClass = new \ReflectionClass($className);
      $constants = $reflClass->getConstants();
      static::$TOKEN_NAMES = array_flip($constants); 
    }
    return static::$TOKEN_NAMES;
  }/*}}}*/

  protected function consumeCharacters($length=1)
  {/*{{{*/
    $this->position += $length;
    $this->bytepos += $length;
    if($this->position > $this->length) {
      $this->lookahead = null;
    } else {
      $this->lookahead = $this->is_ascii
        ? substr($this->text, $this->position, 1)
        : mb_substr($this->text, $this->position, 1, $this->encoding);
    }
  }/*}}}*/

  protected function consume($length=1)
  {/*{{{*/
    $this->position += $length;
    if($this->position > $this->length) {
      $this->lookahead = null;
    } else {
      $this->lookahead = substr($this->text, $this->position, 1);
    }
  }/*}}}*/

  protected function consumeString($str)
  {/*{{{*/
    if($this->is_ascii) {
      $len = strlen($str);
      $this->position += $len;
      $this->bytepos += $len;
    } else {
      $this->position += mb_strlen($str, $this->encoding);
      $this->bytepos += strlen($str);
    }

    if($this->position > $this->length) {
      $this->lookahead = null;
    } else {
      $this->lookahead = $this->is_ascii
        ? substr($this->text, $this->position, 1)
        : mb_substr($this->text, $this->position, 1, $this->encoding);
    }
  }/*}}}*/

  protected function comes($str)
  {/*{{{*/
    if($this->position > $this->length) return false;
    if($this->is_ascii) {
      $length = strlen($str);
      return substr($this->text, $this->position, $length) === $str;
    } else {
      $length = mb_strlen($str, $this->encoding);
      return mb_substr($this->text, $this->position, $length, $this->encoding) === $str;
    }
  }/*}}}*/

  protected function peek($length=1, $offset=0)
  {/*{{{*/
    return $this->is_ascii
      ? substr($this->text, $this->position + $offset + 1, $length)
      : mb_substr($this->text, $this->position + $offset + 1, $length, $this->encoding);
  }/*}}}*/

  protected function comesExpression($pattern, $options = 'msi')
  {/*{{{*/
    if($this->position > $this->length) return false;
    //return preg_match('/\G'.$pattern.'/iu', $this->text, $matches, 0, $this->bytepos);
    mb_ereg_search_init($this->text, '\G'.$pattern, $options);
    mb_ereg_search_setpos($this->bytepos);
    return mb_ereg_search();
  }/*}}}*/

  protected function match($pattern, $position=null, $options='msi')
  {/*{{{*/
    if(null === $position) $position = $this->bytepos;
    if($this->position >= $this->length) return false;
    mb_ereg_search_init($this->text, '\G'.$pattern, $options);
    mb_ereg_search_setpos($position);
    return mb_ereg_search_regs();
  }/*}}}*/

}
