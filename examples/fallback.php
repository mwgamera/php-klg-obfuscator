<?php
namespace example;

require 'common.php';

$seed = htmlspecialchars(@$_REQUEST['seed']);
$href = htmlspecialchars(@$_REQUEST['href']);
$nonce = @$_POST['nonce'];

$auth = hash_hmac('sha1', $seed.$nonce.$href, $secret);

if (strtolower($auth) == strtolower(@$_POST['auth'])):

    $o = new \klg\Obfuscator($secret, $_REQUEST['seed']);
    $href = $o->deobfuscate($_REQUEST['href']);
    $href = htmlspecialchars($href);
    echo "<a href='$href'>$href</a>";

else:

    $rand = new \klg\random\SecureRandom();
    $nonce = $rand->token_base64(40);
    $auth = hash_hmac('sha1', $seed.$nonce.$href, $secret);
    echo <<<EOF
    <form action="" method="POST" enctype="multipart/form-data">
    <input type="hidden" id="seed" name="seed" value="$seed">
    <input type="hidden" id="href" name="href" value="$href">
    <input type="hidden" id="nonce" name="nonce" value="$nonce">
    <input type="hidden" id="auth" name="auth" value="$auth">
    <input type="submit" value="Show email">
    </form>
EOF;
endif;
