<?php

require_once __DIR__.'/../../ju1ius_TestCase.php';

class StringTest extends ju1ius_TestCase
{
  private static
    $test_input_1 = <<<EOS
Some text
With
fünnŷ chàrâctèrs
and lïne breaks
EOS;

  /**
   * @dataProvider testGetLineProvider
   **/
  public function testGetLine($offset, $expected_line)
  {
    $source = new ju1ius\Text\Source\String(self::$test_input_1);
    $line = $source->getLine($offset);
    $this->assertEquals($expected_line, $line);
  }
  public function testGetLineProvider()
  {
    return array(
      array(0, 0),
      array(9, 0),
      array(10, 1),
      array(32, 3)
    );
  }

  /**
   * @depends testGetLine
   * @dataProvider testGetColumnProvider
   **/
  public function testGetColumn($offset, $expected_col)
  {
    $source = new ju1ius\Text\Source\String(self::$test_input_1);
    $col = $source->getColumn($offset);
    $this->assertEquals($expected_col, $col);
  }
  public function testGetColumnProvider()
  {
    return array(
      array(0, 0),
      array(8, 8),
      array(10, 0),
      array(32, 0)
    );
  }
}
