<?php
/**
 * GenericShortcode
 *
 * This file is part of Grav Shortcodes plugin.
 *
 * Dual licensed under the MIT or GPL Version 3 licenses, see LICENSE.
 * http://benjamin-regler.de/license/
 */

namespace Grav\Plugin\Shortcodes\Twig;

/**
 * GenericShortcode
 */
abstract class GenericShortcode
{
  /**
   * Name of the shortcode
   *
   * @var string
   */
  protected $name;

  /**
   * Callable to call for the shortcode.
   *
   * @var callable
   */
  protected $callable;

  /**
   * An array of shortcode options
   *
   * @var array
   */
  protected $options;

  /**
   * An array of arguments to pass to the shortcode callback.
   *
   * @var array
   */
  protected $arguments = [];

  /**
   * Type of the shortcode.
   *
   * @var string
   */
  protected $type;

  /**
   * Constructor.
   *
   * @param string    $name     Name of the shortcode.
   * @param callable  $callable Callable to call for the shortcode.
   * @param string    $type     Type of the shortcode.
   * @param array     $options  An array of shortcode options.
   */
  public function __construct($name, $callable, $type, array $options = [])
  {
    $this->name = $name;
    $this->callable = $callable;
    $this->type = $type;
    $this->options = array_merge([
      'needs_environment' => false,
      'needs_context'     => false,
      'is_safe'           => null,
      'is_safe_callback'  => null,
      'node_class'        => __NAMESPACE__ . '\\NodeExpressionShortcode',
    ], $options);
  }

  /**
   * Get shortcode name
   *
   * @return string The name of the shortcode.
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Get callable
   *
   * @return callable Returns the callable to call for the shortcode.
   */
  public function getCallable()
  {
    return $this->callable;
  }

  /**
   * Get node class.
   *
   * @return string Returns the node class.
   */
  public function getNodeClass()
  {
    return $this->options['node_class'];
  }

  /**
   * Set arguments.
   *
   * @param array $arguments Set the arguments to pass to the shortcode
   *                         callback.
   */
  public function setArguments($arguments)
  {
    $this->arguments = $arguments;
  }

  /**
   * Get the arguments.
   *
   * @return array Return additional arguments of the shortcodes to
   *                      pass to the shortcode callback.
   */
  public function getArguments()
  {
    return $this->arguments;
  }

  /**
   * Get the type of the shortcode.
   *
   * @return string The type of the shortcode.
   */
  public function getType()
  {
    return $this->type;
  }

  /**
   * Returns whether the shortcode needs the Twig environment.
   *
   * @return bool True if the environment is passed to the shortcode
   *              callback, false otherwise.
   */
  public function needsEnvironment()
  {
    return $this->options['needs_environment'];
  }

  /**
   * Returns whether the shortcode needs the Twig context.
   *
   * @return bool True if the context is passed to the shortcode
   *              callback, false otherwise.
   */
  public function needsContext()
  {
    return $this->options['needs_context'];
  }

  /**
   * Get a safe expression from the shortcode callback.
   *
   * @param  \Twig_Node $shortcodeArgs The arguments to pass to the callback.
   * @return mixed                     Returns the result of the callback.
   */
  public function getSafe(\Twig_Node $shortcodeArgs)
  {
    if (null !== $this->options['is_safe']) {
      return $this->options['is_safe'];
    }

    if (null !== $this->options['is_safe_callback']) {
      return call_user_func($this->options['is_safe_callback'], $shortcodeArgs);
    }

    return [];
  }
}
