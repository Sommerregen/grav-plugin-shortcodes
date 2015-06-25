<?php
/**
 * ShortcodeNode
 *
 * This file is part of Grav Shortcodes plugin.
 *
 * Dual licensed under the MIT or GPL Version 3 licenses, see LICENSE.
 * http://benjamin-regler.de/license/
 */

namespace Grav\Plugin\Shortcodes\Twig;

use Grav\Plugin\Shortcodes\Twig\ShortcodeNode;

/**
 * We should imagine the required code to call our function:
 *
 * {% mytag 1 "test" (2+3) %}
 *   Hello, world!
 * {% endmytag %}
 *
 * Should call:
 *   functionToCall("Hello, world!", array(1, "test", 5))
 *
 * As expressions need to be subcompiled ( 2+3 should result in 5 ), we
 * will need to create an array of arguments, and call functionToCall
 * using call_user_func_array.
 */

/**
 * ShortcodeNode
 */
class ShortcodeNode extends \Twig_Node
{
   public function __construct($body, $params, $lineno = 0, $tag = null)
   {
      parent::__construct(array('body' => $body), array('params' => $params), $lineno, $tag);
   }

   public function compile(\Twig_Compiler $compiler)
   {
      $body = $this->getNode('body');
      if (!is_array($body)) {
         $body = [$body];
      }

      $compiler
        ->addDebugInfo($this)
        ->write("ob_start();\n");

      foreach ($body as $key => $node) {
         $compiler->subcompile($node);
      }

      $compiler
        ->write("\$body = ob_get_clean();\n")
        ->write("\$params = array(");

      foreach ($this->getAttribute('params') as $key => $node) {
         $compiler
           ->string($key)
           ->raw(" => ")
           ->subcompile($node)
           ->raw(", ");
      }
      $compiler
        ->raw(");\n")
        ->write("echo \$context['__shortcodes'](")
        ->string($this->tag)
        ->write(", \$body, \$params);\n")
        ->write("unset(\$body, \$params);\n");
   }
}
