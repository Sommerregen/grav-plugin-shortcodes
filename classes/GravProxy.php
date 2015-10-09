<?php
/**
 * This file is part of Grav Shortcodes plugin.
 *
 * Dual licensed under the MIT or GPL Version 3 licenses, see LICENSE.
 * http://benjamin-regler.de/license/
 */

namespace Grav\Plugin\Shortcodes;

use Grav\Common\Grav;
use Grav\Common\Page\Page;
use RocketTheme\Toolbox\Event\Event;

class GravProxy implements \ArrayAccess
{
  protected $object;
  protected $page;
  protected $name = [];
  protected $callable;

  /**
   * Constructor.
   *
   * @param object   $object [description]
   * @param Page   $page [description]
   * @param [type] $name  [description]
   */
  public function __construct($object, $page, $callable = null, $name = null)
  {
    $this->object = $object;
    $this->page = $page;
    $this->callable = $callable;

    if ($name) {
      $this->name = is_array($name) ? $name : [$name];
    }

    if ($callable && !is_callable($callable)) {
      throw new \Exception(sprintf("Function '%s' must be callable.", $callable));
    }
  }

  public function __call($method, array $arguments = [])
  {
    if ($callable = $this->callable) {
      $result = $callable([// new Event([
        'type' => '__call',
        'object' => $this->object,
        'page' => $this->page,
        'name' => implode('.', $this->name),
        'arguments' => [$method, $arguments]
      ]);
    }

    return is_null($result) ? call_user_func_array([$this->object, $method], $arguments) : $result;
  }

  /**
   * ArrayAccess implementation
   */

  /**
   * Whether or not an offset exists.
   *
   * @param mixed $offset  An offset to check for.
   * @return bool          Returns TRUE on success or FALSE on failure.
   */
  public function offsetExists($offset)
  {
    if ($callable = $this->callable) {
      $result = $callable([ //new Event([
        'type' => 'offsetExists',
        'object' => $this->object,
        'page' => $this->page,
        'name' => implode('.', $this->name),
        'arguments' => [$offset]
      ]);
    }

    return is_null($result) ? isset($this->object[$offset]) : $result;
  }

  /**
   * Returns the value at specified offset.
   *
   * @param mixed $offset  The offset to retrieve.
   * @return mixed         Can return all value types.
   */
  public function offsetGet($offset)
  {
    if (isset($this->object[$offset])) {
      if ($callable = $this->callable) {
        $result = $callable([ // new Event([
          'type' => 'offsetGet',
          'object' => $this->object,
          'page' => $this->page,
          'name' => implode('.', $this->name),
          'arguments' => [$offset]
        ]);
      }

      if (is_null($result)) {
        $name = array_merge($this->name, [$offset]);
        $result = new static($this->object[$offset], $this->page, $this->callable, $name);
      }

      return $result;
    }
  }

  /**
   * Assigns a value to the specified offset.
   *
   * @param mixed $offset  The offset to assign the value to.
   * @param mixed $value   The value to set.
   */
  public function offsetSet($offset, $value)
  {
    if ($callable = $this->callable) {
      $result = $callable([ //new Event([
        'type' => 'offsetSet',
        'object' => $object,
        'page' => $this->page,
        'name' => implode('.', $this->name),
        'arguments' => [$offset]
      ]);
    }

    if (is_null($result)) {
      if (is_null($offset)) {
        $this->object[] = $value;
      } else {
        $this->object[$offset] = $value;
      }
    }
  }

  /**
   * Unsets an offset.
   *
   * @param mixed $offset  The offset to unset.
   */
  public function offsetUnset($offset)
  {
    if ($callable = $this->callable) {
      $result = $callable([// new Event([
        'type' => 'offsetUnset',
        'object' => $this->object,
        'page' => $this->page,
        'name' => implode('.', $this->name),
        'arguments' => [$offset]
      ]);
    }

    if (is_null($result)) {
      unset($this->object[$offset]);
    }
  }
}
