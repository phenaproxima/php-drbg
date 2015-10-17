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
    if ($strength > 256) {
      throw new \OutOfBoundsException('Strength cannot exceed 256 bits.');
    }
    if ((strlen($personalizer) * 8) > 256) {
      throw new \LengthException('Personalization string cannot exceed 256 bits.');
    }

    if ($strength <= 112) {
      $this->strength = 112;
    }
    elseif ($strength <= 128) {
      $this->strength = 128;
    }
    elseif ($strength <= 192) {
      $this->strength = 192;
    }
    else {
      $this->strength = 256;
    }

    if ((strlen($entropy) * 8 * 2) < (3 * $this->strength)) {
      throw new \LengthException('Entropy must be at least ' . (1.5 * $this->strength) . ' bits.');
    }
    if ((strlen($entropy) * 8) > 1000) {
      throw new \LengthException('Entropy cannot exceed 1000 bits.');
    }

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
    if ((strlen($entropy) * 8) < $this->strength) {
      throw new \LengthException('Entropy must be at least ' . $this->strength . ' bits.');
    }
    if ((strlen($entropy) * 8) > 1000) {
      throw new \LengthException('Entropy cannot exceed 1000 bits.');
    }

    $this->update($entropy);
    $this->reseedCount = 1;
  }

  public function generate($length, $strength = 256) {
    if ($length * 8 > 7500) {
      throw new \InvalidArgumentException('Cannot generate more than 7500 bits in a single call.');
    }
    if ($strength > $this->strength) {
      throw new \OutOfBoundsException('Given strength exceeds configured ' . $this->strength . ' bits.');
    }

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
