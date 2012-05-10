<?php

namespace ju1ius\Text\Parser;

use ju1ius\Text;
use ju1ius\Text\Lexer\Token;
use ju1ius\Text\Parser\Exception\ParseException;
use ju1ius\Text\Parser\Exception\UnexpectedTokenException;

/**
 * Packrat parser
 **/
abstract class Packrat extends LLk
{
  /**
   * @const Parsing failed on last attempt
   **/
  const FAILED = -1;

  /**
   * @var \SplStack Stack of index markers into lookahead buffer
   **/
  protected $markers;

  /**
   * @var array
   **/
  protected $memoization;


  public function __construct(Text\Lexer $lexer=null)
  {
    if($lexer) {
      $this->setLexer($lexer);
    }
  }

  protected function reset()
  {
    parent::reset();
    $this->markers = new \SplStack();
    $this->lookaheads = array();
    $this->memoization = array();
  }

  protected function consume($length=1)
  {
    $this->position += $length;
    // have we hit end of buffer when not backtracking? - 1
    if ($this->position >= count($this->lookaheads) && !$this->isSpeculating()) {
      // if so, it's an opportunity to start filling at index 0 again
      $this->position = 0;
      $this->lookaheads = array();
      // clear any rule_memo dictionaries
      $this->clearMemoization();
    }
    // get another to replace consumed token
    $this->synchronizeLookaheadBuffer($length);
  }

  protected function consumeUntil($type)
  {
    while(!$this->lookahead()->isOfType($type)) {
      $this->consume();
    }
  }

  protected function LT($offset=1)
  {
    $this->synchronizeLookaheadBuffer($offset);
    $index = $this->position + $offset - 1;
    return $this->lookaheads[$index];
  }

  /** 
   * Makes sure we have $num_tokens from current position
   * in the lookahead buffer
   **/
  protected function synchronizeLookaheadBuffer($num_tokens)
  {
    $last_index = count($this->lookaheads) - 1;
    $requested_index = $this->position + $num_tokens - 1;
    // out of tokens?
    if ($requested_index > $last_index) {
      // get n tokens
      $n = $requested_index - $last_index;
      // add $n tokens to the lookahead buffer
      for ($i = 1; $i <= $n; $i++) {
        $this->lookaheads[] = $this->lexer->nextToken();
      }
    }
  }

  /**
   * Adds a backtracking marker to the stack
   *
   * @return int The current position of the lookahead buffer
   **/
  protected function mark()
  {
    $this->markers->push($this->position);
    return $this->position;
  }

  /**
   * Pops a marker out of the stack and rewinds the lookahead buffer
   **/
  protected function release()
  {
    $marker = $this->markers->pop();
    $this->seek($marker);
  }

  protected function seek($index)
  {
    $this->position = $index;
  }

  protected function isSpeculating()
  {
    return !$this->markers->isEmpty();
  }

  /** 
   * While backtracking, record partial parsing results.
   * If invoking rule method failed, record that fact.
   * If it succeeded, record the token position we should skip to
   * next time we attempt this rule for this input position.
   *
   * @param string $rule_name
   * @param integer $start_token_index
   * @param boolean $failed
   **/
  protected function memoize($rule_name, $start_token_index, $failed)
  {
    // record token just after last in rule if success
    $stop_token_index = $failed ? self::FAILED : $this->position;
    $this->memoization[$rule_name][$start_token_index] = $stop_token_index;
  }
  protected function clearMemoization($rule_name=null)
  {
    if ($rule_name) {
      $this->memoization[$rule_name] = array();
    } else {
      $this->memoization = array();
    }
  }

  /** 
   * Have we parsed a particular rule before at this input position?
   * If no memoization value, we've never parsed here before.
   * If memoization value is FAILED, we parsed and failed before.
   * If value >= 0, it is an index into the token buffer.  It indicates
   * a previous successful parse.  This method has a side effect:
   * it seeks ahead in the token buffer to avoid reparsing.
   */
  protected function hasAlreadyParsedRule($rule_name)
  {
    if(!isset($this->memoization[$rule_name])) return false;
    if(!isset($this->memoization[$rule_name][$this->position])) return false;
    $index = $this->memoization[$rule_name][$this->position];
    printf(
      "parsed %s before at index %s; skip ahead to token index %s: %s",
      $rule_name, $this->position, $index, $this->lookaheads[$index]
    );
    if ($index === self::FAILED) {
      throw new PreviousParseFailedException();
    }
    // else skip ahead, pretending we parsed this rule ok
    $this->seek($index);
    return true;
  }


}
