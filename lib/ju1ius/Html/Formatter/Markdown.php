<?php

namespace ju1ius\Html\Formatter;

use ju1ius\Html\FormatterInterface;
use ju1ius\Text;

class Markdown implements FormatterInterface
{
  private
    $dom,
    $xslt,
    $charset,
    $wordwrap;

  /**
   * @param \DOMDocument $dom
   * @param int          $width=75        The maximum line length
   * @param string       $charset="utf-8"
   **/
  public function __construct($width=75, $charset='utf-8')
  {
    $this->wordwrap = $width;
    $this->charset = $charset;
  }

  public function format()
  {
    return Text\MultiByte::wordwrap(
      $this->xslt->transformToXml($this->dom),
      $this->wordwrap, "\n", true, $this->charset
    );
  }

  public function loadHtml($html)
  {
    $this->dom = \DOMDocument::loadHTML($html);
    $this->loadStyleSheets();
  }

  public function loadHtmlFile($file)
  {
    $this->dom = \DOMDocument::loadHTMLFile($file);
    $this->loadStyleSheets();
  }

  public function setDOM(\DOMDocument $dom)
  {
    $this->dom = $dom;
    $this->loadStyleSheets();
  }

  private function loadStyleSheets()
  {
    $this->xslt = new \XSLTProcessor();
    $stylesheet = \DOMDocument::load(__DIR__.'/stylesheets/markdown.xsl');
    $this->xslt->importStylesheet($stylesheet);
  }

}
