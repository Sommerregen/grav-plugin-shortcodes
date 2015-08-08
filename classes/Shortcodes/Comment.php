<?php
/**
 * Comment
 *
 * This file is part of Grav Shortcodes plugin.
 *
 * Dual licensed under the MIT or GPL Version 3 licenses, see LICENSE.
 * http://benjamin-regler.de/license/
 */

namespace Grav\Plugin\Shortcodes\Shortcodes;

use RocketTheme\Toolbox\Event\Event;
use Grav\Plugin\Shortcodes\Shortcode;

/**
 * Comment
 *
 * Comment allows you to use comments and annotations in a markdown
 * document without being outputted to the user.
 */
class Comment extends Shortcode
{
  /**
   * Get informations about the shortcode.
   *
   * @return array An associative array needed to register the shortcode.
   */
  public function getShortcode()
  {
    return ['name' => 'comment', 'type' => 'block'];
  }

  /**
   * Execute shortcode.
   *
   * @param  Event        $event An event object.
   * @return string|null         Return modified contents.
   */
  public function execute(Event $event)
  {
    // Empty (ignore all body content)
    return '';
  }
}
