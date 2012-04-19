<?php

require_once __DIR__.'/../ju1ius_TestCase.php';

class EncodingTest extends ju1ius_TestCase
{
  /**
   * @dataProvider testIsAsciiCompatibleProvider
   **/
  public function testIsAsciiCompatible($encoding, $expected)
  {
    $this->assertEquals($expected, ju1ius\Text\Encoding::isAsciiCompatible($encoding));
  }
  public function testIsAsciiCompatibleProvider()
  {
    return array(
      array('ascii', true),
      array('UTF8', true),
      array('UTF-16', false),
      array('Latin1', true),
      array('Shift-JIS', true),
    );
  }

  /**
   * @dataProvider testIsSameEncodingProvider
   **/
  public function testIsSameEncoding($encoding_1, $encoding_2, $expected)
  {
    $this->assertEquals($expected, ju1ius\Text\Encoding::isSameEncoding($encoding_1, $encoding_2));
  }
  public function testIsSameEncodingProvider()
  {
    return array(
      array('ascii', 'US-ASCII', true),
      array('UTF8', 'utf-8', true),
      array('UTF-16', 'big5', false),
      array('Latin1', 'iso-8859-1', true),
      array('Shift-JS', 'foobar', false),
    );
  }
}
