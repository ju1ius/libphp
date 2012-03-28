<?php

namespace ju1ius\Html\Formatter;

use ju1ius\Html\FormatterInterface;
use ju1ius\Text;

use Zend\Text\Table\Table as Zend_Table;
use Zend\Text\Table\Row as Zend_Row;
use Zend\Text\Table\Column as Zend_Column;

/**
 *
 * Formats HTML to plain text
 *
 **/
class PlainText implements FormatterInterface
{
  protected
    $original_dom,
    $dom,
    $xpath,
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
		$this->original_dom = new \DOMDocument();
		$this->original_dom->preserveWhiteSpace = false;
  }

  public function format()
  {
    $this->dom = $this->xslt->transformToDoc($this->original_dom);
		$this->dom->normalize();
		$this->dom->formatOutput = true;
    $this->xpath = new \DOMXPath($this->dom);
    $this->xpath->registerNamespace(
      'txt',
      "http://github.com/ju1ius/html-email-preflight"
    );
    $text = $this->parseDocument();
		return $text;
  }

  public function loadHtml($html)
  {
		$this->original_dom->loadHTML($html);
		$this->original_dom->normalize();
    $this->loadStyleSheets();
  }

  public function loadHtmlFile($file)
  {
    $this->original_dom->loadHTMLFile($file);
		$this->original_dom->normalize();
    $this->loadStyleSheets();
  }

  public function setDom(\DOMDocument $dom)
  {
    $this->original_dom = $dom;
		$this->original_dom->normalize();
    $this->loadStyleSheets();
  }

  private function loadStyleSheets()
  {
    $this->xslt = new \XSLTProcessor();
    $stylesheet = \DOMDocument::load(__DIR__.'/stylesheets/plaintext.xsl');
    $this->xslt->importStylesheet($stylesheet);
  }

  private function parseDocument()
  {
    $context = array(
      'depth'       => 0,
      'indent'      => "",
      'bullet'      => "",
      'line_prefix' => "",
      'line_suffix' => "",
      'raw'         => false
    );
    $output = "";
    $root = $this->xpath->query('/txt:document')->item(0);
    foreach($root->childNodes as $child) {
      $output .= $this->parseNode($child, $context);
    }
    return $output;
  }

  private function parseNode(\DOMNode $node, $context)
  {
    $type = $node->nodeType;
    if($type == XML_ELEMENT_NODE) {
      if ($node->tagName == 'txt:block') {
        return $this->parseBlock($node, $context);
			} else if($node->tagName == 'txt:separator') {
				return $this->parseSeparator($node, $context);
			} else if($node->tagName == 'txt:table') {
        return $this->parseTable($node, $context);
      }
    } else if ($type == XML_TEXT_NODE) {
      return $this->parseTextNode($node, $context);
    }
  }

	private function parseSeparator(\DOMElement $node, $context)
	{
		$char = $node->getAttribute('char');
		$width = $this->wordwrap - mb_strlen($context['indent'], $this->charset);
		$text = $context['indent'] . str_repeat($char, $width);
		return "\n\n" . $text . "\n\n";
	}

  private function parseBlock(\DOMElement $node, $context)
	{
		// ---------- Set up the context to pass to children
    $text = "";
    $context['line_prefix'] = '';
    $context['line_suffix'] = '';
		
    if ($node->hasAttribute('indent')) {
      $context['indent'] .= $node->getAttribute('indent');
      $context['depth']++; 
    }

    if ($node->hasAttribute('bullet')) {
      $context['bullet'] = $node->getAttribute('bullet');
      // If we have a bullet, add a small padding
      // to following lines
      $context['line_prefix'] = '  ';
    }

    if ($node->hasAttribute('border-left')) {
      $context['indent'] .= $node->getAttribute('border-left');
    }
    if ($node->hasAttribute('border-right')) {
      // FIXME: Not yet implemented
      $context['line_suffix'] .= $node->getAttribute('border-right');
    }

    if($node->hasAttribute('raw')) {
      // FIXME: should this really apply to all descendants ?
      $context['raw'] = true;
		}

		// ---------- Parse children

    if($node->hasChildNodes()) {

      $children = $node->childNodes;
      $len = $children->length;
      $first = $children->item(0);

      if($first->nodeType == XML_ELEMENT_NODE) {
        if($first->hasAttribute('indent')) {
					// we need to apply ident ourselves before going deeper
					$text .= $context['indent'] . $context['bullet'];
					// if we are an indenting block, add a linebreak,
					// since following nodes could end up on the same line
					if($node->hasAttribute('indent')) $text.="\n";
        }
        $text .= $this->parseNode($first, $context);

      } else if ($first->nodeType == XML_TEXT_NODE) {

        $text .= $this->parseTextNode($first, $context);

      }
      // Apply bullets only to first child
      $context['bullet'] = "";

      for($i = 1; $i < $len; $i++) {
        $child = $children->item($i);
        $text .= $this->parseNode($child, $context);
      }

    } else {
      // empty block
      $text = $context['indent'] . $context['bullet']
            . $context['line_prefix'] . $context['line_suffix']
            . "\n";
		}

		// ---------- Apply box-model

    if($node->hasAttribute('box')) {
      // boxify
      $text = $this->boxify($text, $node->getAttribute('box'));
    } else {
      // borders
			$text = $this->applyBorders($node, $text, $context);
    }
		$text = $this->applyLineBreaks($node, $text);

    return $text;
  }

  private function parseTable(\DOMElement $node, $context)
  {
    $trs = $this->xpath->query('txt:tr', $node);
    $num_cols = 0;
    $rows = array();

    foreach ($trs as $tr) {

      $row = new Zend_Row();
      $tds = $this->xpath->query('txt:td', $tr);
      $len = $tds->length;
      if($len > $num_cols) $num_cols = $len;

      foreach($tds as $td) {
        $td_text = "";
        foreach($td->childNodes as $child) {
          $td_text .= $this->parseNode($child, $context);
        }
        $column = new Zend_Column($td_text);
        if($td->hasAttribute('colspan')) {
          $column->setColSpan((int)$td->getAttribute('colspan'));
        }
        $row->appendColumn($column);
      }

      $rows[] = $row;

    }
    if($num_cols) {
      $col_width = (int) floor($this->wordwrap / $num_cols);
      $table = new Zend_Table(array(
        'columnWidths' => array_fill(0, $num_cols, $col_width),
        'decorator'    => 'ascii',
        'padding'      => 1
      ));
      foreach ($rows as $row) {
        $table->appendRow($row);
      }
      return $table->render();
    }
  }

  private function parseTextNode(\DOMText $node, $context)
  {
    $text = "";
    $raw = $context['raw'];
    $indent = $context['indent'];
    $suffix = $context['line_suffix'];
    $value = $raw ?
      $node->nodeValue
      : Text\Utf8::normalizeWhitespace($node->nodeValue);

    if($context['bullet']) {
      // bullet has been set by the parent,
      // it means we are the first child node
      // We have to apply the bullet to the first line of text
      $prefix = $indent . $context['bullet'];
      if(empty($value)) {
        return $prefix . $suffix . "\n";
      }
      $wordwrap = $this->wordwrap - mb_strlen($prefix, $this->charset);
      //$first_line = $raw ? array_shift($lines) : trim(array_shift($lines));
      $first_linebreak = mb_strpos($value, "\n", 0, $this->charset);
      if(false === $first_linebreak) {
        // no line break, only one line
        $first_line = $value;
      } else {
        // ----- get the first line
        $first_line = mb_substr($value, 0, $first_linebreak, $this->charset);
      }
      // ----- wrap it
      $wrapped = Text\MultiByte::wordwrap(
        $first_line, $wordwrap, "\n", true, $this->charset
      );
      // ----- now get the wrapped first line
      $wrapped_first_linebreak = mb_strpos($wrapped, "\n", 0, $this->charset);
      if(false === $wrapped_first_linebreak) {
        // only one wrapped line
        return $prefix . $wrapped . "\n";
      } else {
        $wrapped_first_line = mb_substr($wrapped, 0, $wrapped_first_linebreak, $this->charset);
        // ----- write it
        $text .= $prefix . $wrapped_first_line . "\n";
        // remove the wrapped first-line from original value
        $value = mb_substr($value, $wrapped_first_linebreak, mb_strlen($value, $this->charset), $this->charset);
      }
    }
    // Handle remaining lines
    $prefix = $indent . $context['line_prefix'];
    $wordwrap = $this->wordwrap - mb_strlen($prefix, $this->charset);
    $value = Text\MultiByte::wordwrap(
      $value, $wordwrap, "\n", true, $this->charset
    );
    $value = $raw ? $value : Text\Utf8::normalizeWhitespace($value);
    $lines = explode("\n", $value);
    foreach ($lines as $line) {
      $v = $raw ? $line : trim($line);
      if($v) {
        $text .= $prefix . $v . "\n";
      }
    }
    return $text;
  }

  private function boxify($text, $chars)
  {
    $chars = explode(",", $chars);
    $num_chars = count($chars);
    if($num_chars == 2) {
      $n = $s = $chars[0];
      $e = $w = $nw = $ne = $se = $sw = $chars[1];
    } else if($num_chars == 3) {
      $nw = $ne = $se = $sw = $chars[0];
      $n = $s = $chars[1];
      $e = $w = $chars[2];
    } else {
      throw new \InvalidArgumentException("Invalid format for box characters");
    }
    $width = Text\MultiByte::text_width($text, $this->charset);
    $output = $nw . str_repeat($n, $width + 2) . $ne . "\n";
    foreach(explode("\n", $text) as $line) {
      $output .= $w . ' ' . Text\MultiByte::str_pad($line, $width) . ' ' . $e . "\n";
    }
    $output .= $sw . str_repeat($s, $width + 2) . $se . "\n";
    return $output;
	}

	private function applyBorders(\DOMElement $node, $text, $context)
	{
		$has_border_top = $node->hasAttribute('border-top');
		$has_border_bottom = $node->hasAttribute('border-bottom');

		if($has_border_bottom || $has_border_top) {

			$width = Text\MultiByte::text_width($text, $this->charset);
			$txt_width = $width - mb_strlen($context['indent'], $this->charset);

			if($has_border_bottom) {
				$char = $node->getAttribute('border-bottom');
				$border = str_repeat($char, $txt_width);
				$text = rtrim($text) . "\n" . $context['indent'] . $border . "\n";
			}

			if($has_border_top) {
				$char = $node->getAttribute('border-top');
				$border = str_repeat($char, $txt_width);
				$text = $context['indent'] . $border . "\n" . ltrim($text);
			}

		}
		return $text;
	}

	/**
	 * Computes margins to text blocks.
	 * We must get the maximum margin value from children
	 * in order to collapse margins
	 *
	 * @param \DOMElement $node The current text block
	 * @param string $text The current text buffer for this node
	 *
	 * @return string
	 **/
	private function applyLineBreaks(\DOMElement $node, $text)
	{
		if($node->hasAttribute('lines-before')) {

			$lines_before = (int) $node->getAttribute('lines-before');
			$children_lines_before = $this->getNumLinesAppliedByChildren('before', $node);
			// We also have to get the result of the previous sibling
			$previous_lines_after = 0;
			if(($prev = $node->previousSibling) && $prev->nodeType == XML_ELEMENT_NODE && $prev->hasAttribute('lines-after')) {
				$previous_lines_after = $prev->getAttribute('lines-after');
			}
			$max_lines_before = max($lines_before, $children_lines_before);
			if($max_lines_before > $previous_lines_after) {
				$prefix = str_repeat("\n", $max_lines_before - $previous_lines_after);
				$text = $prefix . ltrim($text, "\n");
			}
			$node->setAttribute('lines-before', $max_lines_before - $previous_lines_after);
			
    }
		if($node->hasAttribute('lines-after')) {

			$lines_after = (int) $node->getAttribute('lines-after');
			$children_lines_after = $this->getNumLinesAppliedByChildren('after', $node);
			if($lines_after > $children_lines_after) {
				$suffix = str_repeat("\n", $lines_after + 1);
				$text = rtrim($text, "\n") . $suffix;
			}
			$node->setAttribute('lines-after', max($children_lines_after, $lines_after));

		}
		return $text;
	}

	/**
	 * At this point lines-before/after have already been added by the children,
	 * so we need to walk down the tree to find the max number of lines
	 * added by the children, and apply them to the current node,
	 * only if it's smaller than our number...
	 *
	 **/
	private function getNumLinesAppliedByChildren($where, $node)
	{
		$child = ($where == 'before') ? 'firstChild' : 'lastChild';
		$max_lines = 0;
		$it = $node;
		while($it = $it->$child) {
			if($it->nodeType !== XML_ELEMENT_NODE) {
				continue;
			}
			if($it->hasAttribute('lines-'.$where)) {
				$lines = (int) $it->getAttribute('lines-'.$where);
				if($lines > $max_lines) $max_lines = $lines;
				// remove this attribute, as the value of $max_lines
				// will be set on the parent
				$it->removeAttribute('lines-'.$where);
			}
		}
		return $max_lines;
	}

}
