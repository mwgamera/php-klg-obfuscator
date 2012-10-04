<?php
use klg\obfuscator\Key;

class KeyTest extends \PHPUnit_Framework_TestCase {
  /**
   * Test variability of random seeds.
   **/
  public function testVarSeed($n = 50) {
    $a = array();
    for ($i = 0; $i < $n; $i++) {
      $k = new Key('');
      $a[$k->getSeed()] = 1;
    }
    // almost certainly all shall be distinct
    $this->assertCount($n, $a);
  }

  /**
   * Test variability of keys from subsequent secrets.
   **/
 public function testVarSecret($n = 50) {
    $a = array();
    for ($i = 0; $i < $n; $i++) {
      $k = new Key($i, 'constant seed');
      $a[serialize($k->raw())] = 1;
    }
    // almost certainly all shall be distinct
    $this->assertCount($n, $a);
  }

  /**
   * Test variability of keys from subsequent seeds.
   **/
  public function testVarKeys($n = 50) {
    $a = array();
    for ($i = 0; $i < $n; $i++) {
      $k = new Key('', $i);
      $a[serialize($k->raw())] = 1;
    }
    // almost certainly all shall be distinct
    $this->assertCount($n, $a);
  }

  /**
   * Test consistence.
   **/
  public function testStable($n = 50) {
    $x = array();
    while ($n-- > 0) {
      $a = new Key('a');
      $x[$a->getSeed()] = $a->raw();
    }
    $a = new Key('a');
    $b = new Key('b');
    foreach ($x as $s => $k) {
      $a->setSeed($s);
      $b->setSeed($s);
      $this->assertEquals($k, $a->raw());
      $this->assertNotEquals($k, $b->raw());
    }
  }
}
