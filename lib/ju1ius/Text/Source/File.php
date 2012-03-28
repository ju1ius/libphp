<?php

namespace ju1ius\Text\Source;

/**
 * A source file
 */
class File extends String
{
  protected
    $url;

  /**
   * @param string $url
   * @param string $contents
   * @param string $encoding
   **/
  public function __construct($url, $contents, $encoding="utf-8")
  {
    $this->url = $url;
    parent::__construct($contents, $encoding);
  }

  public function getUrl()
  {
    return $this->url; 
  }

}
