<?php
/**
 * Assets
 *
 * This file is part of Grav Shortcodes plugin.
 *
 * Dual licensed under the MIT or GPL Version 3 licenses, see LICENSE.
 * http://benjamin-regler.de/license/
 */

namespace Grav\Plugin\Shortcodes\Shortcodes;

use Grav\Common\Uri;
use RocketTheme\Toolbox\Event\Event;
use Grav\Plugin\Shortcodes\Shortcode;

/**
 * Assets
 *
 * Add CSS and JS assets directly into the site and find inline assets in
 * the following format:
 *
 * {{% assets type="css" inline=true %}}
 * h1 {color: red !important;}
 * {{% end %}}
 *
 * {{% assets type="css" order=5 %}}
 * //cdnjs.cloudflare.com/ajax/libs/1140/2.0/1140.css
 * //cdnjs.cloudflare.com/ajax/libs/1140/2.0/1141.css
 * //cdnjs.cloudflare.com/ajax/libs/1140/2.0/1142.css
 * {{% assets %}}
 *
 * {{% assets type="js" %}}
 * //cdnjs.cloudflare.com/ajax/libs/angularFire/0.5.0/angularfire.min.js
 * {{% end %}}
 *
 * {{% assets type="js" inline=true %}}
 * function initialize() {
 *   var mapCanvas = document.getElementById('map_canvas');
 *   var mapOptions = {
 *     center: new google.maps.LatLng(44.5403, -78.5463),
 *     zoom: 8,
 *     mapTypeId: google.maps.MapTypeId.ROADMAP
 *   }
 *   var map = new google.maps.Map(mapCanvas, mapOptions);
 * }
 * {{% end %}}
 */
class Assets extends Shortcode
{
  /**
   * Get informations about the shortcode.
   *
   * @return array An associative array needed to register the shortcode.
   */
  public function getShortcode()
  {
    return ['name' => 'assets', 'type' => 'block'];
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

    /* @var \Grav\Plugin\Shortcodes\Shortcodes $shortcodes */
    $shortcodes = $event['shortcodes'];

    /* @var \Grav\Common\Data\Data $options */
    $options = $event['options'];
    $options->setDefaults($this->defaults);

    $type = strtolower($options->get('type'));
    $body = trim(strip_tags($event['body'], '<link><script>'));

    if ($options->get('inline')) {
      $shortcodes->addExtra('assets', 'addInline'. ucfirst($type), $body);
    } else {
      /* @var \Grav\Common\Page\Page $page */
      $page = $event['page'];

      /* @var UniformResourceLocator $locator */
      $locator = $grav['locator'];

      /* @var Uri $uri */
      $uri = $grav['uri'];

      $priority = $options->get('priority', 10);
      $pipeline = $options->get('pipeline', false);
      $loading = $options->get('load', '');

      $entries = explode("\n", $body);
      $name = ($type === 'css') ? 'addCss' : 'addJs';

      foreach ($entries as $entry) {
        $url = $before = trim($entry, " \t");
        // Don't process protocol agnostic or external URLs
        if (!$grav['uri']->isExternal($url) && substr($url, 0, 2) !== '//') {
          if (false !== strpos($url, '://')) {
            // Get relative path to the resource (or false if not found).
            if ($resource = $locator->findResource($url, false)) {
              $url = rtrim($uri->rootUrl(false), '/') . '/' . $resource;
            }
          } else {
            // Resolve URL (relative or absolute with respect to current page)
            $url = Uri::convertUrl($page, $url);
          }

          if (false === strpos($url, '://')) {
            $url = preg_replace('~^' . preg_quote($uri->rootUrl(false)) . '~i', '', $url);
            $url = rtrim($uri->rootUrl(true), '/') . $url;
          }
        }

        $shortcodes->addExtra('assets', $name, [$url, $priority, $pipeline, $loading]);
      }
    }
  }
}
