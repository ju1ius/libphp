<?php

namespace ju1ius\Collections;

/**
 * Parameter holder which allows dot-separated access to hierarchical data
 **/
class ParameterBag
{
  protected
    $data,
    $cache,
    $separator;

  public function __construct(array $data = array(), $separator='.')
  {
    $this->data = $data;
    $this->separator = $separator;
    $this->cache = array();
  }

  /**
   * Gets a value at the specified path, returning the default if not found
   *
   * @param string $path
   * @param mixed  $default
   *
   * @return mixed
   **/
  public function get($path=null, $default=null)
  {
    if(null === $path) return $this->data;
    if(isset($this->cache[$path])) return $this->cache[$path];

    $segs = explode($this->separator, $path); 
    $len = count($segs);
    $target =& $this->data;

    for($i = 0; $i < $len-1; $i++) { 
      if(isset($target[$segs[$i]]) && is_array($target[$segs[$i]])) { 
        $target =& $target[$segs[$i]]; 
      } else { 
        return $default; 
      } 
    } 

    if(isset($target[$segs[$len-1]])) {
      $result = $target[$segs[$len-1]];
      $this->cache[$path] = $result;
      return $result;
    } else {
      return $default;
    }  
  }

  /**
   * Sets a value at the specified path.
   * If value is null, unsets the value at the specified path.
   *
   * @param string $path
   * @param mixed  $value
   *
   * @return $this
   **/
  public function set($path=null, $value=null)
  {
    if(is_array($path)) {

      foreach($path as $p => $v) {
        $this->set($p, $v);
      }

    } else {

      $segs = explode($this->separator, $path);
      $len = count($segs);
      $tail = $segs[$len-1];
      $target =& $this->data; 

      for($i = 0; $i < $len-1; $i++) {
        if(!isset($target[$segs[$i]])) {
          $target[$segs[$i]] = array(); 
        } 
        $target =& $target[$segs[$i]]; 
      } 

      if($tail == '*') {
        $path = implode($this->separator, array_slice($segs, 0, $len-1));
        foreach($target as $k => $v) {
          $target[$k] = $value;
          $this->cache[$path.$this->separator.$k] = $value;
        } 
      } else if($value === null && isset($target[$tail])) {
        unset($target[$tail]);
        unset($this->cache[$path]);
      } else {
        $target[$tail] = $value;
        $this->cache[$path] = $value;
      } 
    } 

    return $this;
  }

  /**
   * Recursively merges provided data with existing data
   *
   * @param array $data
   *
   * @return $this
   **/
  public function merge(array $data)
  {
    $this->data = array_replace_recursive($this->data, $data);
    return $this;
  }

  /**
   * Returns a flattened version of the data (one-dimensional array with dot-separated paths as its keys).
   *
   * @param string $path
   *
   * @return array
   **/
  public function flatten($path=null)
  {
    $data = $this->get($path); 
    $flat = array(); 

    if($path === null) {
      $path = ''; 
    } else {
      $path .= $this->separator;
    } 

    foreach($data as $key => $value) {
      if(is_array($value)) {
        $flat += $this->flatten($path.$key); 
      } else {
        $flat[$path.$key] = $value; 
      }
    }

    return $flat;  
  }

  /**
   * Expands a flattened array to a new ParameterBag .
   *
   * @param array $flat
   *
   * @return ParameterBag
   **/
  static public function expand(array $flat)
  {
    $bag = new ParameterBag(); 

    foreach($flat as $key => $value)
    {
      $bag->set($key, $value); 
    } 

    return $bag;  
  }

  public function dump()
  {
    echo "\nData:\n";
    var_dump($this->data);
    echo "\nCache:\n";
    var_dump($this->cache);
  }
}
