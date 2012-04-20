<?php

namespace ju1ius\Text;

abstract class Lexer2 extends Lexer
{
  protected
    $lines,
    $numlines,
    $lineno;

  public function setSource(Source\String $source)
  {
    parent::setSource($source);
    mb_regex_encoding($this->encoding);
    $this->lines = mb_split('(?:\r\n|\n)', $source->getContents());
    $this->numlines = count($this->lines);
    $this->setLine(0);
  }

  public function reset()
  {
    parent::reset();
    $this->setLine(0);
  }

  protected function setLine($num)
  {
    $this->lineno = $num;
    $this->text = $this->lines[$num];
    $this->length = mb_strlen($this->text, $this->encoding);
  }

  protected function nextLine()
  { 
    $this->lineno++;
    $this->setLine($this->lineno);
    $this->position = -1;
    $this->lookahead = null;
  }

}
