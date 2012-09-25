<?php
namespace klg\obfuscator;

/**
 * Cipher used to obfuscate textual data.
 * Plaintext is NULL-terminated string of bytes.
 * Cryptogram should contain only characters that require no escaping
 * when used in URL, SGML/XML attribute, or Javascript string literal.
 * @uses XXTEA
 **/
class Cipher {
  /**
   * Encrypt block of data with given key.
   * @param   string  block of data
   * @param   Key     128-bit key
   * @param   integer initialization vector
   * @return  string  encrypted data
   **/
  public static function encrypt($str, Key $key, $iv = null) {
    static $rand = null;
    if (!$rand)
      $rand = new \klg\random\SecureRandom;
    // convert to array
    $str = rtrim($str, "\0"); // normalize
    $words = array_values(unpack('N*', $str . "\0\0\0"));
    // prepend initialization vector
    $iv = $iv ?: $rand->get_integer();
    array_unshift($words, $iv ?: $rand->get_integer());
    // append random length padding
    $rand->php_reseed();
    for ($i = 0; mt_rand(0,1) && $i < 5; $i++)
      array_push($words, 0);
    $words = XXTEA::encrypt($words, $key->raw());
    // encode with base64url
    array_unshift($words, 'N*');
    $str = call_user_func_array('pack', $words);
    $str = base64_encode($str);
    $str = str_replace(
      array('+','/','='),
      array('-','_',''), $str);
    return $str;
  }

  /**
   * Decrypt block of data with given key.
   * @param   string  block of data
   * @param   Key     128-bit key
   * @return  string  decrypted data
   **/
  public static function decrypt($str, Key $key) {
    // decode base64url
    $str = str_replace(array('-','_'), array('+','/'), $str);
    $str = base64_decode($str);
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
?>
