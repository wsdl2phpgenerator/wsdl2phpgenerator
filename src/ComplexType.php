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
 * @see \wsdl2php\Variable
 */
require_once \dirname(__FILE__).'/Variable.php';

/**
 * ComplexType
 *
 * @package Wsdl2PhpGenerator
 * @author Fredrik Wallgren <fredrik@wallgren.me>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class ComplexType extends \wsdl2php\Type
{
  /**
   *
   * @var array The members in the type
   */
  private $members;

  /**
   * Construct the object
   *
   * @param string $name The identifier for the class
   */
  function __construct($name)
  {
    parent::__construct($name, null);
    $this->members = array();
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

    $class = new \phpSource\PhpClass($this->phpIdentifier, $config->getClassExists());

    $constructorComment = new \phpSource\PhpDocComment();
    $constructorComment->setAccess(\phpSource\PhpDocElementFactory::getPublicAccess());
    $constructorSource = '';
    $constructorParameters = '';

    // Add member variables
    foreach ($this->members as $member)
    {
      $type = '';
      
      try
      {
        $type = \wsdl2php\Validator::validateType($member->getType());
      }
      catch (\wsdl2php\ValidationException $e)
      {
        $type .= 'Custom';
      }

      $name = \wsdl2php\Validator::validateNamingConvention($member->getName());
      $comment = new \phpSource\PhpDocComment();
      $comment->setVar(\phpSource\PhpDocElementFactory::getVar($type, $name, ''));
      $comment->setAccess(\phpSource\PhpDocElementFactory::getPublicAccess());
      $var = new \phpSource\PhpVariable('public', $name, '', $comment);
      $class->addVariable($var);

      $constructorSource .= '  $this->'.$name.' = $'.$name.';'.\PHP_EOL;
      $constructorComment->addParam(\phpSource\PhpDocElementFactory::getParam($type, $name, ''));
      $constructorComment->setAccess(\phpSource\PhpDocElementFactory::getPublicAccess());
      $constructorParameters .= ', $'.$name;
    }

    $constructorParameters = \substr($constructorParameters, 2); // Remove first comma
    $function = new \phpSource\PhpFunction('public', '__construct', $constructorParameters, $constructorSource, $constructorComment);

    // Only add the constructor if type constructor is selected
    if ($config->getNoTypeConstructor() == false)
    {
      $class->addFunction($function);
    }

    $this->class = $class;
  }

  /**
   * Adds the member. Owerwrites members with same name
   *
   * @param string $type
   * @param string $name
   */
  public function addMember($type, $name)
  {
    $this->members[$name] = new \wsdl2php\Variable($type, $name);
  }
}