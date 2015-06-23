<?php
/**
 * Shortcodes v1.0.0
 *
 * This plugin enables to use shortcodes (simple snippets) inside a
 * document to be rendered by Grav.
 *
 * Dual licensed under the MIT or GPL Version 3 licenses, see LICENSE.
 * http://benjamin-regler.de/license/
 *
 * @package     Shortcodes
 * @version     1.0.0
 * @link        <https://github.com/sommerregen/grav-plugin-shortcodes>
 * @author      Benjamin Regler <sommerregen@benjamin-regler.de>
 * @copyright   2015, Benjamin Regler
 * @license     <http://opensource.org/licenses/MIT>        MIT
 * @license     <http://opensource.org/licenses/GPL-3.0>    GPLv3
 */

namespace Grav\Plugin;

use Grav\Common\Grav;
use Grav\Common\Plugin;
use RocketTheme\Toolbox\Event\Event;

use Grav\Plugin\Shortcodes\Autoloader;
use Grav\Plugin\Shortcodes\Shortcodes;

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
  protected $backend;

  /**
   * Return a list of subscribed events.
   *
   * @return array A list of events, i.e. 'name' => ['method', priority].
   */
  public static function getSubscribedEvents()
  {
    return [
      'onTwigInitialized' => ['onTwigInitialized', 0],
      'onBuildPagesInitialized' => ['onBuildPagesInitialized', 0]
    ];
  }

  /**
   * Initialize configuration when building pages.
   */
  public function onBuildPagesInitialized()
  {
    if ($this->isAdmin()) {
      $this->active = false;
      return;
    }

    if ($this->config->get('plugins.shortcodes.enabled')) {
      $this->init();
      $this->enable([
        'onPageContentRaw' => ['onPageContentRaw', 0]
      ]);
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

    $config = $this->mergeConfig($page);
    if ($config->get('enabled')) {
      $raw = $page->getRawContent();

      // Set the parsed content back into as raw content
      $page->setRawContent($this->backend->render($raw, $config->get('shortcodes', []), $page));
    }
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
    if (!$this->backend) {
      // Initialize Autoloader
      require_once(__DIR__.'/classes/Autoloader.php');

      $autoloader = new Autoloader();
      $autoloader->register();

      // Initialize back-end
      $this->backend = new Shortcodes($this->config);
    }

    return $this->backend;
  }
}
