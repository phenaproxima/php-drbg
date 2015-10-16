<?php

namespace phenaproxima;

/**
 * @coversDefaultClass \phenaproxima\ByteArray
 */
class ByteArrayTest extends \PHPUnit_Framework_TestCase {

  /**
   * @covers ::create
   */
  public function testCreateFromString() {
    $this->assertEquals('Kronos', ByteArray::create('Kronos')->toBinaryString());
  }

  /**
   * @covers ::create
   */
  public function testCreateFromArray() {
    $this->assertEquals('Kronos', ByteArray::create([0x4b, 0x72, 0x6f, 0x6e, 0x6f, 0x73])->toBinaryString());
  }

  /**
   * @covers ::create
   */
  public function testCreateFromStatic() {
    $kronos = ByteArray::create('Kronos');
    $this->assertSame($kronos, ByteArray::create($kronos));
  }

  /**
   * @covers ::create
   */
  public function testCreateFromUnsupportedType() {
    $this->assertCount(0, ByteArray::create(1));
    $this->assertCount(0, ByteArray::create(TRUE));
    $this->assertCount(0, ByteArray::create(new \StdClass));
  }

  /**
   * @covers ::getBytes
   */
  public function testGetBytes() {
    $bytes = [
      1 => 0x4b,
      2 => 0x72,
      3 => 0x6f,
      4 => 0x6e,
      5 => 0x6f,
      6 => 0x73,
    ];
    $this->assertEquals([75, 114, 111, 110, 111, 115], (new ByteArray($bytes))->getBytes());
  }

  /**
   * @covers ::toHexArray
   */
  public function testToHexArray() {
    $this->assertEquals(['4b', '72', '6f', '6e', '6f', '73'], ByteArray::create('Kronos')->toHexArray());
  }

  /**
   * @covers ::toHexString
   */
  public function testToHexString() {
    $this->assertEquals('4b726f6e6f73', ByteArray::create('Kronos')->toHexString());
  }

  /**
   * @covers ::prepend
   */
  public function testPrepend() {
    $this->assertEquals('!?Kronos', ByteArray::create('Kronos')->prepend('!', '?')->toBinaryString());
  }

  /**
   * @covers ::append
   */
  public function testAppend() {
    $this->assertEquals('KronosPleaseSir', ByteArray::create('Kronos')->append('Please', 'Sir')->toBinaryString());
  }

  /**
   * @covers ::count
   */
  public function testCount() {
    $this->assertCount(6, ByteArray::create('Kronos'));
  }

}
