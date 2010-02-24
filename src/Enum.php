<?php

/**
 * @package Wsdl2PhpGenerator
 */

namespace wsdl2php;

/**
 * @see \wsdl2php\Type
 */
require_once \dirname(__FILE__).'/Type.php';

/**
 * Enum represents a simple type with enumerated values
 *
 * @package Wsdl2PhpGenerator
 * @author Fredrik Wallgren <fredrik@wallgren.me>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Enum extends \wsdl2php\Type
{
  /**
   *
   * @var array The values in the enum
   */
  private $values;

  /**
   * Construct the object
   *
   * @param string $name The identifier for the class
   * @param string $restriction The restriction(datatype) of the values
   */
  function __construct($name, $restriction)
  {
    parent::__construct($name, $restriction);
    $this->values = array();
  }

  /**
   * Implements the loading of the class object
   * @throws \wsdl2php\Exception if the class is already generated(not null)
   */
  protected function generateClass()
  {
    if ($this->class != null)
    {
      throw new \wsdl2php\Exception("The class has already been generated");
    }

    $config = \wsdl2php\Generator::getInstance()->getConfig();

    $this->class = new \phpSource\PhpClass($this->phpIdentifier, $config->getClassExists());

    foreach ($this->values as $value)
    {
      try
      {
        $name = \wsdl2php\Validator::validateNamingConvention($value);
      }
      catch (\wsdl2php\ValidationException $e)
      {
        $name = 'constant'.$name;
      }
      try
      {
        $name = \wsdl2php\Validator::validateType($name);
      }
      catch (\wsdl2php\ValidationException $e)
      {
        $name .= 'Custom';
      }

      $this->class->addConstant($value, $name);
    }
  }

  /**
   * Adds the value, typechecks strings and integers.
   * Otherwise it only checks so the value is not null
   *
   * @param mixed $value The value to add
   * @throws \InvalidArgumentException if the value doesn'nt fit in the restriction
   */
  public function addValue($value)
  {
    if ($this->datatype == 'string')
    {
      if (\is_string($value) == false)
      {
        throw new \InvalidArgumentException('The value('.$value.') is not string but the restriction demands it');
      }
    }
    elseif ($this->datatype == 'integer')
    {
      // The value comes as string from the wsdl
      if (\is_string($value))
      {
        $value = \intval($value);
      }

      if (\is_int($value) == false)
      {
        throw new \InvalidArgumentException('The value('.$value.') is not int but the restriction demands it');
      }
    }
    else
    {
      if ($value == null)
      {
        throw new \InvalidArgumentException('Value('.$value.') is null');
      }
    }

    $this->values[] = $value;
  }
}