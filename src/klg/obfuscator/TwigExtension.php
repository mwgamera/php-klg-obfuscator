<?php
namespace klg\obfuscator;

/**
 * Extension for Twig templating engine.
 * Simplifies useing obfuscation with Twig.
 **/
class TwigExtension extends \Twig_Extension
{
    /**
     * Obfuscator object used by this extension.
     *
     * @var \klg\Obfuscator
     **/
    private $o;

    /**
     * Construct new instance of extension using given Obfuscator.
     *
     * @param \klg\Obfuscator Obfuscator to use
     **/
    public function __construct(\klg\Obfuscator $o)
    {
        $this->o = $o;
    }

    /**
     * Returns a list of global variables to add to the existing list.
     * This extension defines one global variable, i.e. obfuscator,
     * that represents state of the Obfuscator in use.
     *
     * @return array An array of global variables
     **/
    public function getGlobals()
    {
        $o = $this->o;
        return array(
            'obfuscator' => new StateTwigGlobal($this->o),
        );
    }

    /**
     * Returns a list of filters to add to the existing list.
     * This extension defines two filters, i.e. obfuscate
     * and deobfuscate.
     *
     * @return array An array of filters
     **/
    public function getFilters()
    {
        return array(
            'obfuscate' => new \Twig_Filter_Method(
                $this, 'obfuscateFilter', array(
                    'needs_environment' => true,
                    'is_safe' => array('html'),
                )
            ),
            'deobfuscate' => new \Twig_Filter_Method(
                $this, 'deobfuscateFilter', array(
                    'needs_environment' => true,
                )
            ),
        );
    }

    /**
     * Obfuscation filter.
     **/
    public function obfuscateFilter(\Twig_Environment $env, $value)
    {
        if (function_exists('mb_convert_encoding')) {
            $value = mb_convert_encoding($value, 'UTF-8', $env->getCharset());
        }
        return $this->o->obfuscate($value);
    }

    /**
     * Deobfuscation filter.
     **/
    public function deobfuscateFilter(\Twig_Environment $env, $value)
    {
        $value = $this->o->deobfuscate($value);
        if (function_exists('mb_convert_encoding')) {
            $value = mb_convert_encoding($value, $env->getCharset(), 'UTF-8');
        }
        return $value;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string the extension name
     **/
    public function getName()
    {
        return 'Obfuscator';
    }
}
