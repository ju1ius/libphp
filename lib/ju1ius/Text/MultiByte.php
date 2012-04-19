<?php

namespace ju1ius\Text;

/**
 * Contains multibyte safe string methods
 *
 * @package    ju1ius
 * @subpackage Text
 */
class MultiByte
{ 
  public static function str_split($str, $charset=null)
  {
    if($charset) {
      mb_regex_encoding($charset);
    }
    // Split at all position not after the start (^) and not before the end ($)
    return mb_split('(?<!^)(?!$)', $str); 
  }

	/**
	 * Returns the length of the longest line in a string.
	 *
	 * @param string $str
	 * @param string $charset
	 * @return int
	 */
	public static function text_width($str, $charset=null)
  {
    if($charset) {
      mb_regex_encoding($charset);
    }
    $lines = mb_split('\r\n|[\n\r\v]', $str);
    $max_width = 0;
    foreach ($lines as $line) {
      $width = mb_strlen($line, $charset);
      if($width > $max_width) $max_width = $width;
    }
    return $max_width;
	}

  /**
   * MultiByte safe version of internal wordwrap function
   *
   * @param string  $str        The input string
   * @param int     $width      The maximum line length
   * @param string  $break      The linebreak to use. Must be escaped with preg_quote if coming from external input.
   *
   * @return string
   **/
  public static function wordwrap($str, $width=75, $break="\n", $cut=false, $charset=null)
  {
    if($charset) {
      mb_regex_encoding($charset);
    }
    if($cut) {
      // Match anything 1 to $width chars long followed by whitespace or EOS,
      // otherwise match anything $width chars long
      $search = '(.{1,'.($width-1).'})(?:\pZ|(\pM)|$)|(.{'.($width-1).'})';
      $replace = '\1\2\3'.$break;
    } else {
      // Anchor the beginning of the pattern with a lookahead
      // to avoid backtracking when words are longer than $width
      $search = '(?=\s)(.{1,'.($width-1).'})(?:\s|$)';
      $replace = '\1'.$break;
    }
    return mb_ereg_replace($search, $replace, $str);
  }

  /**
   * Normalizes whitespace by reducing repeated whitespace characters
   *
   * If $preserve_linebreaks is false (default), every repeated whitespace character
   * will be replaced by a single space (" ").
   *
   * If $preserve_linebreaks is true, it will try to preserve original linebreaks.
   *
   * @param string  $str
   * @param bool    $preserve_linebreaks
   * @return string
   **/
  public static function normalizeWhitespace($str, $preserve_linebreaks=false, $charset=null)
  {
    if($charset) {
      mb_regex_encoding($charset);
    }
    if($preserve_linebreaks) {
      $str = trim($str);
      $str = mb_ereg_replace('(?:[ \t\p{Zs}])+', ' ', $str);
      $str = mb_ereg_replace('(?:\r\n|[\f\r\n\p{Zl}\p{Zp}])+', "\n", $str);
      $str = mb_ereg_replace('(?: ?\n ?)+', "\n", $str);
      return $str;
    }
    return mb_ereg_replace('\s\s+', ' ', trim($str));
  }

  /**
   * Returns a string padded to a certain length with another string.
   *
   * This method behaves exactly like str_pad but is multibyte safe.
   *
   * @param string $input    The string to be padded.
   * @param int $length      The length of the resulting string.
   * @param string $pad      The string to pad the input string with. Must
   *                         be in the same charset like the input string.
   * @param const $type      The padding type. One of STR_PAD_LEFT,
   *                         STR_PAD_RIGHT, or STR_PAD_BOTH.
   * @param string $charset  The charset of the input and the padding
   *                         strings.
   *
   * @return string  The padded string.
   */
  static public function str_pad($input, $length, $pad=' ', $type=STR_PAD_RIGHT, $charset='UTF-8')
  {
    $mb_length = mb_strlen($input, $charset);
    $sb_length = strlen($input);
    $pad_length = mb_strlen($pad, $charset);

    /* Return if we already have the length. */
    if ($mb_length >= $length) {
      return $input;
    }

    /* Shortcut for single byte strings. */
    if ($mb_length == $sb_length && $pad_length == strlen($pad)) {
      return str_pad($input, $length, $pad, $type);
    }

    switch ($type) {
      case STR_PAD_LEFT:
        $left = $length - $mb_length;
        $output = mb_substr(str_repeat($pad, ceil($left / $pad_length)), 0, $left, $charset) . $input;
        break;
      case STR_PAD_BOTH:
        $left = floor(($length - $mb_length) / 2);
        $right = ceil(($length - $mb_length) / 2);
        $output = mb_substr(str_repeat($pad, ceil($left / $pad_length)), 0, $left, $charset) .
          $input .
          mb_substr(str_repeat($pad, ceil($right / $pad_length)), 0, $right, $charset);
        break;
      case STR_PAD_RIGHT:
        $right = $length - $mb_length;
        $output = $input . mb_substr(str_repeat($pad, ceil($right / $pad_length)), 0, $right, $charset);
        break;
    }

    return $output;
  }

}
