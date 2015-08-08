<?php
/**
 * Environment
 *
 * This file is part of Grav Shortcodes plugin.
 *
 * Dual licensed under the MIT or GPL Version 3 licenses, see LICENSE.
 * http://benjamin-regler.de/license/
 */

namespace Grav\Plugin\Shortcodes\Twig;

/**
 * Environment
 */
class Environment extends \Twig_Environment
{
    /**
     * Array of shortcodes
     */
    protected $shortcodes;

    /**
     * Shortcode callback
     */
    protected $shortcodeCallbacks;

    /**
     * Constructor.
     *
     * @param Twig_LoaderInterface $loader  A Twig_LoaderInterface instance
     * @param array                $options An array of options
     */
    public function __construct(\Twig_LoaderInterface $loader = null, $options = array())
    {
       parent::__construct($loader, $options);

       $this->shortcodeCallbacks = array();

        // Overwrite internal staging class
       $this->staging = new Staging();
    }

    /**
     * Registers a Shortcode.
     *
     * @param string|GenericShortcode   $name      The shortcode name or a
     *                                             GenericShortcode instance
     * @param GenericShortcode          $shortcode A GenericShortcode instance
     */
    public function addShortcode($name, $shortcode = null)
    {
        if (!$name instanceof GenericShortcode && !($shortcode instanceof GenericShortcode)) {
            throw new \LogicException('A shortcode must be an instance of GenericShortcode');
        }

        if ($name instanceof GenericShortcode) {
            $shortcode = $name;
            $name = $shortcode->getName();
        }

        if ($this->extensionInitialized) {
            throw new \LogicException(sprintf('Unable to add shortcode "%s" as extensions have already been initialized.', $name));
        }

        $this->staging->addShortcode($name, $shortcode);

        // No kidding. Each shortcode also registers a (generic) TokenParser...
	   $this->staging->addTokenParser(new ShortcodeTokenParser($name, $shortcode));
    }

    /**
     * Sets the Lexer instance.
     *
     * @param \Twig_LexerInterface $lexer A Twig_LexerInterface instance
     */
    public function setLexer(\Twig_LexerInterface $lexer)
    {
        $this->lexer = $lexer;
        // Bugfix: Reset extensionInitialized
        $this->extensionInitialized = false;
    }

    /**
     * Get a shortcode by name.
     *
     * Subclasses may override this method and load shortcodes differently;
     * so no list of shortcodes is available.
     *
     * @param string                  $name     Shortcode name
     *
     * @return GenericShortcode|false           A GenericShortcode instance
     *                                          or false if the function
     *                                          does not exist
     */
    public function getShortcode($name)
    {
        if (!$this->extensionInitialized) {
            $this->initExtensions();
        }

        if (isset($this->shortcodes[$name])) {
            return $this->shortcodes[$name];
        }

        foreach ($this->shortcodes as $pattern => $shortcode) {
            $pattern = str_replace('\\*', '(.*?)', preg_quote($pattern, '#'), $count);

            if ($count) {
                if (preg_match('#^'.$pattern.'$#', $name, $matches)) {
                    array_shift($matches);
                    $shortcode->setArguments($matches);

                    return $shortcode;
                }
            }
        }

        foreach ($this->shortcodeCallbacks as $callback) {
            if (false !== $shortcode = call_user_func($callback, $name)) {
                return $shortcode;
            }
        }

        return false;
    }

    /**
     * Register undefined Shortcode callback.
     *
     * @param  callable $callable The callable to call for unknown shortcodes.
     */
    public function registerUndefinedShortcodeCallback($callable)
    {
        $this->shortcodeCallbacks[] = $callable;
    }

    /**
     * Gets registered shortcodes.
     *
     * @return GenericShortcode[] An array of GenericShortcode instances
     */
    public function getShortcodes()
    {
        if (!$this->extensionInitialized) {
            $this->initExtensions();
        }

        return $this->shortcodes;
    }

    /**
     * Initialize extensions
     */
    protected function initExtensions()
    {
        if ($this->extensionInitialized) {
            return;
        }

        $this->shortcodes = array();

        // Calls $this->initExtension($this->staging);
        parent::initExtensions();
    }

    /**
     * Initialize extension
     *
     * @param  \Twig_ExtensionInterface $extension The Twig extension to
     *                                             initialize.
     */
    protected function initExtension(\Twig_ExtensionInterface $extension)
    {
        parent::initExtension($extension);

        // shortcodes
        if ($extension instanceof ExtensionInterface) {
            foreach ($extension->getShortcodes() as $name => $shortcode) {
                if ($name instanceof GenericShortcode) {
                    $shortcode = $name;
                    $name = $shortcode->getName();
                } elseif ($shortcode instanceof GenericShortcode) {
                    $name = $shortcode->getName();
                }

                $this->shortcodes[$name] = $shortcode;
            }
        }
    }
}
