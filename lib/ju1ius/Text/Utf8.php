<?php

namespace ju1ius\Text;

/**
 * Provides functions to handle UTF-8 strings
 *
 * @package Text
 */
class Utf8
{
  public static function str_split($str)
  {
    // Split at all position not after the start (^) and not before the end ($)
    return preg_split('/(?<!^)(?!$)/uS', $str); 
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
  public static function normalizeWhitespace($str, $preserve_linebreaks=false)
  {
    if($preserve_linebreaks) {
      return preg_replace(
        array('/\h+/u', '/\v+/u', "/(?: ?\n ?)+/u"),
        array(' ', "\n", "\n"),
        trim($str)
      );
    }
    return preg_replace('/\s\s+/u', ' ', trim($str));
  }

  /**
   * UTF-8 safe version of internal wordwrap function
   *
   * @param string  $str        The input string
   * @param int     $width      The maximum line length
   * @param string  $break      The linebreak to use. Must be escaped with preg_quote if coming from external input.
   *
   * @return string
   **/
  public static function wordwrap($str, $width=75, $break="\n", $cut=false)
  {
    if($cut) {
      // Match anything 1 to $width chars long followed by whitespace or EOS,
      // otherwise match anything $width chars long
      $search = '/(.{1,'.($width-1).'})(?:\pZ|(\pM)|$)|(.{'.$width.'})/uS';
      $replace = '$1$2$3'.$break;
    } else {
      // Anchor the beginning of the pattern with a lookahead
      // to avoid backtracking when words are longer than $width
      $search = '/(?=\s)(.{1,'.$width.'})(?:\s|$)/uS';
      $replace = '$1'.$break;
    }
    return preg_replace($search, $replace, $str);
  }
}
