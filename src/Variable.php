<?php
/**
 * @package Wsdl2PhpGenerator
 */

/**
 * Very stupid datatype to use instead of array
 *
 * @package Wsdl2PhpGenerator
 * @author Fredrik Wallgren <fredrik.wallgren@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Variable
{
  /**
   *
   * @var string The type
   */
  private $type;

  /**
   *
   * @var string The name
   */
  private $name;

  /**
   *
   * @param string $type
   * @param string $name
   */
  function __construct($type, $name)
  {
    $this->type = $type;
    $this->name = $name;
  }

  /**
   *
   * @return string
   */
  public function getType()
  {
    return $this->type;
  }

  /**
   *
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }
}

