<?php
namespace klg\obfuscator;

/**
 * Encryption key deterministically derived from random seed
 * and server secret.  The seed shall be randomly generated and
 * given out together with encrypted data to parties that do not
 * know the server secret.
 **/
class Key
{
    /** Number of rounds in key derivation function. */
    const KEY_ROUNDS = 20;

    /** Length of seed string generated. */
    const SEED_LENGTH = 22; // >128-bit

    /** Maximal length of seed. */
    const MAX_SEED_LENGTH = 1024;

    /**
     * Server secret used to derive the key.
     * This should be constant across requests but never disclosed.
     *
     * @var string
     **/
    private $secret;

    /**
     * Random seed used to derive the key.
     *
     * @var string
     **/
    private $seed;

    /**
     * A key derived from seed and server secret.
     *
     * @var int[]
     **/
    private $key;

    /**
     * Construct new key object.
     * If seed is not given, new one will be generated randomly.
     *
     * @param string  server secret
     * @param string  random seed
     **/
    public function __construct($serverSecret, $seed = null)
    {
        $this->secret = (string) $serverSecret;
        $this->seed = $seed;
    }

    /**
     * Get seed used to derive this key.
     * If there is no seed yet, generate a new one.
     *
     * @return string seed used for this key
     **/
    public function getSeed()
    {
        if ($this->seed) {
            return $this->seed;
        }
        $buf = base64_encode(random_bytes((self::SEED_LENGTH + 1) * 3 / 4));
        return $this->seed = strtr(substr($buf, 0, self::SEED_LENGTH), '+/', '-_');
    }

    /**
     * Set seed for this key.
     *
     * @param string  seed to be used with this key
     **/
    public function setSeed($seed)
    {
        $this->seed = (string) $seed;
        $this->key = null;
    }

    /**
     * Get key in raw format usable with XXTEA.
     * Key is an array of 32-bit integers of length 4
     * (128 bits of key material altogether).
     *
     * @return int[] raw key
     **/
    public function raw()
    {
        if ($this->key) {
            return $this->key;
        }
        return $this->key = $this->kdf();
    }

    /**
     * Get key in JSON format usable with Javascript code.
     *
     * @return string key
     **/
    public function json()
    {
        $k = $this->raw();
        return sprintf('[%u,%u,%u,%u]',
            $k[0], $k[1], $k[2], $k[3]);
    }

    /**
     * Key derivation algorithm.
     *
     * @return int[] raw key
     **/
    private function kdf()
    {
        $r = self::KEY_ROUNDS;
        $s = substr($this->getSeed(), 0, self::MAX_SEED_LENGTH);
        $k = $this->secret;
        $v = sha1($s, true);
        if (function_exists('hash_hmac')) {
            while ($r-- > 0) {
                $m = pack('Na20a*', $r, $v, $s);
                $v ^= hash_hmac('sha1', $m, $k, true);
            }
        } else {
            while ($r-- > 0) {
                $m = pack('Na20a*', $r, $v, $s);
                $v ^= self::hmac_sha1($k, $m);
            }
        }
        return array_values(unpack('N4', $v));
    }

    /**
     * Portable implementation of RFC 2104 HMAC-SHA1.
     * Used by kdf if Hash extension is disabled.
     *
     * @param   string  key
     * @param   string  message
     *
     * @return string message authentication code
     **/
    private static function hmac_sha1($key, $msg)
    {
        $ipad = str_repeat(chr(0x36), 64);
        $opad = str_repeat(chr(0x5C), 64);
        if (strlen($key) > 64) {
            $key = sha1($key, true);
        }
        if (strlen($key) < 64) {
            $key = str_pad($key, 64, chr(0));
        }
        $ipad ^= $key;
        $opad ^= $key;
        return sha1($opad.sha1($ipad.$msg, true), true);
    }
}
