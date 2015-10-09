<?php
/**
 * Summary
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
 * Summary
 *
 * Add a custom summary to a page.
 */
class Summary extends Shortcode
{
  /**
   * Get informations about the shortcode.
   *
   * @return array An associative array needed to register the shortcode.
   */
  public function getShortcode()
  {
    return ['name' => 'summary', 'type' => 'block'];
  }

  /**
   * Execute shortcode.
   *
   * @param  Event        $event An event object.
   * @return string|null         Return modified contents.
   */
  public function execute(Event $event)
  {
    /* @var \Grav\Common\Grav $grav */
    $grav = $event['grav'];

    /* @var \Grav\Common\Page\Page $page */
    $page = $event['page'];

    /* @var \Grav\Plugin\Shortcodes\Shortcodes $shortcodes */
    $shortcodes = $event['shortcodes'];

    /* @var \Grav\Common\Data\Data $options */
    $options = $event['options'];
    $options->setDefaults($this->defaults);

    $body = trim($event['body']);
    $method = strtolower($options->get('render', $options->get(0, 'html')));

    switch ($method) {
      case 'twig':
        $body = nl2br($grav['twig']->processString($body));
        break;

      case 'twig+html':
      case 'html+twig':
        // Twig is not enabled on the page; pre-process content
        if (!$page->header()->process->twig) {
          $body = trim($grav['twig']->processString($body));
        }

      case 'html':
        $summary = clone $page;
        $body = $summary->content($body);
        break;

      case 'raw':
      default:
        $body = nl2br($body);
        break;
    }

    $page->setSummary($body);
  }
}
