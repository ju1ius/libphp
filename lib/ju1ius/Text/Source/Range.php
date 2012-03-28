<?php

namespace ju1ius\Text\Source;

/**
 * A range of positions in a source string
 */
class Range
{
  protected
    $start,
    $end;

  /**
   * @param Position $start Inclusive start position
   * @param Position $end   Exclusive end position
   **/
  public function __construct(Position $start, Position $end)
  {
    $this->start = $start;
    $this->end = $end;
  }

  public function getStart()
  {
    return $this->start;  
  }
  public function getEnd()
  {
    return $this->end;  
  }
}
