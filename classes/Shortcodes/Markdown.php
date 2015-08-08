<?php
/**
 * Markdown
 *
 * This file is part of Grav Shortcodes plugin.
 *
 * Dual licensed under the MIT or GPL Version 3 licenses, see LICENSE.
 * http://benjamin-regler.de/license/
 */

namespace Grav\Plugin\Shortcodes\Shortcodes;

use Grav\Common\Markdown\Parsedown;
use Grav\Common\Markdown\ParsedownExtra;

use RocketTheme\Toolbox\Event\Event;
use Grav\Plugin\Shortcodes\Shortcode;

/**
 * Markdown
 *
 * Markdown is a shortcut to parse texts using Markdown syntax in a document.
 */
class Markdown extends Shortcode
{
  /**
   * Get informations about the shortcode.
   *
   * @return array An associative array needed to register the shortcode.
   */
  public function getShortcode()
  {
    return ['name' => 'markdown', 'type' => 'block'];
  }

  /**
   * Execute shortcode.
   *
   * @param  Event        $event An event object.
   * @return string|null         Return modified contents.
   */
  public function execute(Event $event)
  {
    /* @var \Grav\Common\Data\Data $options */
    $options = $event['options'];

    /* @var \Grav\Common\Grav $grav */
    $grav = $event['grav'];

    /* @var \Grav\Common\Page\Page $page */
    $page = $event['page'];

    /* @var Config $config */
    $config = $grav['config'];

    $body = trim($event['body']);

    // Mimic \Grav\Common\Page\Page processMarkdown() method
    $defaults = (array) $config->get('system.pages.markdown');
    $defaults = array_merge_recursive($defaults, $this->defaults);

    $options->setDefaults($defaults);
    if (isset($page->header()->markdown)) {
      $options->merge($page->header()->markdown);
    }

    // Initialize the preferred variant of Parsedown
    if ($options->get('extra')) {
      $parsedown = new ParsedownExtra($page, $options->toArray());
    } else {
      $parsedown = new Parsedown($page, $options->toArray());
    }

    $content = $parsedown->text($body);
    return $content;
  }
}
