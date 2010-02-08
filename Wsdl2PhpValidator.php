<?php
/**
 * @package Wsdl2PhpGenerator
 */

/**
 * Include the needed files
 */
include_once('Wsdl2PhpValidationException.php');

/**
 * Class that contains functionality to validate a string as valid php
 * Contains functionf for validating Type, Classname and Naming convention
 *
 * @package Wsdl2PhpGenerator
 * @author Fredrik Wallgren <fredrik@wallgren.me>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Wsdl2PhpValidator
{
  /**
   *
   * @var array Array containing all keywords in php
   */
  private $keywords;

  /**
   *
   * @var array Array containing primitive types
   */
  private $primitives;

  /**
   * Set up the object. Defines all keywords
   */
  public function __construct()
  {
    $this->keywords = array('and',
    'or',
    'xor',
    'as',
    'break',
    'case',
    'cfunction',
    'class',
    'continue',
    'declare',
    'const',
    'default',
    'do',
    'else',
    'elseif',
    'enddeclare',
    'endfor',
    'endforeach',
    'endif',
    'endswitch',
    'endwhile',
    'eval',
    'extends',
    'for',
    'foreach',
    'function',
    'global',
    'goto',
    'namespace',
    'if',
    'new',
    'old_function',
    'static',
    'switch',
    'use',
    'var',
    'while',
    'array',
    'die',
    'echo',
    'empty',
    'exit',
    'include',
    'include_once',
    'isset',
    'list',
    'print',
    'require',
    'require_once',
    'return',
    'unset',
    '__file__',
    '__line__',
    '__function__',
    '__class__',
    'abstract',
    'private',
    'public',
    'protected',
    'throw',
    'try');

    $this->primitives = array('string',
      'int',
      'float',
      'double',
      'bool',
      'boolean');
  }

  /**
   * Changes the name if it is invalid to a valid name
   *
   * @param string $name The name to validate
   * @return string Returns the validated name
   */
  public function validateClass($name)
  {
    return $this->validateClassName($name);
  }

  /**
   *
   * @param string $name The name to validate
   * @return string The validated name
   */
  public function validateType($name)
  {
    return $this->validateTypeName($name);
  }

  /**
   * Validates a name against standard PHP naming conventions
   *
   * @param string $name the name to validate
     *
   * @return string the validated version of the submitted name
     * 
   * @access public
   */
  public function validateNamingConvention($name)
  {
    return preg_replace('/[^a-zA-Z0-9_\x7f-\xff]*/', '', preg_replace('/^[^a-zA-Z_\x7f-\xff]*/', '', $name));
  }

  /**
   * Checks if $str is a primitive datatype
   *
   * @param string $str
   * @return bool True if $str is a primitive
   */
  public function isPrimitive($str)
  {
    return in_array(strtolower($str), $this->primitives);
  }

  /**
   * Validates a class name against PHP naming conventions and already defined
   * classes, and optionally stores the class as a member of the interpreted classmap.
   *
   * @param string $className the name of the class to test
   *
   * @return string the validated version of the submitted class name
   *
   * @access private
   */
  private function validateClassName($className)
  {
    $validClassName = $this->validateNamingConvention($className);

    if (class_exists($validClassName))
    {
      throw new Wsdl2PhpValidationException("Class ".$validClassName." already defined. Cannot redefine class with class loaded.");
    }

    if ($this->isKeyword($validClassName))
    {
      throw new Wsdl2PhpValidationException($validClassName.' is a restricted keyword.');
    }

    return $validClassName;
  }


  /**
   * Validates a wsdl type against known PHP primitive types, or otherwise
   * validates the namespace of the type to PHP naming conventions
   *
   * @param string $type the type to test
   *
   * @return string the validated version of the submitted type
   *
   * @access private
   */
  private function validateTypeName($type)
  {
    if (substr($type, -2) == "[]" || strtolower(substr($type, 0, 7)) == "arrayof")
    {
      return 'array';
    }

    switch (strtolower($type))
    {
      case "int": case "integer": case "long": case "byte": case "short":
      case "negativeinteger": case "nonnegativeinteger":
      case "nonpositiveinteger": case "positiveinteger":
      case "unsignedbyte": case "unsignedint": case "unsignedlong": case "unsignedshort":
        return 'int';
        break;

      case "float": case "long": case "double": case "decimal":
        return 'float';
        break;

      case "string": case "token": case "normalizedstring": case "hexbinary":
        return 'string';
        break;

      default:
        $validType = $this->validateNamingConvention($type);
        break;
    }

    if ($this->isKeyword($validType))
    {
      throw new Wsdl2PhpValidationException($validType.' is a restricted keyword.');
    }

    return $validType;
  }

  /**
   * Checks if $str is a restricted keyword
   *
   * @param string $str
   * @return bool True if $str is a restricted keyword
   */
  private function isKeyword($str)
  {
    return in_array(strtolower($str), $this->keywords);
  }
}