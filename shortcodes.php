<?php
/**
 * Shortcodes v1.2.0
 *
 * This plugin enables to use shortcodes (simple snippets) inside a
 * document to be rendered by Grav.
 *
 * Dual licensed under the MIT or GPL Version 3 licenses, see LICENSE.
 * http://benjamin-regler.de/license/
 *
 * @package     Shortcodes
 * @version     1.2.0
 * @link        <https://github.com/sommerregen/grav-plugin-shortcodes>
 * @author      Benjamin Regler <sommerregen@benjamin-regler.de>
 * @copyright   2015, Benjamin Regler
 * @license     <http://opensource.org/licenses/MIT>        MIT
 * @license     <http://opensource.org/licenses/GPL-3.0>    GPLv3
 */

namespace Grav\Plugin;

use Grav\Common\Grav;
use Grav\Common\Plugin;
use Grav\Plugin\Shortcodes;
use RocketTheme\Toolbox\Event\Event;

/**
 * ShortcodesPlugin
 *
 * This plugin enables to use shortcodes (simple snippets) inside a
 * document to be rendered by Grav.
 */
class ShortcodesPlugin extends Plugin
{
  /**
   * Instance of Shortcodes class
   *
   * @var \Grav\Plugin\Shortcodes\Shortcodes
   */
  protected $shortcodes;

  /**
   * Return a list of subscribed events.
   *
   * @return array A list of events, i.e. 'name' => ['method', priority].
   */
  public static function getSubscribedEvents()
  {
    return [
      'onPluginsInitialized' => ['onPluginsInitialized', 0]
    ];
  }

  /**
   * Initialize configuration
   */
  public function onPluginsInitialized()
  {
    if ($this->isAdmin()) {
      $this->active = false;
      return;
    }

    if ($this->config->get('plugins.shortcodes.enabled')) {
      $this->enable([
        'onPageInitialized' => ['onPageInitialized', 0],
        'onPageContentRaw' => ['onPageContentRaw', 0],
        'onPageContentProcessed' => ['onPageContentProcessed', 0],
        'onTwigInitialized' => ['onTwigInitialized', 0]
      ]);
    }
  }

  /**
   * Initialize page.
   */
  public function onPageInitialized()
  {
    /** @var \Grav\Common\Page\Page $page */
    $page = $this->grav['page'];

    /** @var Cache $cache */
    $cache = $this->grav['cache'];

    /** @var Debugger $debugger */
    $debugger = $this->grav['debugger'];

    $cache_id = md5('shortcodes' . $page->id() . $cache->getKey());
    if ($data = $cache->fetch($cache_id)) {
      $debugger->addMessage("Shortcodes Plugin cache hit.");

      foreach ($data as $key => $extra) {
        $object = ($key != 'page') ? $this->grav[$key] : $page;

        foreach ($extra as $value) {
          list($method, $arguments) = $value;
          call_user_func_array([$object, $method], $arguments);
        }
      }
    }
  }

  /**
   * Add content after page content was read into the system.
   *
   * @param  Event  $event An event object, when `onPageContentRaw` is
   *                       fired.
   */
  public function onPageContentRaw(Event $event)
  {
    /** @var Page $page */
    $page = $event['page'];

    /** @var Cache $cache */
    $cache = $this->grav['cache'];

    $config = $this->mergeConfig($page);
    if ($config->get('enabled')) {
      $raw = $page->getRawContent();

      // Set parsed content back into as raw content
      $page->setRawContent($this->init()->render($raw, $config->get('shortcodes', []), $page));
    }
  }

  /**
   * Add content after page was processed.
   *
   * @param Event $event An event object, when `onPageContentProcessed`
   *                     is fired.
   */
  public function onPageContentProcessed(Event $event)
  {
    // Get the page header
    $page = $event['page'];

    // Get modified content, replace all tokens with their
    // respective shortcodes and write content back to page
    $content = $page->getRawContent();
    $page->setRawContent($this->init()->normalize($content));
  }

  /**
   * Initialize Twig configuration and filters.
   */
  public function onTwigInitialized()
  {
    // Expose function
    $this->grav['twig']->twig()->addFunction(
      new \Twig_SimpleFunction('shortcodes', [$this, 'shortcodeFunction'], ['is_safe' => ['html']])
    );
  }

  /**
   * Filter to parse Shortcodes.
   *
   * @param  string $content The content to be filtered.
   * @param  array  $options Array of options for the Shortcode filter.
   *
   * @return string          The filtered content.
   */
  public function shortcodeFunction($content, $params = [])
  {
    $config = $this->mergeConfig($this->grav['page'], $params);
    return $this->init()->render($content, $config->get('shortcodes', []), $this->grav['page']);
  }

  /**
   * Initialize plugin and all dependencies.
   *
   * @return Shortcodes   Returns an instance of \Grav\Plugin\Shortcodes\Shortcodes.
   */
  protected function init()
  {
    if (!$this->shortcodes) {
      // Initialize Autoloader
      require_once(__DIR__.'/classes/Autoloader.php');

      $autoloader = new Shortcodes\Autoloader();
      $autoloader->register();

      // Initialize back-end
      $this->shortcodes = new Shortcodes\Shortcodes($this->config);
    }

    return $this->shortcodes;
  }
}
