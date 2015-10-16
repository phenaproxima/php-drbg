<?php

namespace phenaproxima;

class ByteArray implements \Countable {

  /**
   * @var int[]
   */
  protected $bytes = [];

  public function __construct(array $bytes = []) {
    $this->bytes = $bytes;
  }

  /**
   * @return static
   */
  public static function create($bytes) {
    if ($bytes instanceof static) {
      return $bytes;
    }
    elseif (is_string($bytes)) {
      $bytes = unpack('C*', $bytes);
    }

    return new static(is_array($bytes) ? $bytes : []);
  }

  /**
   * @return static
   */
  public function copy() {
    return clone $this;
  }

  /**
   * @return int[]
   */
  public function getBytes() {
    // array_values() will re-index the array.
    return array_values($this->bytes);
  }

  /**
   * @return string
   */
  public function toBinaryString() {
    $arguments = array_merge(['C*'], $this->getBytes());
    return call_user_func_array('pack', $arguments);
  }

  /**
   * @return string[]
   */
  public function toHexArray() {
    $func = function($byte) {
      return sprintf('%02x', $byte);
    };
    return array_map($func, $this->getBytes());
  }

  /**
   * @return string
   */
  public function toHexString() {
    return implode(NULL, $this->toHexArray());
  }

  /**
   * @return $this
   */
  public function prepend() {
    $bytes = [];
    foreach (func_get_args() as $argument) {
      $bytes = array_merge($bytes, static::create($argument)->getBytes());
    }
    $this->bytes = array_merge($bytes, $this->getBytes());
    return $this;
  }

  /**
   * @return $this
   */
  public function append() {
    foreach (func_get_args() as $argument) {
      $this->bytes = array_merge($this->getBytes(), static::create($argument)->getBytes());
    }
    return $this;
  }

  /**
   * @return int
   */
  public function count() {
    return count($this->getBytes());
  }

}
