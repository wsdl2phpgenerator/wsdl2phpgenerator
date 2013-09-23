<?php

/**
 * @package Wsdl2PhpGenerator
 */

/**
 * @see Type
 */
require_once dirname(__FILE__).'/Type.php';

/**
 * @see Variable
 */
require_once dirname(__FILE__).'/Variable.php';

/**
 * ComplexType
 *
 * @package Wsdl2PhpGenerator
 * @author Fredrik Wallgren <fredrik.wallgren@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class ComplexType extends Type
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
   * @throws Exception if the class is already generated(not null)
   */
  protected function generateClass()
  {
    if ($this->class != null)
    {
      throw new Exception("The class has already been generated");
    }

    $config = Generator::getInstance()->getConfig();

    $class = new PhpClass($this->phpIdentifier, $config->getClassExists());
    
    $constructorComment = new PhpDocComment();
    $constructorComment->setAccess(PhpDocElementFactory::getPublicAccess());
    $constructorSource = '';
    $constructorParameters = '';

    // Add member variables
    foreach ($this->members as $member)
    {
      $type = '';
      
      try
      {
        $type = Validator::validateType($member->getType());
        $simplyfiedType = SimplifyTypesService::instance()->getRootType($type);
      }
      catch (ValidationException $e)
      {
        $type .= 'Custom';
      }

      
      
      $name = Validator::validateNamingConvention($member->getName());
      $comment = new PhpDocComment();
      $comment->setVar(PhpDocElementFactory::getParam($simplyfiedType, $name, ''));
      $comment->setAccess(PhpDocElementFactory::getProtectedAccess());
      $var = new PhpVariable('protected', $name, '', $comment);
      $class->addVariable($var);
      
      $enumVars = XsdInspectorService::instance()->getEnumeration($type);
      
      
      // add getter
      $getterComment = new PhpDocComment();
      $getterComment->setAccess(PhpDocElementFactory::getPublicAccess());
      $getterReturnDescription = '';
      if (null !== $enumVars) {
      	$getterReturnDescription .= ' <'.implode(',', $enumVars).'>';
      }
      
      $getterComment->setReturn(PhpDocElementFactory::getReturn($simplyfiedType, $getterReturnDescription));
      $class->addFunction(new PhpFunction('public'
      										, 'get' . ucfirst($name)
      										, ''
      										, '	return $this->'.$name.';'.PHP_EOL
    										, $getterComment));
      // add setter
      $setterComment = new PhpDocComment();
      $setterComment->setAccess(PhpDocElementFactory::getPublicAccess());
      $setterComment->setVar(PhpDocElementFactory::getVar($simplyfiedType, $name, ''));
      
      
      $setterReturnDescription = '';
      $setterFunctionSrc = '	$this->'.$name.' = $'.$name.';'.PHP_EOL.'	return $this;'.PHP_EOL;
      
      //check enums
      if (null !== $enumVars) {
      	$comment = new PhpDocComment();
      	$comment->setVar(PhpDocElementFactory::getParam('multitype:string', $name.'Enum', ''));
      	$comment->setAccess(PhpDocElementFactory::getPublicAccess());
      	
      	$var = new PhpVariable('public static', $name.'Enum', 'array('.implode(',', $enumVars).')', $comment);
		$class->addVariable($var);
		$setterComment->setDescription('Enumeration of <' . implode(',', $enumVars) . '>');
		$setterFunctionSrc = '	$this->enumSet(\''.$name.'\', $'.$name.');'.PHP_EOL;
		$setterFunctionSrc .= '	return $this;'.PHP_EOL;
      }
      
      //check choice
     
      $choiceVars = XsdInspectorService::instance()->getChoice($class->getIdentifier());
      if (null !== $choiceVars) {
      	$setterComment->setDescription('Choice of <' . implode(',', $choiceVars) . '>');
      	$setterFunctionSrc = '	$this->unsetDefinedVarsInArray(array('.implode(',', $choiceVars).'));'.PHP_EOL;
      	$setterFunctionSrc .= '	$this->'.$name.' = $'.$name.';'.PHP_EOL;
      	$setterFunctionSrc .= '	return $this;'.PHP_EOL;
      	
      }
      
      
      
      $setterComment->setReturn(PhpDocElementFactory::getReturn($this->phpIdentifier, $setterReturnDescription));
      $class->addFunction(new PhpFunction('public'
      										, 'set' . ucfirst($name)
      										, '$'.$name
      										, $setterFunctionSrc
    										, $setterComment));
      
      // constructor
      $constructorSource .= '	$this->'.$name.' = $'.$name.';'.PHP_EOL;
      $constructorComment->addParam(PhpDocElementFactory::getParam($type, $name, ''));
      $constructorComment->setAccess(PhpDocElementFactory::getPublicAccess());
      $constructorParameters .= ', $'.$name;
      
    }

    $constructorParameters = substr($constructorParameters, 2); // Remove first comma
    $parameterSubmitConstructor = new PhpFunction('public', '__construct', $constructorParameters, $constructorSource, $constructorComment);

    // Only add the constructor if type constructor is selected
    if ($config->getUseTypeConstructor() == true)
    {
    	
      	$class->addFunction($parameterSubmitConstructor);
    } 
    
    
    
	//add extends    
	$class->setExtends(XsdInspectorService::instance()->getExtensionClassName($class->getIdentifier()));
	
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
    $this->members[$name] = new Variable($type, $name);
  }
}

