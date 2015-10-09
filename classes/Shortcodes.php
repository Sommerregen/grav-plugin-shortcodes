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

use Grav\Common\GravTrait;
use Grav\Common\Data\Data;
use RocketTheme\Toolbox\Event\Event;

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
   * Twig Sanbox SecurityPolicy
   *
   * @var \Twig_Sandbox_SecurityPolicy
   */
  protected $policy;

  /**
   * @var \Grav\Common\Data\Data
   */
  protected $config;

  /**
   * The current page to render
   *
   * @var \Grav\Common\Page\Page
   */
  protected $page;

  /**
   * Shortcode registry
   *
   * @var array
   */
  protected $shortcodes = [];

  /**
   * A key-valued array used for hashing shortcodes of a page
   *
   * @var array
   */
  protected $hashes = [];

  /**
   * Constructor
   *
   * @param array $config An array of default values.
   */
  public function __construct($config)
  {
    $this->config = $config;
    ShortcodesTrait::setShortcodesClass($this);

    // Set up Twig environment
    $this->loader = new \Twig_Loader_Array([]);
    $this->twig = new Twig\Environment($this->loader, [
      'use_strict_variables' => false,
    ]);

    // Set up sandbox for parsing shortcodes
    $this->policy = new \Twig_Sandbox_SecurityPolicy();
    $this->twig->addExtension(new \Twig_Extension_Sandbox($this->policy, true));
    $this->policy->setAllowedTags($this->loadShortcodes());

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
    $function = function($tag, $body, $arguments) use ($options, $page) {
      if (isset($this->shortcodes[$tag])) {
        $options = isset($options[$tag]) ? $options[$tag] : [];
      }

      $event = new Event([
        'body' => $body,
        'options' => new Data(array_replace_recursive($options, $arguments)),
        'grav' => self::getGrav(),
        'shortcodes' => $this,
        'page' => $page,
        'tag' => $tag
      ]);

      return $event;
    };

    // Wrapper for shortcodes filter function
    $filter_function = function($name, $content, $context, $env) {
      return $this->filterShortcode($name, $content, $context, $env);
    };

    // Process in-page shortcodes Twig
    $name = '@Shortcodes:' . $page->path();
    $this->loader->setTemplate($name, $content);
    $vars = [
      '__shortcodes' => $function,
      '__shortcodes_filter' => $filter_function
    ];

    try {
      $page_default = $this->page;
      $this->page = $page;

      $output = $this->twig->render($name, $vars);
    } catch (\Twig_Error_Loader $e) {
      throw new \RuntimeException($e->getRawMessage(), 404, $e);
    }

    $shortcodes = isset($page->header()->shortcodes) ? $page->header()->shortcodes : [];
    if (isset($shortcodes['extra'])) {
      /** @var Cache $cache */
      $cache = self::getGrav()['cache'];

      $cache_id = md5('shortcodes' . $page->id() . $cache->getKey());
      $cache->save($cache_id, $shortcodes['extra']);
    }

    $this->page = $page_default;
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
    // Register shortcodes from array
    if (is_array($shortcode)) {
      foreach ($shortcode as $item) {
        $this->register($item, $options);
      }

    // Register shortcodes from (built-in) classes
    } elseif ($shortcode instanceof ShortcodeInterface) {
      $options += $shortcode->getShortcode();
      $name = $options['name'];

      $this->shortcodes[$name] = $shortcode;
      switch ($options['type']) {
        case 'inline':
          $this->twig->addShortcode(
            new InlineShortcode($name, [$shortcode, 'execute'], $options)
          );
          return true;

        case 'block':
          $this->twig->addShortcode(
            new BlockShortcode($name, [$shortcode, 'execute'], $options)
          );
          return true;

        default:
          break;
      }

    // Register shortcodes from Shortcode functions
    } elseif ($shortcode instanceof Twig\GenericShortcode) {
      $this->twig->addShortcode($shortcode);
      return true;

    // Register shortcode filters from ShortcodeFilter filters
    } elseif ($shortcode instanceof Twig\GenericShortcodeFilter) {
      $this->twig->addShortcodeFilter($shortcode);
      return true;

    // Register shortcodes from Shortcode extensions
    } elseif ($shortcode instanceof Twig\ExtensionInterface || is_object($shortcode)) {
      $result = false;

      if (method_exists($shortcode, 'getShortcodes')) {
        $result = true;
        $shortcodes = $shortcode->getShortcodes();
        foreach ($shortcodes as $shortcode) {
          $this->twig->addShortcode($shortcode);
        }
      }

      if (method_exists($shortcode, 'getShortcodeFilters')) {
        $result = true;
        $shortcodeFilters = $shortcode->getShortcodeFilters();
        foreach ($shortcodeFilters as $shortcodeFilter) {
          $this->twig->addShortcodeFilter($shortcodeFilter);
        }
      }

      return $result;
    }

    return false;
  }

  /**
   * Add extra items to the shortcodes stream.
   *
   * @param string $group   The group name to add the extra items to.
   * @param any    $extra   The item to store.
   */
  public function addExtra($group, $method, $arguments = null)
  {
    $header = $this->page->header();
    $arguments = is_array($arguments) ? $arguments : [$arguments];

    // Modify page header
    $shortcodes = isset($header->shortcodes) ? $header->shortcodes : [];
    $shortcodes['extra'][$group][] = [$method, $arguments];

    // Temporally store Shortcode extras in page header
    $this->page->modifyHeader('shortcodes', $shortcodes);

    if ($this->page->id() == self::getGrav()['page']->id()) {
      $object = ($group != 'page') ? self::getGrav()[$group] : $page;
      call_user_func_array([$object, $method], $arguments);
    }
  }

  /**
   * Normalize content i.e. replace all hashes with their corresponding
   * shortcodes
   *
   * @param  string $content The content to be processed
   *
   * @return string          The processed content
   */
  public function normalize($content)
  {
    // Fast replace hashes with their corresponding math formula
    $hashes = array_keys($this->hashes);
    $content = str_replace($hashes, $this->hashes, $content);

    // Return normalized content
    return $content;
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
      $class =  __NAMESPACE__ . "\\Shortcodes\\$name";
      $defaults = $this->config->get('plugins.shortcodes.shortcodes.'.strtolower($name), []);

      if (empty($defaults) || $defaults['enabled']) {
        $options = isset($defaults['options']) ? $defaults['options'] : [];
        $shortcode = new $class($options);
        $this->register($shortcode);
      }
    }

    // Fire event
    self::getGrav()->fireEvent('onShortcodesInitialized', new Event(['shortcodes' => $this]));

    $this->shortcodes = $this->twig->getShortcodes();
    return array_keys($this->shortcodes);
  }

  /**
   * A filter function for shortcode filters
   *
   * @param  string $name         The name of the shortcode filter to be
   *                              applied.
   * @param  string $content      The content to be filtered.
   * @param  mixed  $context      The Twig context
   * @param  mixed  $environment  The Twig environment
   *
   * @return string               The filtered content (here a hash); use
   *                              $this->normalize() to replace the hash
   *                              with the filtered content.
   */
  protected function filterShortcode($name, $content, $context = null, $environment = null)
  {
    $output = $content;
    $extra = [$name, $this];

    $filters = $this->twig->getShortcodeFilter($name);
    foreach ($filters as $filter) {
      $arguments = [$output];
      if ($filter->needsEnvironment()) {
        $arguments[] = $environment;
      }
      if ($filter->needsContext()) {
        $arguments[] = $context;
      }

      $arguments = array_merge($arguments, $filter->getArguments(), $extra);
      $output = call_user_func_array($filter->getCallable(), $arguments);

      if (!$output) {
        break;
      }
    }

    // Replace shortcode with a hash to be replaced later
    return $this->hash($output);
  }

  /**
   * Reset shortcode hashes
   */
  protected function reset()
  {
    $this->hashes = [];
  }

  /**
   * Hash a given text.
   *
   * Called whenever a tag must be hashed when a function insert an
   * atomic element in the text stream. Passing $text to through this
   * function gives a unique text-token which will be reverted back when
   * calling unhash.
   *
   * @param  string $text The text to be hashed
   *
   * @return string       Return a unique text-token which will be
   *                      reverted back when calling unhash.
   */
  protected function hash($text)
  {
    static $counter = 0;

    // Swap back any tag hash found in $text so we do not have to `unhash`
    // multiple times at the end.
    $text = $this->unhash($text);

    // Then hash the block
    $key = implode("\1A", array('shortcodes', $this->page->id(), ++$counter, 'S'));
    $this->hashes[$key] = $text;

    // String that will replace the tag
    return $key;
  }

  /**
   * Swap back in all the tags hashed by hash.
   *
   * @param  string $text The text to be un-hashed
   *
   * @return string       A text containing no hash inside
   */
  protected function unhash($text)
  {
    $pattern = '~shortcodes\x1A([0-9a-z]+)\x1A([0-9]+)\x1AS~i';
    $text = preg_replace_callback($pattern, function($matches) {
      return $this->hashes[$matches[0]];
    }, $text);

    return $text;
  }
}
