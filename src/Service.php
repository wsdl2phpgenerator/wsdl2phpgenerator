<?php

/**
 * @package Wsdl2PhpGenerator
 */

namespace wsdl2php;

/**
 * @see phpSource\PhpClass
 */
require_once \dirname(__FILE__).'/../lib/phpSource/PhpClass.php';

/**
 * @see phpSource\PhpDocElementFactory.php
 */
require_once \dirname(__FILE__).'/../lib/phpSource/PhpDocElementFactory.php';

/**
 * @see Operation
 */
require_once \dirname(__FILE__).'/Operation.php';

/**
 * Service represents the service in the wsdl
 *
 * @package Wsdl2PhpGenerator
 * @author Fredrik Wallgren <fredrik@wallgren.me>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Service
{
  /**
   *
   * @var \phpSource\PhpClass The class used to create the service.
   */
  private $class;

  /**
   *
   * @var string The name of the service
   */
  private $identifier;

  /**
   *
   * @var array An array containing the operations of the service
   */
  private $operations;

  /**
   *
   * @var string The description of the service used as description in the phpdoc of the class
   */
  private $description;

  /**
   *
   * @var array An array of \wsdl2php\Types
   */
  private $types;

  /**
   *
   * @param string $identifier The name of the service
   * @param array $types The types the service knows about
   * @param string $description The description of the service
   */
  function __construct($identifier, array $types, $description)
  {
    $this->identifier = $identifier;
    $this->types = $types;
    $this->description = $description;
  }

  /**
   *
   * @return \phpSource\PhpClass Returns the class, generates it if not done
   */
  public function getClass()
  {
    if($this->class == null)
    {
      $this->generateClass();
    }

    return $this->class;
  }

  /**
   * Generates the class if not already generated
   */
  public function generateClass()
  {
    $config = \wsdl2php\Generator::getInstance()->getConfig();

    // Add prefix and suffix
    $name = $config->getPrefix().$this->identifier.$config->getSuffix();

    // Generate a valid classname
    try
    {
      $name = \wsdl2php\Validator::validateClass($name);
    }
    catch (\wsdl2php\ValidationException $e)
    {
      $name .= 'Custom';
    }

    // Create the class object
    $comment = new \phpSource\PhpDocComment($this->description);
    $this->class = new \phpSource\PhpClass($name, $config->getClassExists(), '\SoapClient', $comment);

    // Create the constructor
    $comment = new \phpSource\PhpDocComment();
    $comment->addParam(\phpSource\PhpDocElementFactory::getParam('array', 'config', 'A array of config values'));
    $comment->addParam(\phpSource\PhpDocElementFactory::getParam('string', 'wsdl', 'The wsdl file to use'));
    $comment->setAccess(\phpSource\PhpDocElementFactory::getPublicAccess());

    $source = '  foreach(self::$classmap as $key => $value)
  {
    if(!isset($options[\'classmap\'][$key]))
    {
      $options[\'classmap\'][$key] = $value;
    }
  }
  '.$this->generateServiceOptions($config).'
  parent::__construct($wsdl, $options);'.\PHP_EOL;

    $function = new \phpSource\PhpFunction('public', '__construct', 'array $options = array(), $wsdl = \''.$config->getInputFile().'\'', $source, $comment);

    // Add the constructor
    $this->class->addFunction($function);

    // Generate the classmap
    $name = 'classmap';
    $comment = new \phpSource\PhpDocComment();
    $comment->setAccess(\phpSource\PhpDocElementFactory::getPrivateAccess());
    $comment->setVar(\phpSource\PhpDocElementFactory::getVar('array', $name, 'The defined classes'));

    $init = 'array('.\PHP_EOL;
    foreach ($this->types as $type)
    {
      if($type instanceof \wsdl2php\ComplexType)
      {
        $init .= "  '".$type->getIdentifier()."' => '".(($ns = $config->getNamespaceName()) ? $ns . '\\' : '').$type->getPhpIdentifier()."',".\PHP_EOL;
      }
    }
    $init = \substr($init, 0, \strrpos($init, ','));
    $init .= ')';
    $var = new \phpSource\PhpVariable('private static', $name, $init, $comment);

    // Add the classmap variable
    $this->class->addVariable($var);

    // Add all methods
    foreach ($this->operations as $operation)
    {
      $name = \wsdl2php\Validator::validateNamingConvention($operation->getName());

      $comment = new \phpSource\PhpDocComment($operation->getDescription());
      $comment->setAccess(\phpSource\PhpDocElementFactory::getPublicAccess());
      
      foreach ($operation->getParams() as $param => $hint)
      {
        $arr = $operation->getPhpDocParams($param, $this->types);
        $comment->addParam(\phpSource\PhpDocElementFactory::getParam($arr['type'], $arr['name'], $arr['desc']));
      }

      $source = '  return $this->__soapCall(\''.$name.'\', array('.$operation->getParamStringNoTypeHints().'));'.\PHP_EOL;

      $paramStr = $operation->getParamString($this->types);

      $function = new \phpSource\PhpFunction('public', $name, $paramStr, $source, $comment);

      if ($this->class->functionExists($function->getIdentifier()) == false)
      {
        $this->class->addFunction($function);
      }
    }
  }

  /**
   * Adds an operation to the service
   *
   * @param string $name
   * @param array $params
   * @param string $description
   */
  public function addOperation($name, $params, $description)
  {
    $this->operations[] = new \wsdl2php\Operation($name, $params, $description);
  }

  /**
   *
   * @param \wsdl2php\Config $config The config containing the values to use
   *
   * @return string Returns the string for the options array
   */
  private function generateServiceOptions(\wsdl2php\Config $config)
  {
    $ret = '';

    if (\count($config->getOptionFeatures()) > 0)
    {
      $i = 0;
      $ret .= "
  if (isset(\$options['features']) == false)
  {
    \$options['features'] = ";
      foreach ($config->getOptionFeatures() as $option)
      {
        if ($i++ > 0)
        {
          $ret .= ' | ';
        }

        $ret .= $option;
      }

      $ret .= ";
  }".\PHP_EOL;
    }

    if (\strlen($config->getWsdlCache()) > 0)
    {
      $ret .= "
  if (isset(\$options['wsdl_cache']) == false)
  {
    \$options['wsdl_cache'] = ".$config->getWsdlCache();
      $ret .= ";
  }".\PHP_EOL;
    }

    if (\strlen($config->getCompression()) > 0)
    {
      $ret .= "
  if (isset(\$options['compression']) == false)
  {
    \$options['compression'] = ".$config->getCompression();
      $ret .= ";
  }".\PHP_EOL;
    }

    return $ret;
  }
}