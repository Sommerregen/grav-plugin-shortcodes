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
   * Shortcode callbacks
   */
  protected $shortcodeCallbacks;

  /**
   * Array of shortcode filers
   */
  protected $shortcodeFilters;

  /**
   * Shortcode filter callbacks
   */
  protected $shortcodeFilterCallbacks;

  /**
   * Constructor.
   *
   * @param Twig_LoaderInterface $loader  A Twig_LoaderInterface instance
   * @param array                $options An array of options
   */
  public function __construct(\Twig_LoaderInterface $loader = null, $options = [])
  {
    parent::__construct($loader, $options);

    $this->shortcodeCallbacks = [];
    $this->shortcodeFilterCallbacks = [];

    // Overwrite internal staging class
    $this->staging = new Staging();
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
      if (false !== ($shortcode = call_user_func($callback, $name))) {
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
   * Registers a Shortcode.
   *
   * @param string|GenericShortcodeFilter  $name  The shortcode filter name
   *                                              or a GenericShortcodeFilter
   *                                              instance
   * @param GenericShortcodeFilter         $shortcode A GenericShortcodeFilter
   *                                                  instance
   */
  public function addShortcodeFilter($name, $shortcodeFilter = null)
  {
    if (!$name instanceof GenericShortcodeFilter && !($shortcodeFilter instanceof GenericShortcodeFilter)) {
      throw new \LogicException('A shortcode filter must be an instance of GenericShortcodeFilter');
    }

    if ($name instanceof GenericShortcodeFilter) {
      $shortcodeFilter = $name;
      $name = $shortcodeFilter->getName();
    }

    if ($this->extensionInitialized) {
      throw new \LogicException(sprintf('Unable to add shortcode filter "%s" as extensions have already been initialized.', $name));
    }

    $this->staging->addShortcodeFilter($name, $shortcodeFilter);
  }

  /**
   * Get a shortcode filter by name.
   *
   * Subclasses may override this method and load shortcode filters
   * differently; so no list of shortcode filters is available.
   *
   * @param string                  $name     Shortcode filter name
   *
   * @return GenericShortcodeFilter|false     A GenericShortcodeFilter
   *                                          instance or false if the
   *                                          filter does not exist
   */
  public function getShortcodeFilter($name)
  {
    if (!$this->extensionInitialized) {
      $this->initExtensions();
    }

    if (isset($this->shortcodeFilters[$name])) {
      return $this->shortcodeFilters[$name];
    }

    foreach ($this->shortcodeFilters as $pattern => $shortcodeFilter) {
      $pattern = str_replace('\\*', '(.*?)', preg_quote($pattern, '#'), $count);

      if ($count) {
        if (preg_match('#^'.$pattern.'$#', $name, $matches)) {
          array_shift($matches);
          foreach ($shortcodeFilter as $filter) {
            $filter->setArguments($matches);
          }

          return $shortcodeFilter;
        }
      }
    }

    foreach ($this->shortcodeFilterCallbacks as $callback) {
      if (false !== ($shortcodeFilter = call_user_func($callback, $name))) {
        return $shortcodeFilter;
      }
    }

    return false;
  }

  /**
   * Register undefined Shortcode filter callback.
   *
   * @param  callable $callable The callable to call for unknown
   *                            shortcode filters.
   */
  public function registerUndefinedShortcodeFilterCallback($callable)
  {
    $this->shortcodeFilterCallbacks[] = $callable;
  }

  /**
   * Gets registered shortcode filters.
   *
   * @return GenericShortcodeFilter[] An array of GenericShortcodeFilter
   *                                  instances
   */
  public function getShortcodeFilters()
  {
    if (!$this->extensionInitialized) {
      $this->initExtensions();
    }

    return $this->shortcodeFilters;
  }

  /**
   * Initialize extensions
   */
  protected function initExtensions()
  {
    if ($this->extensionInitialized) {
      return;
    }

    $this->shortcodes = [];
    $this->shortcodeFilters = [];

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
      // Shortcodes (block and inline)
      foreach ($extension->getShortcodes() as $name => $shortcode) {
        if ($name instanceof GenericShortcode) {
          $shortcode = $name;
          $name = $shortcode->getName();
        } elseif ($shortcode instanceof GenericShortcode) {
          $name = $shortcode->getName();
        }

        $this->shortcodes[$name] = $shortcode;
      }

      // Shortcode filters
      foreach ($extension->getShortcodeFilters() as $name => $shortcodeFilter) {
        if ($name instanceof GenericShortcodeFilter) {
          $shortcodeFilter = [$name];
          $name = $shortcodeFilter->getName();
        } elseif ($shortcodeFilter instanceof GenericShortcodeFilter) {
          $name = $shortcodeFilter->getName();
        } else {
          // List of shortcode filters to be sorted
          usort($shortcodeFilter, function($a, $b) {
            $priority = 0;
            if ($a->getPriority() > $b->getPriority()) {
              $priority = 1;
            } elseif ($a->getPriority() < $b->getPriority()) {
              $priority = -1;
            }

            // Higher priorities are executed first
            return $priority;
          });

          // Reverse to match registering order of shortcode filters
          $shortcodeFilter = array_reverse($shortcodeFilter);
        }

        $this->shortcodeFilters[$name] = $shortcodeFilter;
      }
    }
  }
}
