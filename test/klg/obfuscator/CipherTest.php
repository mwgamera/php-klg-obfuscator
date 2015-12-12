<?php
use klg\obfuscator\Cipher;
use klg\obfuscator\Key;

class CipherTest extends \PHPUnit_Framework_TestCase
{
    /** Number of cases provided by randomProvider  */
    const TEST_RANDOM = 64;

    /**
     * Test invertibility of encryption and syntax of cryptogram.
     *
     * @dataProvider randomProvider
     **/
    public function testInverse($msg, $key, $iv)
    {
        $x = Cipher::encrypt($msg, $key, $iv);
        $this->assertRegExp('/^[^!#$&\'()*+,\/:;=?@\[\]]+$/', $x);
        $y = Cipher::decrypt($x, $key);
        $this->assertEquals($msg, $y);
    }

    /**
     * Test variability of cryptogram.
     *
     * @dataProvider randomProvider
     **/
    public function testVar($msg, $key, $iv)
    {
        $x1 = Cipher::encrypt($msg, $key);
        $x2 = Cipher::encrypt($msg, $key);
        $x3 = Cipher::encrypt($msg, $key, $iv);
        $key->setSeed($msg);
        $x4 = Cipher::encrypt($msg, $key, $iv);
        // almost certainly shall be distinct
        $x = array($x1 => 1, $x2 => 1, $x3 => 1, $x4 => 1);
        $this->assertCount(4, $x);
    }

    /**
     * Random provider.
     **/
    public function randomProvider()
    {
        static $data = null;
        if ($data) {
            return $data;
        }
        $data = array();
        for ($i = 0; $i < self::TEST_RANDOM; $i++) {
            $msg = array();
            $iv = mt_rand();
            $len = 1 + mt_rand(1, 256);
            $msg[0] = 'C*';
            for ($k = 1; $k < $len; $k++) {
                $msg[$k] = mt_rand(1, 0xff);
            } // not zero!
            $msg = call_user_func_array('pack', $msg);
            $data[] = array($msg, new Key(''), $iv);
        }
        return $data;
    }
}
