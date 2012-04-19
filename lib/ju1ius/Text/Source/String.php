<?php

namespace ju1ius\Text\Source;

use ju1ius\Text\Utf8;

/**
 * A source string
 */
class String
{
  protected
    $contents,
    $encoding,
    $length,
    $line_start_offsets;

  /**
   * @param string $contents
   * @param string $encoding
   **/
  public function __construct($contents, $encoding="utf-8")
  {
    $this->contents = $contents;
    $this->encoding = $encoding;
    $this->length = mb_strlen($contents, $encoding);
    $this->line_start_offsets = $this->computeLineStartOffsets();
  }

  public function getContents()
  {
    return $this->contents;  
  }
  public function __toString()
  {
    return $this->contents;
  }
  public function getEncoding()
  {
    return $this->encoding;  
  }
  public function getLength()
  {
    return $this->length;  
  }


  /**
   * @param int $offset
   *
   * @return Position
   **/
  public function getSourcePosition($offset)
  {
    $line = $this->getLine($offset);
    $column = $this->getColumn($offset, $line); 
    return new Position(
      $offset, $line,
      $column
    );
  }
  /**
   * @param int $offset
   *
   * @return int
   **/
  public function getLine($offset)
  {
    $index = self::binarySearch($this->line_start_offsets, $offset);
    // start of line
    if($index >= 0) return $index;
    return -$index - 2;
  }
  /**
   * @param int $offset
   * @param int $line
   *
   * @return int
   **/
  public function getColumn($offset, $line=null)
  {
    if($line === null) $line = $this->getLine($offset);
    return $offset - $this->getOffsetOfLine($line);
  }
  /**
   * @param int $start
   * @param int $end
   *
   * @return Range
   **/
  public function getSourceRange($start, $end)
  {
    return new Range(
      $this->getSourcePosition($start),
      $this->getSourcePosition($end)
    );
  }
  /**
   * @param int $line
   *
   * @return int
   **/
  public function getOffsetOfLine($line)
  {
    return $this->line_start_offsets[$line];
  }

  protected function computeLineStartOffsets()
  {
    $line_start_offsets = array(0);
    $offset = 0;
    $encoding = $this->getEncoding();
    mb_regex_encoding($encoding);
    $lines = mb_split('\r\n|\n', $this->getContents());
    foreach($lines as $line) {
      $l = mb_strlen($line, $encoding);
      $offset += $l + 1;
      $line_start_offsets[] = $offset;
    }
    return $line_start_offsets;
  }

  protected static function binarySearch($arr, $target)
  {
    $left = 0;
    $right = count($arr) - 1;
    while($left <= $right) {
      $mid = ($left + $right) >> 1;
      if($target > $arr[$mid]) {
        $left = $mid + 1;
      } else if($target < $arr[$mid]) {
        $right = $mid - 1;
      } else {
        return $mid;
      }
    }
    // Not found, $left is the insertion point
    return -($left + 1);
  }

}
