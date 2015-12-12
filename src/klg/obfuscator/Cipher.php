<?php
namespace klg\obfuscator;

/**
 * Cipher used to obfuscate textual data.
 * Plaintext is NULL-terminated string of bytes.
 * Cryptogram should contain only characters that require no escaping
 * when used in URL, SGML/XML attribute, or Javascript string literal.
 *
 * @uses XXTEA
 **/
class Cipher
{
    /**
     * Encrypt block of data with given key.
     *
     * @param   string  block of data
     * @param   Key     128-bit key
     * @param   int     initialization vector
     *
     * @return string encrypted data
     **/
    public static function encrypt($str, Key $key, $iv = null)
    {
        // convert to array
        $str = rtrim($str, "\0"); // normalize
        $words = array_values(unpack('N*', $str."\0\0\0"));
        // prepend initialization vector
        array_unshift($words, $iv ?: random_int(0, 0xffffffff));
        // append random length padding
        for ($i = 0; random_int(0, 1) && $i < 5; $i++) {
            array_push($words, 0);
        }
        $words = XXTEA::encrypt($words, $key->raw());
        // encode with base64url
        array_unshift($words, 'N*');
        $str = call_user_func_array('pack', $words);
        $str = rtrim(strtr(base64_encode($str), '+/', '-_'), '=');
        return $str;
    }

    /**
     * Decrypt block of data with given key.
     *
     * @param   string  block of data
     * @param   Key     128-bit key
     *
     * @return string decrypted data
     **/
    public static function decrypt($str, Key $key)
    {
        // decode base64url
        $str = base64_decode(strtr($str, '-_', '+/'));
        $words = array_values(unpack('N*', $str));
        // decrypt
        $words = XXTEA::decrypt($words, $key->raw());
        array_shift($words); // drop IV
        // convert to string
        array_unshift($words, 'N*');
        $str = call_user_func_array('pack', $words);
        return rtrim($str, "\0");
    }
}
