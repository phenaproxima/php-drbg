<?php

namespace phenaproxima;

/**
 * @coversDefaultClass \phenaproxima\HMAC_DRBG
 */
class HMAC_DRBGTest extends \PHPUnit_Framework_TestCase {

  protected $algorithm;

  /**
   * @expectedException \OutOfBoundsException
   * @expectedExceptionMessage Strength cannot exceed 256 bits.
   */
  public function testInvalidStrength() {
    new HMAC_DRBG('', 512);
  }

  /**
   * The personalizer cannot exceed 32 bytes.
   *
   * @expectedException \LengthException
   * @expectedExceptionMessage Personalization string cannot exceed 256 bits.
   */
  public function testTooLongPersonalizer() {
    new HMAC_DRBG('', 256, str_repeat('*', 33));
  }

  /**
   * The number of bits of entropy must be at least 1.5 times the configured
   * strength of the DRBG. (256 -> 384)
   *
   * @expectedException \LengthException
   * @expectedExceptionMessage Entropy must be at least 384 bits.
   */
  public function testEntropyTooShort() {
    new HMAC_DRBG('too short');
  }

  /**
   * @expectedException \LengthException
   * @expectedExceptionMessage Entropy cannot exceed 1000 bits.
   */
  public function testEntropyTooLong() {
    new HMAC_DRBG(str_repeat('*', 128));
  }

  /**
   * The number of bits of entropy must be at least the configured strength
   * of the DRBG.
   *
   * @expectedException \LengthException
   * @expectedExceptionMessage Entropy must be at least 256 bits.
   */
  public function testReseedEntropyTooShort() {
    (new HMAC_DRBG(str_repeat('*', 64)))->reseed('short');
  }

  /**
   * @expectedException \LengthException
   * @expectedExceptionMessage Entropy cannot exceed 1000 bits.
   */
  public function testReseedEntropyTooLong() {
    $entropy = str_repeat('*', 64);
    (new HMAC_DRBG($entropy))->reseed($entropy . $entropy);
  }

  /**
   * @expectedException \InvalidArgumentException
   * @expectedExceptionMessage Cannot generate more than 7500 bits in a single call.
   */
  public function testGenerateTooManyBytes() {
    (new HMAC_DRBG(str_repeat('*', 64)))->generate(8000);
  }

  /**
   * @expectedException \OutOfBoundsException
   * @expectedExceptionMessage Given strength exceeds configured 256 bits.
   */
  public function testGenerateStrengthTooHigh() {
    (new HMAC_DRBG(str_repeat('*', 64)))->generate(32, 512);
  }

  /**
   * @dataProvider vectors
   */
  public function testNIST($count, $entropy, $nonce, $personalizer, $entropy_reseed, $extra_reseed, $extra1, $extra2, $return) {
  }

  protected function toBinary($hex) {
    $arguments = array_map('hexdec', str_split($hex, 2));
    array_unshift($arguments, 'C*');
    return call_user_func_array('pack', $arguments);
  }

  public function vectors() {
    $vectors = $vector = [];

    $file = fopen(__DIR__ . '/vectors.txt', 'r');
    while ($line = fgets($file)) {
      $line = trim($line);

      if ($line) {
        $line = explode(' = ', $line);
        $vector[] = isset($line[1]) ? $line[1] : NULL;
      }
      else {
        array_push($vectors, $vector);
        $vector = [];
      }
    }
    fclose($file);

    return $vectors;
  }

}
