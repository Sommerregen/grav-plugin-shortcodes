<?php
/**
 * ShortcodesTrait
 *
 * This file is part of Grav Shortcodes plugin.
 *
 * Dual licensed under the MIT or GPL Version 3 licenses, see LICENSE.
 * http://benjamin-regler.de/license/
 */

namespace Grav\Plugin\Shortcodes;

/**
 * ShortcodesTrait
 */
trait ShortcodesTrait
{
  /**
   * A Shortcode instance.
   *
   * @var \Grav\Plugin\Shortcodes\Shortcodes
   */
  protected static $shortcodes;

  /**
   * Get the Shortcode instance.
   *
   * @return \Grav\Plugin\Shortcodes
   */
  public static function getShortcodesClass()
  {
    if (!self::$shortcodes) {
      self::$shortcodes = Shortcodes::instance();
    }
    return self::$shortcodes;
  }

  /**
   * Sets a Shortcode instance.
   *
   * @param Grav\Plugin\Shortcodes $shortcodes The Shortcode instance
   */
  public static function setShortcodesClass(Shortcodes $shortcodes)
  {
    self::$shortcodes = $shortcodes;
  }
}
