<?php
namespace klg\obfuscator;

/**
 * Serve as Twig global variable representing sate of
 * the Obfuscator as used by TwigExtension.
 **/
class StateTwigGlobal {
  /**
   * Reference to the Obfuscator.
   * @var \klg\Obfuscator
   **/
  private $o;

  /**
   * Constructor.
   * @param \klg\Obfuscator Obfuscator to use
   **/
  public function __construct(\klg\Obfuscator $o) {
    $this->o = $o;
  }

  /**
   * Get the seed.
   * @return string random seed
   **/
  public function seed() {
    return $this->o->key->getSeed();
  }

  /**
   * Get derived key.
   * Returned value is a raw array which should be piped through
   * json_encode filter before sending it to Javascript.
   * @return integer[] key used by Obfuscator
   **/
  public function key() {
    return $this->o->key->raw();
  }

  /**
   * Simplify tracking inclusion of deobfuscating script.
   * Returns fales until called with argument true.
   * @param boolean true when including script
   * @return boolean if script was included
   **/
  public function loaded($p = false) {
    static $loaded = false;
    if ($p)
      $loaded = true;
    return $loaded;
  }
}
