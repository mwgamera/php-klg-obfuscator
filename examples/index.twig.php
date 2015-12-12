<?php
namespace example;

require 'common.php';

$o = new \klg\Obfuscator($secret);

$twig = new \Twig_Environment(new \Twig_Loader_String());
$twig->addExtension($o->twigExtension());

$template = file_get_contents(__DIR__.'/template.twig');

echo $twig->render($template, array(
    'people' => array(
        array(
            'name' => 'user1',
            'mail' => 'foo@example.org',
        ),
        array(
            'name' => 'user2',
            'mail' => 'bar@example.net',
        ),
        array(
            'name' => 'user3',
            'mail' => 'baz@example.com',
        ),
    ),
));
