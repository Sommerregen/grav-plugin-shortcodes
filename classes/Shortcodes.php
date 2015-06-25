<?php
/**
 * Shortcodes
 *
 * This file is part of Grav Shortcodes plugin.
 *
 * Dual licensed under the MIT or GPL Version 3 licenses, see LICENSE.
 * http://benjamin-regler.de/license/
 */

namespace Grav\Plugin\Shortcodes;

use Grav\Common\Grav;
use Grav\Common\GravTrait;
use Grav\Common\Data\Data;
use RocketTheme\Toolbox\Event\Event;

use Grav\Plugin\Shortcodes\Shortcode;
use Grav\Plugin\Shortcodes\ShortcodeInterface;
use Grav\Plugin\Shortcodes\Twig\ShortcodeTokenParser;

/**
 * Shortcodes
 *
 * Core class to provide shortcodes in Grav.
 */
class Shortcodes
{
  /**
   * Grav instance
   *
   * @var \Grav\Common\Grav
   */
  use GravTrait;

  /**
   * Twig environment
   *
   * @var \Twig_Environment
   */
  protected $twig;

  /**
   * Twig Loader array
   *
   * @var \Twig_Loader_Array
   */
  protected $loader;

  /**
   * @var \Grav\Common\Data\Data
   */
  protected $config;

  /**
   * Shortcode registry
   *
   * @var array
   */
  protected $shortcodes = [];

  /**
   * Constructor
   *
   * @param array $config An array of default values.
   */
  public function __construct($config)
  {
    $this->config = $config;

    // Set up Twig environment
    $this->loader = new \Twig_Loader_Array([]);
    $this->twig = new \Twig_Environment($this->loader, [
      'use_strict_variables' => false,
    ]);

    // Set up sandbox for parsing shortcodes
    $policy = new \Twig_Sandbox_SecurityPolicy($this->loadShortcodes());
    $this->twig->addExtension(new \Twig_Extension_Sandbox($policy, true));

    // Modify lexer to match special shortcode syntax
    $lexer = new \Twig_Lexer($this->twig, array(
      'tag_comment'   => ['{#', '#}'],
      'tag_block'     => ['{{%', '%}}'],
      'tag_variable'  => ['{#', '#}'],
      'interpolation' => ['#{', '}']
    ));
    $this->twig->setLexer($lexer);
  }

  /**
   * Render shortcodes.
   *
   * @param  string     $content The content to render.
   * @param  array      $options Options to be passed to the renderer.
   * @param  null|Page  $page    Null or an instance of \Grav\Common\Page.
   *
   * @return string              The modified contents.
   */
  public function render($content, $options = [], $page = null)
  {
    // Build an anonymous function to pass to twig `render` method
    $function = function($tag, $body, $params) use ($options, $page) {
      if (isset($this->shortcodes[$tag])) {
        $options = isset($options[$tag]) ? $options[$tag] : [];
        $event = new Event([
          'body' => $body,
          'options' => new Data(array_replace_recursive($options, $params)),
          'grav' => self::getGrav(),
          'shortcodes' => $this,
          'page' => $page
        ]);

        return $this->shortcodes[$tag]->execute($event);
      }
    };

    // Process in-page shortcodes Twig
    $name = '@Shortcodes:' . $page->path();
    $this->loader->setTemplate($name, $content);
    $vars = ['__shortcodes' => $function];

    try {
      $output = $this->twig->render($name, $vars);
    } catch (\Twig_Error_Loader $e) {
      throw new \RuntimeException($e->getRawMessage(), 404, $e);
    }

    return $output;
  }

  /**
   * Register a shortcode.
   *
   * @param  Shortcode $shortcode An instance of ShortcodeInterface.
   * @param  array  $options      An array of defaults options for the
   *                              shortcode.
   *
   * @return bool                 Return status code (true on success).
   */
  public function register($shortcode, $options = [])
  {
    if ($shortcode instanceof ShortcodeInterface) {
      $options += $shortcode->getShortcode();
      $inline = ($options['type'] === 'inline') ? true : false;
      $this->shortcodes[$options['name']] = $shortcode;

      $this->twig->addTokenParser(
        new ShortcodeTokenParser($options['name'], $inline)
      );

      return true;
    }

    return false;
  }

  /**
   * Add extra items to the shortcodes stream.
   *
   * @param string $group   The group name to add the extra items to.
   * @param any    $extra   The item to store.
   */
  public function addExtra($group, $extra)
  {
    /* @var \Grav\Common\Page\Page $page */
    $page = self::getGrav()['page'];

    $header = $page->header();
    $shortcodes = isset($header->shortcodes) ? $header->shortcodes : [];
    $shortcodes['extra'][$group][] = $extra;

    $page->modifyHeader('shortcodes', $shortcodes);
  }

  /**
   * Load shortcodes already provided by this plugin.
   */
  protected function loadShortcodes()
  {
    $iterator = new \FilesystemIterator(__DIR__.'/Shortcodes');
    foreach ($iterator as $fileinfo) {
      $name = $fileinfo->getBasename('.php');

      // Load shortcodes in directory "Shortcodes"
      $class =  __NAMESPACE__."\\Shortcodes\\$name";
      $defaults = $this->config->get('plugins.shortcodes.shortcodes.'.strtolower($name), []);

      if ($defaults['enabled']) {
        $options = isset($defaults['options']) ? $defaults['options'] : [];
        $shortcode = new $class($options);
        $this->register($shortcode);
      }
    }

    // Fire event
    self::getGrav()->fireEvent('onShortcodesEvent', new Event(['shortcodes' => $this]));

    return array_keys($this->shortcodes);
  }
}
