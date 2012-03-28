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
    $line_start_offsets,
    $uid;

  /**
   * @param string $contents
   * @param string $encoding
   **/
  public function __construct($contents, $encoding="utf-8")
  {
    $this->contents = $contents;
    $this->encoding = $encoding;
    $this->length = mb_strlen($contents, $encoding);
    $this->line_start_offsets = self::computeLineStartOffsets($this);
    $this->uid = spl_object_hash($this);
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
  public function getUid()
  {
    return $this->uid;  
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
      $this, 
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

  static public function isLineTerminator($char)
  {
    if($char == "\n" || $char == "\r") return true;
    return preg_match('/^[\x{2028}\x{2029}]$/uS', $char);
  }

  static protected function computeLineStartOffsets(String $source)
  {
    $line_start_offsets = array(0);
    $len = $source->getLength();
    $chars = Utf8::str_split($source->getContents());

    foreach ($chars as $pos => $char) {
      if(self::isLineTerminator($char)) {
        if($pos < $len && $char == "\r" && $chars[$pos+1] == "\n") {
          continue;
        }
        $line_start_offsets[] = $pos + 1;
      }
    }
    //
    $line_start_offsets[] = PHP_INT_MAX;
    return $line_start_offsets;
  }

  static protected function binarySearch($arr, $target)
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
