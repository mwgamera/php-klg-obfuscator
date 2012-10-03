<?php
namespace example;
require 'common.php';

$o = new \klg\Obfuscator($secret);

$twig = new \Twig_Environment(new \Twig_Loader_String);
$twig->addExtension($o->twigExtension());

$template = file_get_contents(__FILE__);
$template = ltrim(substr($template, 2+strpos($template, '?>', __COMPILER_HALT_OFFSET__)));

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
      'mail' => 'baz@example.com'
    )
  )
));

__halt_compiler();
?>

{% macro fallback(address) %}
{# No-Javascript fallback URL address #}
fallback.php?seed={{- obfuscator.seed -}}&amp;href={{- ("mailto:" ~ address)|obfuscate -}}
{%- endmacro -%}

{% macro mailto(address, name) %}
<a href="{{ _self.fallback(address) }}" onmouseover="klg.obfuscator.href(this)">{{ name }}</a>
{%- if obfuscator.loaded == false %}
<script type="application/ecmascript" defer="defer"{#
  #} src="script.php?seed={{ obfuscator.seed }}"></script>
{%- do obfuscator.loaded(true) %}
{% endif %}
{% endmacro -%}

<!doctype html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>Obfuscator example</title>
  </head>
  <body>
    <p>
    Source should be self-explanatory.
    Hover the mouse pointer over one of those:
    </p>
    <ul>
      {% for user in people %}
      <li>{{ _self.mailto(user.mail, user.name) }}</li>
      {% endfor %}
    </ul>
  </body>
</html>
