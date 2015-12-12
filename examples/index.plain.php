<?php
namespace example;

require 'common.php';

$o = new \klg\Obfuscator($secret);

?>
<!doctype html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>Obfuscator example</title>
    <script type="text/ecmascript" defer="defer"
      src="script.php?seed=<?php echo $o->key->getSeed(); ?>"></script>
  </head>
  <body>
    <p>
    Source should be self-explanatory.
    Hover the mouse pointer over
    <a href="javascript:alert('Error!')"
      onmouseover="klg.obfuscator.href(this)"
      data-xhref="<?php echo $o->obfuscate('mailto:foo@example.com'); ?>">this</a>
    or <a onmouseover="klg.obfuscator.href(this)"
      href="fallback.php?seed=<?php echo $o->key->getSeed().'&amp;href='.
        $o->obfuscate('mailto:bar@example.net'); ?>">this</a>
    link to see obfuscator in action.
    </p>
  </body>
</html>
