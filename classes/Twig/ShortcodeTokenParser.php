<?php
/**
 * ShortcodeTokenParser
 *
 * This file is part of Grav Shortcodes plugin.
 *
 * Dual licensed under the MIT or GPL Version 3 licenses, see LICENSE.
 * http://benjamin-regler.de/license/
 */

namespace Grav\Plugin\Shortcodes\Twig;

/**
 * ShortcodeTokenParser
 */
class ShortcodeTokenParser extends \Twig_TokenParser
{
  /**
   * The name of the TokenParser.
   *
   * @var name
   */
  protected $name;

  /**
   * The shortcode assigned to the TokenParser.
   *
   * @var \Grav\Plugin\Shortcodes\Twig\GenericShortcode
   */
  protected $shortcode;

  /**
   * Constuctor.
   *
   * @param string           $name      The name of the TokenParser.
   * @param GenericShortcode $shortcode A shortcode instance to assign
   *                                    to the TokenParser.
   */
  public function __construct($name, $shortcode)
  {
    $this->name = (string) $name;
    $this->shortcode = $shortcode;
  }

  /**
   * Parses a token and returns a node.
   *
   * @param  Twig_Token         $token   A Twig_Token instance
   *
   * @return Twig_NodeInterface          A Twig_NodeInterface instance
   */
  public function parse(\Twig_Token $token)
  {
    $lineno = $token->getLine();
    $stream = $this->parser->getStream();

    // Recovers all inline parameters close to your tag name
    $arguments = $this->parseArguments(true, true);
    $stream->expect(\Twig_Token::BLOCK_END_TYPE);
    $body = new \Twig_Node();

    // Detect middle tags only for block expressions
    $continue = ($this->shortcode->getType() == 'block') ? true : false;
    while ($continue)
    {
      $this->parser->pushLocalScope();
      // Create subtree until the decideShortcodeEnd() callback returns true
      $body = $this->parser->subparse([$this, 'decideShortcodeEnd']);

      // Switch statement in case to add middle tags, such
      // as: {% shortcode %}, {% nextshortcode %}, {% endshortcode %}.
      $name = $stream->expect(\Twig_Token::NAME_TYPE)->getValue();
      switch ($name) {
        case 'end':
        case "end{$this->name}":
          $continue = false;
          break;
        default:
          throw new \Twig_Error_Syntax(sprintf('Unexpected end of template. Twig was looking for the following tags "end%1$s" or "end" to close the "%1$s" block started at line %2$d).', $this->name, $lineno), -1);
      }

      $this->parser->popLocalScope();
      $stream->expect(\Twig_Token::BLOCK_END_TYPE);
    }

    $class = $this->shortcode->getNodeClass();
    return new $class($this->name, $body, $arguments, $lineno, $this->getTag());
   }

  /**
   * Parses arguments.
   *
   * @param bool $namedArguments Whether to allow named arguments or not
   * @param bool $definition     Whether we are parsing arguments for a function definition
   *
   * @return Twig_Node
   *
   * @throws Twig_Error_Syntax
   */
  public function parseArguments($namedArguments = false, $definition = false)
  {
    $args = array();
    $stream = $this->parser->getStream();
    $parser = $this->parser->getExpressionParser();

    $comma = false;
    if ($stream->test(\Twig_Token::PUNCTUATION_TYPE, '(')) {
      $stream->next();
      $comma = true;
    }

    while (!$stream->test(\Twig_Token::BLOCK_END_TYPE)) {
      // Set default name and value
      $name = null;
      $value = $parser->parseExpression();

      // Check for named arguments
      if ($namedArguments && $token = $stream->nextIf(\Twig_Token::OPERATOR_TYPE, '=')) {
        if (!$value instanceof \Twig_Node_Expression_Name) {
          throw new \Twig_Error_Syntax(sprintf('A parameter name must be a string, "%s" given', get_class($value)), $token->getLine(), $this->parser->getFilename());
        }
        $name = $value->getAttribute('name');

        if ($definition) {
          $value = $parser->parseExpression();

          if (!$this->checkConstantExpression($value)) {
            throw new \Twig_Error_Syntax(sprintf('A default value for an argument must be a constant (a boolean, a string, a number, or an array).'), $token->getLine(), $this->parser->getFilename());
          }
        } else {
          $value = $parser->parseExpression();
        }
      }

      if ($definition && null === $name && $value->hasAttribute('name')) {
        $name = $value->getAttribute('name');
        $value = new \Twig_Node_Expression_Constant($name, $this->parser->getCurrentToken()->getLine());
      }

      if (null === $name) {
        $args[] = $value;
      } else {
        $args[$name] = $value;
      }

      // Optional: Parse comma separated arguments
      $token = $stream->getCurrent();
      if ($comma) {
        if ($stream->look()->test(\Twig_Token::BLOCK_END_TYPE)) {
          $stream->expect(\Twig_Token::PUNCTUATION_TYPE, ')', 'A list of arguments must be closed by a parenthesis');
        } else {
          $stream->expect(\Twig_Token::PUNCTUATION_TYPE, ',', 'Arguments must be separated by a comma');
        }
      }
    }

    return new \Twig_Node($args);
  }

  /**
   * Callback called at each tag name when subparsing, must return
   * true when the expected end tag is reached.
   *
   * @param \Twig_Token $token
   * @return bool
   */
  public function decideShortcodeEnd(\Twig_Token $token)
  {
    return $token->test(array('end', "end{$this->name}"));
  }

  /**
   * Your tag name: if the parsed tag match the one you put here, your parse()
   * method will be called.
   *
   * @return string
   */
  public function getTag()
  {
    return $this->name;
  }


  /**
   * Checks that the node only contains "constant" elements
   *
   * @param  Twig_NodeInterface $node [description]
   * @return bool                     [description]
   */
  protected function checkConstantExpression(\Twig_NodeInterface $node)
  {
    if (!($node instanceof \Twig_Node_Expression_Constant || $node instanceof \Twig_Node_Expression_Array
      || $node instanceof \Twig_Node_Expression_Unary || $node instanceof \Twig_Node_Expression_Binary)) {
      return false;
    }

    foreach ($node as $n) {
      if (!$this->checkConstantExpression($n)) {
        return false;
      }
    }

    return true;
  }
}
