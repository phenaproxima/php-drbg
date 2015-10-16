<?php

namespace phenaproxima;

class ByteArrayTest extends \PHPUnit_Framework_TestCase {

  public function testCreateFromString() {
    $this->assertEquals(['4b', '72', '6f', '6e', '6f', '73'], ByteArray::create('Kronos')->toHexArray());
  }

  public function testCreateFromArray() {
    $this->assertEquals('Kronos', ByteArray::create([0x4b, 0x72, 0x6f, 0x6e, 0x6f, 0x73])->toBinaryString());
  }

  public function testCreateFromStatic() {
    $kronos = ByteArray::create('Kronos');
    $this->assertSame($kronos, ByteArray::create($kronos));
  }

  public function testCreateFromUnsupportedType() {
    $this->assertCount(0, ByteArray::create(1));
    $this->assertCount(0, ByteArray::create(TRUE));
    $this->assertCount(0, ByteArray::create(new \StdClass));
  }

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

  public function testToHexString() {
    $this->assertEquals('4b726f6e6f73', ByteArray::create('Kronos')->toHexString());
  }

  public function testPrepend() {
    $this->assertEquals(33, ByteArray::create('Kronos')->prepend('!')->getBytes()[0]);
  }

  public function testAppend() {
    $this->assertEquals('KronosPlease', ByteArray::create('Kronos')->append('Please')->toBinaryString());
  }
  
  public function testCount() {
    $this->assertCount(6, ByteArray::create('Kronos'));
  }

}
