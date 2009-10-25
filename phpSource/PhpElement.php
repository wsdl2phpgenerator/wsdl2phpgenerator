<?php

/**
 * Abstract base class for all PHP elements, variables, functions and classes etc.
 *
 * @package phpSource
 * @author Fredrik Wallgren <fredrik@wallgren.me>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
abstract class PhpElement
{
  /**
   *
   * @var string The access of the function |public|private|protected
   * @access protected
   */
  protected $access;

  /**
   *
   * @var string The identifier of the element
   * @access protected
   */
  protected $identifier;

  /**
   * Function to be overloaded, return the source code of the specialized element
   *
   * @access public
   * @return string
   */
  abstract public function getSource();

  /**
   *
   * @return string The access of the element
   */
  public function getAccess()
  {
    return $this->access;
  }

  /**
   *
   * @return string The identifier, name, of the element
   */
  public function getIdentifier()
  {
    return $this->identifier;
  }
}
