<?php

namespace ju1ius\Text\Source;

/**
 * A source string
 */
class String
{
  private
    $contents,
    $encoding,
    $length,
    $lines,
    $numlines,
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
    $this->lines = self::splitLines($contents, $encoding);
    $this->numlines = count($this->lines);
    // FIXME: this is no longer used and should be removed
    $this->line_start_offsets = $this->computeLineStartOffsets();
  }

  /**
   * Returns the source string
   *
   * @return string
   **/
  public function getContents()
  {
    return $this->contents;  
  }
  /**
   * Returns the source lines
   *
   * @return \SplFixedArray
   **/
  public function getLines()
  {
    return $this->lines;
  }
  /**
   * Returns the line at given index.
   * The index is zero-based, so the first line is at index 0.
   *
   * @return string
   **/
  public function getLine($lineno)
  {
    return $this->lines[$lineno];
  }
  /**
   * Returns the number of lines in the source string.
   *
   * @return integer
   **/
  public function getNumLines()
  {
    return $this->numlines;
  }
  public function __toString()
  {
    return $this->contents;
  }
  /**
   * Returns the source encoding
   *
   * @return string
   **/
  public function getEncoding()
  {
    return $this->encoding;
  }
  /**
   * Returns the source length (in characters).
   *
   * @return integer
   **/
  public function getLength()
  {
    return $this->length;  
  }

  private static function splitLines($string, $encoding)
  {
    mb_regex_encoding($encoding);
    return \SplFixedArray::fromArray(mb_split('\r\n|\n', $string));
  }

  /**
   * Everything below belongs to the old implementation and is no longer used.
   * It's left here just in case...
   **/

  /**
   * Returns a Source\Position object from an offset in the string
   *
   * @deprecated
   * @param int $offset
   *
   * @return Position
   **/
  public function getSourcePosition($offset)
  {
    $line = $this->getLineAtPosition($offset);
    $column = $this->getColumnAtPosition($offset, $line); 
    return new Position(
      $offset, $line,
      $column
    );
  }

  /**
   * Returns the line number of a position in the string
   *
   * @deprecated
   * @param int $offset
   *
   * @return int
   **/
  public function getLineAtPosition($offset)
  {
    $index = self::binarySearch($this->line_start_offsets, $offset);
    // start of line
    if($index >= 0) return $index;
    return -$index - 2;
  }

  /**
   * Returns the column number of a position in the string
   *
   * @deprecated
   * @param int $offset
   * @param int $line
   *
   * @return int
   **/
  public function getColumnAtPosition($offset, $line=null)
  {
    if($line === null) $line = $this->getLineAtPosition($offset);
    return $offset - $this->getOffsetOfLine($line);
  }

  /**
   * Returns a Source\Range object from two positions in the source
   *
   * @deprecated
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
   * Returns the position of the line in the source
   *
   * @deprecated
   * @param int $line
   *
   * @return int
   **/
  public function getOffsetOfLine($line)
  {
    return $this->line_start_offsets[$line];
  }

  private function computeLineStartOffsets()
  {
    $line_start_offsets = array(0);
    $offset = 0;
    $encoding = $this->getEncoding();
    foreach($this->lines as $line) {
      $l = mb_strlen($line, $encoding);
      $offset += $l + 1;
      $line_start_offsets[] = $offset;
    }
    return $line_start_offsets;
  }

  private static function binarySearch($arr, $target)
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
