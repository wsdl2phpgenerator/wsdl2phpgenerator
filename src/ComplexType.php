<?php

/**
 * @package Wsdl2PhpGenerator
 */

/**
 * @see wsdl2phpType
 */
require_once dirname(__FILE__).'/Type.php';

/**
 * @see wsdl2phpVariable
 */
require_once dirname(__FILE__).'/Variable.php';

/**
 * ComplexType
 *
 * @package Wsdl2PhpGenerator
 * @author Fredrik Wallgren <fredrik@wallgren.me>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class wsdl2phpComplexType extends wsdl2phpType
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
   * @param string $restriction The restriction(datatype) of the values
   */
  function __construct($name)
  {
    parent::__construct($name, null);
    $this->members = array();
  }

  /**
   * Implements the loading of the class object
   * @throws wsdl2phpException if the class is already generated(not null)
   */
  protected function generateClass()
  {
    if ($this->class != null)
    {
      throw new wsdl2phpException("The class has already been generated");
    }

    $config = wsdl2phpGenerator::getInstance()->getConfig();

    $class = new phpSourcePhpClass($this->phpIdentifier, $config->getClassExists());

    $constructorComment = new phpSourcePhpDocComment();
    $constructorComment->setAccess(phpSourcePhpDocElementFactory::getPublicAccess());
    $constructorSource = '';
    $constructorParameters = '';

    // Add member variables
    foreach ($this->members as $member)
    {
      $type = '';
      
      try
      {
        $type = wsdl2phpValidator::validateType($member->getType());
      }
      catch (wsdl2phpValidationException $e)
      {
        $type .= 'Custom';
      }

      $name = wsdl2phpValidator::validateNamingConvention($member->getName());
      $comment = new phpSourcePhpDocComment();
      $comment->setVar(phpSourcePhpDocElementFactory::getVar($type, $name, ''));
      $comment->setAccess(phpSourcePhpDocElementFactory::getPublicAccess());
      $var = new phpSourcePhpVariable('public', $name, '', $comment);
      $class->addVariable($var);

      $constructorSource .= '  $this->'.$name.' = $'.$name.';'.PHP_EOL;
      $constructorComment->addParam(phpSourcePhpDocElementFactory::getParam($type, $name, ''));
      $constructorComment->setAccess(phpSourcePhpDocElementFactory::getPublicAccess());
      $constructorParameters .= ', $'.$name;
    }

    $constructorParameters = substr($constructorParameters, 2); // Remove first comma
    $function = new phpSourcePhpFunction('public', '__construct', $constructorParameters, $constructorSource, $constructorComment);

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
    $this->members[$name] = new wsdl2phpVariable($type, $name);
  }
}