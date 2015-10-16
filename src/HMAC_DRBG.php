<?php

namespace phenaproxima;

class HMAC_DRBG {

  /**
   * @var int
   */
  protected $strength;

  /**
   * @var ByteArray
   */
  protected $k;

  /**
   * @var ByteArray
   */
  protected $v;

  /**
   * @var int
   */
  protected $reseedCount = 1;

  public function __construct($entropy, $strength = 256, $personalizer = '') {
    // @TODO Check the arguments.

    $this->k = new ByteArray(array_fill(0, 32, 0x0));
    $this->v = new ByteArray(array_fill(0, 32, 0x1));
    $this->update($entropy . $personalizer);
  }

  protected function HMAC(ByteArray $key, ByteArray $data) {
    $hash = hash_hmac('sha256', $data->toBinaryString(), $key->toBinaryString(), TRUE);
    return ByteArray::create($hash);
  }

  protected function update($data = NULL) {
    $this->k = $this->HMAC($this->k, $this->v->copy()->append([0x0], $data));
    $this->v = $this->HMAC($this->k, $this->v);

    if ($data) {
      $this->k = $this->HMAC($this->k, $this->v->copy()->append([0x1], $data));
      $this->v = $this->HMAC($this->k, $this->v);
    }
  }

  public function reseed($entropy) {
    $this->update($entropy);
    $this->reseedCount = 1;
  }

  public function generate($length) {
    if ($this->reseedCount < 10000) {
      $bytes = new ByteArray();

      while (sizeof($bytes) < $length) {
        $this->v = $this->HMAC($this->k, $this->v);
        $bytes->append($this->v);
      }

      $this->update();
      $this->reseedCount++;

      return $bytes;
    }
  }

}
