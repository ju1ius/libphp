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
	/**
	 * Returns the length of the longest line in a string.
	 *
	 * @param string $str
	 * @param string $charset
	 * @return int
	 */
	public static function text_width($str, $charset="utf-8")
  {
    $lines = explode("\n", $str);
    $max_width = 0;
    foreach ($lines as $line) {
      $width = mb_strlen($line, $charset);
      if($width > $max_width) $max_width = $width;
    }
    return $max_width;
	}

	/**
   * Multibyte word wrap.
   *
   * If your encoding is utf-8, uses ju1ius\Text\Utf8::wordwrap.
   * It performs faster.
   *
   * This is an mbstring version of Zend\Text\MultiByte::wordWrap()
	 *
	 * @param  string  $string
	 * @param  integer $width
	 * @param  string  $break
	 * @param  boolean $cut
	 * @param  string  $charset
	 * @return string
	 */
	public static function wordwrap($string, $width = 75, $break = "\n", $cut = false, $charset = 'UTF-8')
	{
    if(strtolower($charset) === 'utf-8') {
      return Utf8::wordwrap($string, $width, $break, $cut);
    }
		$result     = array();
		$breakWidth = mb_strlen($break, $charset);

		while (($stringLength = mb_strlen($string, $charset)) > 0) {
			$breakPos = mb_strpos($string, $break, 0, $charset);

			if ($breakPos !== false && $breakPos < $width) {
				if ($breakPos === $stringLength - $breakWidth) {
					$subString = $string;
					$cutLength = null;
				} else {
					$subString = mb_substr($string, 0, $breakPos, $charset);
					$cutLength = $breakPos + $breakWidth;
				}
			} else {
				$subString = mb_substr($string, 0, $width, $charset);

				if ($subString === $string) {
					$cutLength = null;
				} else {
					$nextChar = mb_substr($string, $width, 1, $charset);

					if ($breakWidth === 1) {
						$nextBreak = $nextChar;
					} else {
						$nextBreak = mb_substr($string, $breakWidth, 1, $charset);
					}

					if ($nextChar === ' ' || $nextBreak === $break) {
						$afterNextChar = mb_substr($string, $width + 1, 1, $charset);

						if ($afterNextChar === false) {
							$subString .= $nextChar;
						}

						$cutLength = mb_strlen($subString, $charset) + 1;
					} else {
						$spacePos = mb_strrpos($subString, ' ', $charset);

						if ($spacePos !== false) {
							$subString = mb_substr($subString, 0, $spacePos, $charset);
							$cutLength = $spacePos + 1;
						} else if ($cut === false) {
							$spacePos = mb_strpos($string, ' ', 0, $charset);

							if ($spacePos !== false) {
								$subString = mb_substr($string, 0, $spacePos, $charset);
								$cutLength = $spacePos + 1;
							} else {
								$subString = $string;
								$cutLength = null;
							}
						} else {
							$subString = mb_substr($subString, 0, $width, $charset);
							$cutLength = $width;
						}
					}
				}
			}

			$result[] = $subString;

			if ($cutLength !== null) {
				$string = mb_substr($string, $cutLength, ($stringLength - $cutLength), $charset);
			} else {
				break;
			}
		}

		return implode($break, $result);
	}

	/**
	 * String padding
	 *
	 * @param  string  $input
	 * @param  integer $padLength
	 * @param  string  $padString
	 * @param  integer $padType
	 * @param  string  $charset
	 * @return string
	 */
	public static function str_pad($input, $padLength, $padString = ' ', $padType = STR_PAD_RIGHT, $charset = 'UTF-8')
	{
		$return          = '';
		$lengthOfPadding = $padLength - mb_strlen($input, $charset);
		$padStringLength = mb_strlen($padString, $charset);

		if ($padStringLength === 0 || $lengthOfPadding === 0) {
			$return = $input;
		} else {
			$repeatCount = floor($lengthOfPadding / $padStringLength);

			if ($padType === STR_PAD_BOTH) {
				$lastStringLeft  = '';
				$lastStringRight = '';
				$repeatCountLeft = $repeatCountRight = ($repeatCount - $repeatCount % 2) / 2;

				$lastStringLength       = $lengthOfPadding - 2 * $repeatCountLeft * $padStringLength;
				$lastStringLeftLength   = $lastStringRightLength = floor($lastStringLength / 2);
				$lastStringRightLength += $lastStringLength % 2;

				$lastStringLeft  = mb_substr($padString, 0, $lastStringLeftLength, $charset);
				$lastStringRight = mb_substr($padString, 0, $lastStringRightLength, $charset);

				$return = str_repeat($padString, $repeatCountLeft) . $lastStringLeft
					. $input
					. str_repeat($padString, $repeatCountRight) . $lastStringRight;
			} else {
				$lastString = mb_substr($padString, 0, $lengthOfPadding % $padStringLength, $charset);

				if ($padType === STR_PAD_LEFT) {
					$return = str_repeat($padString, $repeatCount) . $lastString . $input;
				} else {
					$return = $input . str_repeat($padString, $repeatCount) . $lastString;
				}
			}
		}

		return $return;
	}
}
