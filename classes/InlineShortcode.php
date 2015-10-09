<?php
/**
 * InlineShortcode
 *
 * This file is part of Grav Shortcodes plugin.
 *
 * Dual licensed under the MIT or GPL Version 3 licenses, see LICENSE.
 * http://benjamin-regler.de/license/
 */

namespace Grav\Plugin\Shortcodes;

/**
 * InlineShortcode
 */
class InlineShortcode extends Twig\GenericShortcode
{
  /**
   * Constructor
   *
   * @param string    $name     Name of the shortcode.
   * @param callable  $callable Callable to call for the shortcode.
   * @param array     $options  An array of shortcode options.
   */
  public function __construct($name, $callable, array $options = [])
  {
    parent::__construct($name, $callable, 'inline', $options);
  }
}
