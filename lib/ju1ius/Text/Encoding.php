<?php

namespace ju1ius\Text;

define('JU1IUS_HAS_FILEINFO', extension_loaded('file_info'));
define('JU1IUS_HAS_MBSTRING', extension_loaded('mbstring'));
define('JU1IUS_HAS_ICONV',    extension_loaded('iconv'));

class Encoding
{
  private static
    $DEFAULT_ENCODING,
    $ENCODINGS_MAP,
    $ASCII_COMPATIBLE_ENCODINGS,
    $IDENTITY_CACHE;

  /**
   * A global pointer to the default charset used for String manipulations.
   *
   * Returns the default encoding if it has been set by setDefault(),
   * or the internal encoding returned by mb_internal_encoding
   * or iconv_get_encoding('internal_encoding')?
   *
   * @return string|null
   **/
  public static function getDefault()
  {
    if(!self::$DEFAULT_ENCODING) {
      if(JU1IUS_HAS_MBSTRING) {
        self::$DEFAULT_ENCODING = mb_internal_encoding();
      } else if (JU1IUS_HAS_ICONV) {
        self::$DEFAULT_ENCODING = iconv_get_encoding('internal_encoding');
      }
    }
    return self::$DEFAULT_ENCODING;
  }
  public static function setDefault($charset)
  {
    self::$DEFAULT_ENCODING = $charset;
  }

  public static function isSupported($encoding)
  {
    return in_array(strtolower($encoding), self::getEncodingsMap());
  }

  public static function isSameEncoding($first, $second)
  {
    $first = strtolower($first);
    $second = strtolower($second);
    if($first === $second) return true;
    $map = self::getEncodingsMap();
    if(!isset($map[$first]) || !isset($map[$second])) return false;
    $aliases = $map[$first];
    return in_array($second, $aliases, true);
  }

  public static function getEncodingsMap()
  {
    if(null === self::$ENCODINGS_MAP) {
      $map = array();
      foreach(mb_list_encodings() as $encoding) {
        $aliases = array_map('strtolower', mb_encoding_aliases($encoding));
        $encoding = strtolower($encoding);
        $map[$encoding] = $aliases;
        foreach($aliases as $alias) {
          if(!isset($map[$alias])) {
            $map[$alias] = $aliases;
            $map[$alias][] = $encoding;
          }
        }
      }
      self::$ENCODINGS_MAP = $map;
    }
    return self::$ENCODINGS_MAP;
  }

  public static function isAsciiCompatible($encoding)
  {
    $compatible_encodings = self::getAsciiCompatibleEncodings();
    return in_array(strtolower($encoding), $compatible_encodings, true);
  }

  public static function getAsciiCompatibleEncodings()
  {
    if(null === self::$ASCII_COMPATIBLE_ENCODINGS) {
      $ascii_chars = '';
      foreach(range(0,127) as $ord) {
        $ascii_chars .= chr($ord);
      }
      $compatible_encodings = array();
      foreach(mb_list_encodings() as $encoding) {
        $encoded = mb_convert_encoding($ascii_chars, $encoding);
        if($encoded === $ascii_chars) {
          $compatible_encodings[] = strtolower($encoding);
          foreach(mb_encoding_aliases($encoding) as $alias) {
            $compatible_encodings[] = strtolower($alias);
          }
        }
      }
      self::$ASCII_COMPATIBLE_ENCODINGS = $compatible_encodings;
    }
    return self::$ASCII_COMPATIBLE_ENCODINGS;
  }

  public static function detect($str)
  {
    $encoding = false;
    if(JU1IUS_HAS_FILEINFO) {
      $encoding = finfo_buffer($str, FILEINFO_MIME_ENCODING);
    }
    // if the encoding is detected as binary, try again with mbstring
    if ((false === $encoding || 'binary' == strtolower($encoding)) && JU1IUS_HAS_MBSTRING) {
      $encoding = mb_detect_encoding($str, mb_detect_order(), true);
    }
    if (false === $encoding || 'binary' == strtolower($encoding)) {
      return false;
    }
    return $encoding;
  }

  public static function detectFile($filename)
  {
    $encoding = false;
    if(JU1IUS_HAS_FILEINFO) {
      $encoding = finfo_file($filename, FILEINFO_MIME_ENCODING);
    }
    if(false === $encoding || 'binary' == strtolower($encoding)) {
      return static::detect(file_get_contents($filename));
    }
    return $encoding;
  }

