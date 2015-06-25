<?php
/**
 * Embed
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
 * Embed
 *
 * Embed pages or page content into other pages using simple markdown syntax.
 */
class Embed extends Shortcode
{
  /**
   * Get informations about the shortcode.
   *
   * @return array An associative array needed to register the shortcode.
   */
  public function getShortcode()
  {
    return ['name' => 'embed', 'type' => 'inline'];
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
    $options->setDefaults($this->defaults);

    $path = $options->get('page', $options->get(0));
    if (!($modular = (bool) $options->get('modular'))) {
      // Figure out whether path belongs to a modular page or not
      $modular = (strpos(basename($path), '_') === 0) ? true : false;
    }

    if ($page = $event['page']->find($path)) {
      if ($modular) {
        if ($template = $options->get('template', '')) {
          $page->template($template);
        }

        $page->modularTwig(true);
        $content = $event['grav']['twig']->processPage($page);
      } else {
        $content = $page->content();
      }
    }

    return $content;
  }
}
