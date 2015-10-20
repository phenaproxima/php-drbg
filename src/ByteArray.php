<?php

namespace phenaproxima;

class ByteArray implements \Countable {

  /**
   * @var int[]
   */
  protected $bytes = [];

  /**
   * Constructs a ByteArray.
   *
   * @param int[] $bytes
   *  (optional) Array of bytes, represented as integers.
   */
  public function __construct(array $bytes = []) {
    $this->bytes = $bytes;
  }

  /**
   * Creates a ByteArray.
   *
   * @param static|string|array $bytes
   *   Array of input bytes. Can be a ByteArray, a binary string, or an array
   *   of bytes represented as integers.
   *
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
   * Creates a ByteArray from a hex-encoded string.
   *
   * @param string $hex
   *   A string of hex-encoded bytes.
   *
   * @return static
   */
  public static function fromHexString($hex) {
    if ($hex) {
      $hex = array_map('hexdec', str_split($hex, 2));
    }
    return new static($hex);
  }

  /**
   * Clones the ByteArray. This is just a utility method to provide a fluent
   * interface.
   *
   * @return static
   */
  public function copy() {
    return clone $this;
  }

  /**
   * Returns the raw bytes wrapped by this ByteArray, represented as integers.
   *
   * @return int[]
   */
  public function getBytes() {
    // array_values() will re-index the array.
    return array_values($this->bytes);
  }

  /**
   * Returns the byte array as a binary string.
   *
   * @return string
   */
  public function toBinaryString() {
    $arguments = array_merge(['C*'], $this->getBytes());
    return call_user_func_array('pack', $arguments);
  }

  /**
   * Returns the byte array as an array of two-digit hex values.
   *
   * @return string[]
   */
  public function toHexArray() {
    $func = function($byte) {
      return sprintf('%02x', $byte);
    };
    return array_map($func, $this->getBytes());
  }

  /**
   * Returns the byte array as a hex-encoded string.
   *
   * @return string
   */
  public function toHexString() {
    return implode(NULL, $this->toHexArray());
  }

  /**
   * Prepends bytes to the array.
   *
   * @param mixed $value, [$value [...]]
   *   The value(s) to prepend. Each value can be a ByteArray, a binary string,
   *   or an array of bytes represented as integers.
   *
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
   * Appends bytes to the array.
   *
   * @param mixed $value, [$value, [...]]
   *   The value(s) to append. Each value can be a ByteArray, a binary string,
   *   or an array of bytes represented as integers.
   *
   * @return $this
   */
  public function append() {
    foreach (func_get_args() as $argument) {
      $this->bytes = array_merge($this->getBytes(), static::create($argument)->getBytes());
    }
    return $this;
  }

  /**
   * Returns the size of the byte array.
   *
   * @return int
   */
  public function count() {
    return count($this->getBytes());
  }

}
