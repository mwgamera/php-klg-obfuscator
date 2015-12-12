<?php
namespace klg\obfuscator;

/**
 * Core of the Corrected Block TEA (XXTEA) cipher.
 **/
class XXTEA
{
    /**
     * Encrypt block of data with given key.
     *
     * @param   int[] block of data
     * @param   int[] 128-bit key
     *
     * @return int[] block of encrypted data
     **/
    public static function encrypt($data, $key)
    {
        $n = count($data);
        $z = $data[$n - 1];
        $q = (int) (6 + 52 / count($data));
        $s = 0;
        while ($q-- > 0) {
            $s = 0xffffffff & ($s + 0x9e3779b9);
            $e = $s >> 2;
            for ($p = 0; $p < $n; $p++) {
                $y = $data[($p + 1) % $n];
                $a = ($z >> 5 & 0x07ffffff) ^ $y << 2;
                $b = ($y >> 3 & 0x1fffffff) ^ $z << 4;
                $a = 0xffffffff & ($a + $b);
                $b = 0xffffffff & (($s ^ $y) + ($key[($p ^ $e) & 3] ^ $z));
                $z = 0xffffffff & ($data[$p] + ($a ^ $b));
                $data[$p] = $z;
            }
        }
        return $data;
    }

    /**
     * Decrypt block of data with given key.
     *
     * @param   int[] block of data
     * @param   int[] 128-bit key
     *
     * @return int[] block of decrypted data
     **/
    public static function decrypt($data, $key)
    {
        $n = count($data);
        $y = $data[0];
        $q = (int) (6 + 52 / count($data));
        $s = 0xffffffff & ($q * 0x9e3779b9);
        while ($q-- > 0) {
            $e = $s >> 2;
            for ($p = $n - 1; $p >= 0; $p--) {
                $z = $data[($n + $p - 1) % $n];
                $a = ($z >> 5 & 0x07ffffff) ^ $y << 2;
                $b = ($y >> 3 & 0x1fffffff) ^ $z << 4;
                $a = 0xffffffff & ($a + $b);
                $b = 0xffffffff & (($s ^ $y) + ($key[($p ^ $e) & 3] ^ $z));
                $y = 0xffffffff & ($data[$p] - ($a ^ $b));
                $data[$p] = $y;
            }
            $s = 0xffffffff & ($s - 0x9e3779b9);
        }
        return $data;
    }
}
