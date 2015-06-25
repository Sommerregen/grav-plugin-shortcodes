<?php
/**
 * ShortcodeInterface
 *
 * This file is part of Grav Shortcodes plugin.
 *
 * Dual licensed under the MIT or GPL Version 3 licenses, see LICENSE.
 * http://benjamin-regler.de/license/
 */

namespace Grav\Plugin\Shortcodes;

use RocketTheme\Toolbox\Event\Event;

/**
 * ShortcodeInterface
 *
 * Interface for shortcodes.
 */
interface ShortcodeInterface
{
  /**
   * Execute shortcode.
   *
   * @param  Event        $event An event object.
   * @return string|null         Return modified contents.
   */
  public function execute(Event $event);

  /**
   * Get informations about the shortcode.
   *
   * @return array An associative array needed to register the shortcode.
   */
  public function getShortcode();
}
