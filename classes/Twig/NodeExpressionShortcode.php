<?php
/**
 * NodeExpressionShortcode
 *
 * This file is part of Grav Shortcodes plugin.
 *
 * Dual licensed under the MIT or GPL Version 3 licenses, see LICENSE.
 * http://benjamin-regler.de/license/
 */

namespace Grav\Plugin\Shortcodes\Twig;

use Grav\Plugin\Shortcodes\ShortcodesTrait;

/**
 * NodeExpressionShortcode
 */
class NodeExpressionShortcode extends \Twig_Node_Expression_Call
{
  use ShortcodesTrait;

  /**
   * Constructor.
   *
   * @param string              $name      The name of the node.
   * @param \Twig_NodeInterface $body      The body content.
   * @param \Twig_NodeInterface $arguments Arguments to pass to the node
   *                                       expression.
   * @param int                 $lineno    Line number.
   * @param string              $tag       The name of the tag.
   */
  public function __construct($name, \Twig_NodeInterface $body, \Twig_NodeInterface $arguments, $lineno, $tag = null)
  {
    parent::__construct(['node' => $body, 'arguments' => $arguments], ['name' => $name], $lineno, $tag);
  }

  /**
   * Compile the node.
   *
   * @param  \Twig_Compiler $compiler A Twig compiler instance.
   */
  public function compile(\Twig_Compiler $compiler)
  {
    $name = $this->getAttribute('name');
    $shortcode = $compiler->getEnvironment()->getShortcode($name);
    $filter = $compiler->getEnvironment()->getShortcodeFilter($name);

    $this->setAttribute('name', $name);
    $this->setAttribute('type', 'shortcode');
    $this->setAttribute('thing', $shortcode);
    $this->setAttribute('needs_environment', $shortcode->needsEnvironment());
    $this->setAttribute('needs_context', $shortcode->needsContext());
    $this->setAttribute('arguments', $shortcode->getArguments());
    if ($shortcode instanceof \Twig_FunctionCallableInterface || $shortcode instanceof GenericShortcode) {
      $instance = ShortcodesTrait::getShortcodesClass();
      $this->setAttribute('callable', $shortcode->getCallable());
    }

    if ($this->hasNode('node')) {
      $body = $this->getNode('node');
      if (!is_array($body)) {
        $body = [$body];
      }

      $compiler
        ->addDebugInfo($this)
        ->write("ob_start();\n");

      foreach ($body as $key => $node) {
        $compiler->subcompile($node);
      }

      $compiler->write("\$body = ob_get_clean();\n");
    }

    if ($this->hasNode('arguments') && null !== $this->getNode('arguments')) {
      $compiler->write("\$arguments = array(");

      foreach ($this->getNode('arguments') as $key => $node) {
        $compiler
          ->string($key)
          ->raw(" => ")
          ->subcompile($node)
          ->raw(", ");
      }
      $compiler->raw(");\n");
    }

    $compiler
      ->write('$compiled = $context["__shortcodes"](')
      ->string($this->tag)
      ->raw(", \$body, \$arguments);\n")
      ->write($filter ? '$compiled = ' : 'echo ');

    $this->compileCallable($compiler);
    $compiler->raw(";\n");

    // Filter shortcode, if registered filters are present
    if ($filter) {
      $compiler
        ->write('echo $context["__shortcodes_filter"](')
        ->string($name)
        ->raw(', $compiled, $context, $this->env)');
    }

    $compiler
      ->raw(";\n")
      ->write('unset($body, $arguments, $compiled);')
      ->raw("\n");
  }

  /**
   * Compile arguments to be used by the node.
   *
   * @param  \Twig_Compiler $compiler A Twig compiler instance.
   */
  protected function compileArguments(\Twig_Compiler $compiler)
  {
    $compiler->raw('(');
    $first = true;

    if ($this->hasAttribute('needs_environment') && $this->getAttribute('needs_environment')) {
      $compiler->raw('$this->env');
      $first = false;
    }

    if ($this->hasAttribute('needs_context') && $this->getAttribute('needs_context')) {
      if (!$first) {
        $compiler->raw(', ');
      }
      $compiler->raw('$context');
      $first = false;
    }

    if ($this->hasAttribute('arguments')) {
      foreach ($this->getAttribute('arguments') as $argument) {
        if (!$first) {
          $compiler->raw(', ');
        }
        $compiler->string($argument);
        $first = false;
      }
    }

    if ($this->hasNode('node')) {
      if (!$first) {
        $compiler->raw(', ');
      }
      $compiler->raw('$compiled');
      $first = false;
    }

    $compiler->raw(')');
  }
}