  public function convert($str, $to="utf-8", $from=false)
  {
    if(!$from) {
      $from = static::detect($str);
    }
    if(JU1IUS_HAS_MBSTRING) {
      if(false === $from) $from = mb_internal_encoding();
      return mb_convert_encoding($str, $to, $from);
    } else if(JU1IUS_HAS_ICONV) {
      if(false === $from) $from = iconv_get_encoding('internal_encoding');
      return iconv($from, $to.'//TRANSLIT', $str);
    }
    return false;
  }

  /**
   * Removes the Byte Order Mark from a string
   *
   * @param string $text
   *
   * @return string
   **/
  public static function removeBOM($text)
  {
    $len = strlen($text);
    if($len > 3) {
      switch ($text[0]) {

        case "\xEF":
          if(("\xBB" == $text[1]) && ("\xBF" == $text[2])) {
            // EF BB BF  UTF-8 encoded BOM
            return substr($text, 3);
          }
          break;

        case "\xFE":
          if (("\xFF" == $text[1]) && ("\x00" == $text[2]) && ("\x00" == $text[3])) {
            // FE FF 00 00  UCS-4, unusual octet order BOM (3412)
            return substr($text, 4);
          } else if ("\xFF" == $text[1]) {
             // FE FF  UTF-16, big endian BOM
            return substr($text, 2);
          }
          break;

        case "\x00":
          if (("\x00" == $text[1]) && ("\xFE" == $text[2]) && ("\xFF" == $text[3])) {
            // 00 00 FE FF  UTF-32, big-endian BOM
            return substr($text, 4);
          } else if (("\x00" == $text[1]) && ("\xFF" == $text[2]) && ("\xFE" == $text[3])) {
            // 00 00 FF FE  UCS-4, unusual octet order BOM (2143)
            return substr($text, 4);
          }
          break;

        case "\xFF":
          if (("\xFE" == $text[1]) && ("\x00" == $text[2]) && ("\x00" == $text[3])) {
            // FF FE 00 00  UTF-32, little-endian BOM
            return substr($text, 4);
          } else if ("\xFE" == $text[1]) {
            // FF FE  UTF-16, little endian BOM
            return substr($text, 2);
          }
          break;

      }
    }
    return $text;
  }

  /**
   * Detects a string encoding according to it's BOM if present
   *
   * @param string $text
   *
   * @return string
   **/
  public static function checkForBOM($text)
  {
    $len = strlen($text);
    if($len > 3) {
      switch ($text[0]) {

        case "\xEF":
          if(("\xBB" == $text[1]) && ("\xBF" == $text[2])) {
            // EF BB BF  UTF-) encoded BOM
            return 'UTF-8';
          }
          break;

        case "\xFE":
          if (("\xFF" == $text[1]) && ("\x00" == $text[2]) && ("\x00" == $text[3])) {
            // FE FF 00 00  UCS-4, unusual octet order BOM (3412)
            return "X-ISO-10646-UCS-4-3412";
          } else if ("\xFF" == $text[1]) {
             // FE FF  UTF-16, big endian BOM
            return "UTF-16BE";
          }
          break;

        case "\x00":
          if (("\x00" == $text[1]) && ("\xFE" == $text[2]) && ("\xFF" == $text[3])) {
            // 00 00 FE FF  UTF-32, big-endian BOM
            return "UTF-32BE";
          } else if (("\x00" == $text[1]) && ("\xFF" == $text[2]) && ("\xFE" == $text[3])) {
            // 00 00 FF FE  UCS-4, unusual octet order BOM (2143)
            return "X-ISO-10646-UCS-4-2143";
          }
          break;

        case "\xFF":
          if (("\xFE" == $text[1]) && ("\x00" == $text[2]) && ("\x00" == $text[3])) {
            // FF FE 00 00  UTF-32, little-endian BOM
            return "UTF-32LE";
          } else if ("\xFE" == $text[1]) {
            // FF FE  UTF-16, little endian BOM
            return "UTF-16LE";
          }
          break;
      }
    }
    return false;
  }

  /**
   * Returns a byte representation of the given string
   *
   * @param string $string
   * @param int    $length
   *
   * @return string
   **/
  public static function toByteString($string, $length=null)
  {
    if($length == null) $length = strlen($string);
    $bytes = array();
    for($i = 0; $i < $length; $i++) {
      $bytes[] = "0x".dechex(ord($string[$i]));
    }
    return implode(' ', $bytes);
  }

}
