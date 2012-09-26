<?php
namespace klg;
use klg\obfuscator\Cipher;
use klg\obfuscator\Key;

/**
 * Obfuscate UTF-8 strings in a way that allows easy decoding in Javascript
 * while cryptographically forcing potentially misbehaving harvester to make
 * additional request.
 **/
class Obfuscator {

  /**
   * Key used to obfuscate data.
   * @var Key
   **/
  public $key;

  /**
   * Create new Obfuscator with given secret.
   * @param string server secret
   * @param string optional seed used to derive the key
   **/
  public function __construct($serverSecret, $seed = null) {
    $this->key = new Key($serverSecret, $seed);
  }

  /**
   * Obfuscate UTF-8 string by encrypting it.
   * @param string plain text string
   * @return string obfuscated string
   **/
  public function obfuscate($str) {
    return Cipher::encrypt($str, $this->key);
  }

  /**
   * Deobfuscate string.
   * @param string obfuscated string
   * @return string plain text
   **/
  public function deobfuscate($str) {
    return Cipher::decrypt($str, $this->key);
  }

  /**
   * Get the name of Javascript file containing code
   * used to deobfuscate obfuscated strings.
   * @return string file path
   **/
  public function jsPath() {
    $path = dirname(__FILE__) . DIRECTORY_SEPARATOR;
    if (file_exists($path . 'obfuscator.min.js'))
      return $path . 'obfuscator.min.js';
    if (file_exists($path . 'obfuscator.js'))
      return $path . 'obfuscator.js';
    return false;
  }

  /**
   * Get the Javascript code that shall be used
   * to deobfuscate strings at the client side.
   * @return string Javascript code
   **/
  public function jsCode() {
    return file_get_contents($this->jsPath());
  }

  /**
   * Get a Twig extension object.
   * @return \Twig_Extension
   **/
  public function twigExtension() {
    static $ext;
    if (!$ext)
      $ext = new \klg\obfuscator\TwigExtension($this);
    return $ext;
  }
}
?>
