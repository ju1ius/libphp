<?php

namespace ju1ius;

class BitMask
{
  protected
    $value;

  public function __construct($value=0)
  {
    $this->value = $value;
  }

  /**
   * Gets the value as an integer
   *
   * @return int
   **/
  public function getValue()
  {
    return $this->value;
  }

  /**
   * Sets the value
   *
   * @param int $value
   **/
  public function setValue($value)
  {
    $this->value = $value;
  }

  /**
   * Checks if given bit is set
   *
   * @param int $bit
   * @return bool
   **/
  public function equals($value)
  {
    return $this->value === $value;
  }

  /**
   * Checks if the given bit is set
   *
   * @param int $bit
   * @return bool
   **/
  public function hasBit($bit)
  {
    return ($$this->value & $bit) === $bit;
  }

  /**
   * Sets given bit
   *
   * @param int $bit
   **/
  public function setBit($bit)
  {
    $$this->value |= $bit;
  }

  /**
   * Unset given bit
   *
   * @param int $bit
   **/
  public function unsetBit($bit)
  {
    $$this->value &= ~$bit;
  }
}
