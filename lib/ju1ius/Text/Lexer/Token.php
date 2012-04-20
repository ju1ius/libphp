<?php

namespace ju1ius\Text\Lexer;

class Token
{
  /**
   * @var integer The type of this token.
   **/
  public $type;
  /**
   * @var mixed The value of this token.
   **/
  public $value;
  /**
   * @var integer The position of this token in the source file.
   **/
  public $position;

  /**
   * Constructor.
   *
   * @param string $type The type of this token.
   * @param mixed $value The value of this token.
   * @param integer $position The order of this token.
   */
  public function __construct($type, $value, $position)
  {
    $this->type = $type;
    $this->value = $value;
    $this->position = $position;
  }

  /**
   * Gets a string representation of this token.
   *
   * @return string
   */
  public function __toString()
  {
    if(is_array($this->value)) {
      return implode('', $this->value);
    }
    return (string) $this->value;
  }

  /**
   * Answers whether this token's type equals to $type.
   *
   * @param string|int $types The type to test against this token.
   *
   * @return Boolean
   */
  public function isOfType($type)
  {
    return $this->type === $type;
  }


  /**
   * Checks whether this token's type is one of given types.
   *
   * @param array $types The types to test against this token.
   *
   * @return Boolean
   */
  public function isOneOfTypes(array $types)
  {
    return in_array($this->type, $types);
  }

  /**
   * Gets the position of this token.
   *
   * @return integer
   */
  public function getPosition()
  {
    return $this->position;
  }

  public function getType()
  {
    return $this->type;
  }

  public function getValue()
  {
    return $this->value;
  }
}
