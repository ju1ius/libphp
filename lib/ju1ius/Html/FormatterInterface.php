<?php

namespace ju1ius\Html;

interface FormatterInterface
{
  public function format();
  public function loadHtml($html);
  public function loadHtmlFile($file);
  public function setDom(\DOMDocument $dom);
}
