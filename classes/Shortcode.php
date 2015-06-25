<?php
/**
 * Shortcode
 *
 * This file is part of Grav Shortcodes plugin.
 *
 * Dual licensed under the MIT or GPL Version 3 licenses, see LICENSE.
 * http://benjamin-regler.de/license/
 */

namespace Grav\Plugin\Shortcodes;

use Grav\Plugin\Shortcodes\ShortcodeInterface;

/**
 * Shortcode
 *
 * The base class for all shortcodes.
 */
abstract class Shortcode implements ShortcodeInterface
{
  /**
   * @var array
   */
  protected $defaults;

  /**
   * Constructor
   *
   * @param array $config An array of default values.
   */
  public function __construct($defaults = []) {
    $this->defaults = $defaults;
  }
}
