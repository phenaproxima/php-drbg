<?php

namespace phenaproxima;

/**
 * @coversDefaultClass \phenaproxima\HMAC_DRBG
 */
class HMAC_DRBGTest extends \PHPUnit_Framework_TestCase {

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
   * @dataProvider nist
   */
  public function testNIST($count, $entropy_input, $nonce, $personalization, $entropy_input_reseed, $additional_input_reseed, $additional_input_1, $additional_input_2, $returned_bits) {
    // The DRBG implementation doesn't support additional input.
    if ($additional_input_reseed || $additional_input_1 || $additional_input_2) {
      $this->markTestSkipped();
    }

    // Decode all the input arguments into binary strings.
    $entropy_input = $this->decodeHex($entropy_input);
    $nonce = $this->decodeHex($nonce);
    $personalization = $this->decodeHex($personalization);
    $entropy_input_reseed = $this->decodeHex($entropy_input_reseed);
    $returned_bits = $this->decodeHex($returned_bits);

    $length = strlen($returned_bits);
    $drbg = new HMAC_DRBG($entropy_input . $nonce, 256, $personalization);
    $drbg->reseed($entropy_input_reseed);
    $drbg->generate($length);
    $hash = $drbg->generate($length);

    $this->assertEquals($returned_bits, substr($hash, 0, $length));
  }

  protected function decodeHex($hex) {
    if ($hex) {
      $arguments = array_map('hexdec', str_split($hex, 2));
      array_unshift($arguments, 'C*');
      return call_user_func_array('pack', $arguments);
    }
  }

  public function nist() {
    return json_decode(file_get_contents(__DIR__ . '/vectors.json'));
  }

}
