<?php
/**
 * GenericShortcodeFilter
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
abstract class GenericShortcodeFilter
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
   * Constructor.
   *
   * @param string    $name     Name of the shortcode filter .
   * @param callable  $callable Callable to call for the shortcode filter.
   * @param array     $options  An array of shortcode filter options.
   */
  public function __construct($name, $callable, array $options = [])
  {
    $this->name = $name;
    $this->callable = $callable;
    $this->options = array_merge([
      'needs_environment' => false,
      'needs_context'     => false,
      'is_safe'           => null,
      'is_safe_callback'  => null,
      'pre_escape'        => null,
      'preserves_safety'  => null,
      'node_class'        => __NAMESPACE__ . '\\NodeExpressionShortcode',
      'priority'          => 0
    ], $options);
  }

  /**
   * Get shortcode filter name
   *
   * @return string The name of the shortcode filter.
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Get callable
   *
   * @return callable Returns the callable to call for the shortcode filter.
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
   *                         filter callback.
   */
  public function setArguments($arguments)
  {
    $this->arguments = $arguments;
  }

  /**
   * Get the arguments.
   *
   * @return array Return additional arguments of the shortcodes filter
   *               to pass to the shortcode filter callback.
   */
  public function getArguments()
  {
    return $this->arguments;
  }

  /**
   * Returns whether the shortcode filter needs the Twig environment.
   *
   * @return bool True if the environment is passed to the shortcode filter
   *              callback, false otherwise.
   */
  public function needsEnvironment()
  {
    return $this->options['needs_environment'];
  }

  /**
   * Returns whether the shortcode filter needs the Twig context.
   *
   * @return bool True if the context is passed to the shortcode filter
   *              callback, false otherwise.
   */
  public function needsContext()
  {
    return $this->options['needs_context'];
  }

  /**
   * Get a safe expression from the shortcode filter callback.
   *
   * @param  \Twig_Node $shortcodeFilterArgs The arguments to pass to the
   *                                         callback.
   * @return mixed                           Returns the result of the
   *                                         callback.
   */
  public function getSafe(\Twig_Node $shortcodeFilterArgs)
  {
    if (null !== $this->options['is_safe']) {
      return $this->options['is_safe'];
    }

    if (null !== $this->options['is_safe_callback']) {
      return call_user_func($this->options['is_safe_callback'], $shortcodeFilterArgs);
    }

    return [];
  }

  /**
   * Returns whether the output after the transformation is a safe
   * expression.
   *
   * @return boolean TRUE if filter preserves the safety input, FALSE
   *                 otherwise.
   */
  public function getPreservesSafety()
  {
    return $this->options['preserves_safety'];
  }

  /**
   * Returns whether the filter pre-escapes the input before applying
   * the transformation.
   *
   * @return boolean TRUE if filter pre-escapes the input, FALSE otherwise.
   */
  public function getPreEscape()
  {
    return $this->options['pre_escape'];
  }

  /**
   * Get the priority of the filter in the filter chain when applying
   * the transformation.
   *
   * @return int  The priority.
   */
  public function getPriority()
  {
    return $this->options['priority'];
  }
}
