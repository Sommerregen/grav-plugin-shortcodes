<?php
/**
 * ExtensionInterface
 *
 * This file is part of Grav Shortcodes plugin.
 *
 * Dual licensed under the MIT or GPL Version 3 licenses, see LICENSE.
 * http://benjamin-regler.de/license/
 */

namespace Grav\Plugin\Shortcodes\Twig;

/**
 * ExtensionInterface
 */
interface ExtensionInterface extends \Twig_ExtensionInterface
{
  /**
   * Returns a list of shortcodes to add to the existing list.
   *
   * @return array An array of shortcodes
   */
  public function getShortcodes();

  /**
   * Returns a list of shortcode filters to add to the existing list.
   *
   * @return array An array of shortcode filters
   */
  public function getShortcodeFilters();
}
