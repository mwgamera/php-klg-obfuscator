<?php
use klg\obfuscator\XXTEA;

class XXTEATest extends \PHPUnit_Framework_TestCase {
  /** Number of tests to perform with random data.  */
  const TEST_RANDOM = 64;

  /**
   * Test invertibility of encryption.
   * @dataProvider randomProvider
   **/
  public function testInverse($key, $message) {
    $x = XXTEA::encrypt($message, $key);
    $y = XXTEA::decrypt($x, $key);
    $this->assertEquals($message, $y);
  }

  /**
   * Encryption test against reference data.
   * @dataProvider referenceProvider
   **/
  public function testEncrypt($key, $plaintext, $ciphertext) {
    $x = XXTEA::encrypt($plaintext, $key);
    $this->assertEquals($x, $ciphertext);
  }

  /**
   * Decryption test against reference data.
   * @dataProvider referenceProvider
   **/
  public function testDecrypt($key, $plaintext, $ciphertext) {
    $x = XXTEA::decrypt($ciphertext, $key);
    $this->assertEquals($x, $plaintext);
  }

  /**
   * Random provider for fuzzing.
   **/
  public function randomProvider() {
    $data = array();
    for ($i = 0; $i < self::TEST_RANDOM; $i++) {
      $key = array();
      $msg = array();
      for ($k = 0; $k < 4; $k++)
        $key[$k] = mt_rand(0, 0xffffffff);
      $len = mt_rand(2,128);
      for ($k = 0; $k < $len; $k++)
        $msg[$k] = mt_rand(0, 0xffffffff);
      $data[] = array($key, $msg);
    }
    return $data;
  }

  /**
   * Provider redaing data from file that contains test vectors
   * generated with reference implementation as given in
   * Wheeler & Needham "Correction to XTEA" (2008).
   **/
  public function referenceProvider() {
    static $data = false;
    if (!$data)
      $data = require dirname(__FILE__).'/XXTEATestData.phar';
    return $data;
  }
}
