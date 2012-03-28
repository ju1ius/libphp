<?php

namespace ju1ius\Text\Source;

/**
 * A position in a source string - includes offset, line and column.
 */
class Position
{
  protected
    $source,
    $offset,
    $line,
    $column;

  /**
   * @param String $source
   * @param int  $offset
   * @param int  $line
   * @param int  $column
   **/
  public function __construct(String $source, $offset, $line, $column)
  {
    $this->source = $source;
    $this->offset = $offset;
    $this->line = $line;
    $this->column = $column;
  }

  public function getSource()
  {
    return $this->source;  
  }
  public function getOffset()
  {
    return $this->offset;  
  }
  public function getLine()
  {
    return $this->line;  
  }
  public function getColumn()
  {
    return $this->column;  
  }
}
