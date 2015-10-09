<?php
/**
 * Staging
 *
 * This file is part of Grav Shortcodes plugin.
 *
 * Dual licensed under the MIT or GPL Version 3 licenses, see LICENSE.
 * http://benjamin-regler.de/license/
 */

namespace Grav\Plugin\Shortcodes\Twig;

/**
 * Staging
 */
class Staging extends Extension
{
  protected $functions = [];
  protected $filters = [];
  protected $shortcodes = [];
  protected $shortcodeFilters = [];
  protected $visitors = [];
  protected $tokenParsers = [];
  protected $globals = [];
  protected $tests = [];

  /**
   * Add a function.
   *
   * @param string         $name     The name of the function.
   * @param \Twig_Function $function A Twig function.
   */
  public function addFunction($name, $function)
  {
    $this->functions[$name] = $function;
  }

  /**
   * Returns a list of functions to add to the existing list.
   *
   * @return array An array of functions
   */
  public function getFunctions()
  {
    return $this->functions;
  }

  /**
   * Add a Filter.
   *
   * @param string       $name   The name of the Twig filter.
   * @param \Twig_Filter $filter A Twig filter.
   */
  public function addFilter($name, $filter)
  {
    $this->filters[$name] = $filter;
  }

  /**
   * Returns a list of filters to add to the existing list.
   *
   * @return array An array of filters
   */
  public function getFilters()
  {
    return $this->filters;
  }

  /**
   * Add a shortcode.
   *
   * @param string            $name      The name of the shortcode
   * @param GenericShortcode  $shortcode A Twig shortcode.
   */
  public function addShortcode($name, $shortcode)
  {
    $this->shortcodes[$name] = $shortcode;
  }

  /**
   * {@inheritdoc}
   */
  public function getShortcodes()
  {
    return $this->shortcodes;
  }

  /**
   * Add a shortcode filter.
   *
   * @param string            $name      The name of the shortcode filter
   * @param GenericShortcode  $shortcode A Twig shortcode filter.
   */
  public function addShortcodeFilter($name, $filter)
  {
    $this->shortcodeFilters[$name][] = $filter;
  }

  /**
   * {@inheritdoc}
   */
  public function getShortcodeFilters()
  {
    return $this->shortcodeFilters;
  }

  /**
   * Add a NodeVisitor.
   *
   * @param Twig_NodeVisitorInterface $visitor A Twig NodeVisitor.
   */
  public function addNodeVisitor(\Twig_NodeVisitorInterface $visitor)
  {
    $this->visitors[] = $visitor;
  }

  /**
   * Returns the node visitor instances to add to the existing list.
   *
   * @return Twig_NodeVisitorInterface[] An array of Twig_NodeVisitorInterface instances
   */
  public function getNodeVisitors()
  {
    return $this->visitors;
  }

  /**
   * Add a TokenParser.
   *
   * @param \Twig_TokenParserInterface $parser A Twig Parser.
   */
  public function addTokenParser($parser)
  {
    if ($parser instanceof \Twig_TokenParserInterface || $parser instanceof ShortcodeTokenParser) {
      $this->tokenParsers[] = $parser;
    }
  }

  /**
   * Returns the token parser instances to add to the existing list.
   *
   * @return array An array of Twig_TokenParserInterface or
   *               Twig_TokenParserBrokerInterface instances
   */
  public function getTokenParsers()
  {
    return $this->tokenParsers;
  }

  /**
   * Add a global.
   *
   * @param string       $name  The name of the global.
   * @param \Twig_Global $value A Twig Global.
   */
  public function addGlobal($name, $value)
  {
    $this->globals[$name] = $value;
  }

  /**
   * Returns a list of global variables to add to the existing list.
   *
   * @return array An array of global variables
   */
  public function getGlobals()
  {
    return $this->globals;
  }

  /**
   * Add a Twig Test
   * @param string     $name The name of the Twig Test.
   * @param \Twig_Test $test A Twig Test.
   */
  public function addTest($name, $test)
  {
    $this->tests[$name] = $test;
  }

  /**
   * Returns a list of tests to add to the existing list.
   *
   * @return array An array of tests
   */
  public function getTests()
  {
    return $this->tests;
  }

  /**
   * Returns the name of the extension.
   *
   * @return string The extension name
   */
  public function getName()
  {
    return 'staging';
  }
}
