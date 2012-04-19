<?php

namespace ju1ius\Text\Parser;

use ju1ius\Text;

abstract class LLk extends Text\Parser
{
  /**
   * @var integer size of the lookahead buffer
   **/
  protected $K;

  /**
   * @var array lookahead buffer 
   **/
  protected $lookaheads;


  public function __construct(Text\Lexer $lexer=null, $k=2)
  {
    $this->setLexer($lexer);
    $this->K = $k;
  }

  public function setLexer(Text\Lexer $lexer)
  {
    $this->lexer = $lexer;
  }

  protected function reset()
  {
    parent::reset();
    $this->lookaheads = new \SplFixedArray($this->K);
    for ($i = 1; $i <= $this->K; $i++) $this->consume();
  }

  protected function consume()
  {
    // fill next position with token
    $this->lookaheads[$this->position] = $this->lexer->nextToken();
    // increment circular index
    $this->position = ($this->position + 1) % $this->K;
  }

  protected function current()
  {
    return $this->lookaheads[$this->position];
  }

  protected function LA($offset=1)
  {
    return $this->LT($offset)->getType();
  }

  protected function LT($offset=1)
  {
    // circular fetch
    return $this->lookaheads[($this->position + $offset - 1) % $this->K];
  }
}
