<?php
namespace example;
require 'common.php';

$o = new \klg\Obfuscator($secret, $_REQUEST['seed']);

header('Content-type: text/ecmascript');
echo $o->jsCode();
echo 'klg.obfuscator.key='. json_encode($o->key->raw());
?>
