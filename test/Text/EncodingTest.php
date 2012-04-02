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
      array('Shift-JS', false),
    );
  }
}
