<?php

namespace Wsdl2Php;

include_once('Wsdl2PhpConfig.php');
include_once('Wsdl2PhpException.php');
include_once('Wsdl2PhpValidator.php');

// Php code classes
include_once('phpSource/PhpFile.php');
include_once('phpSource/PhpVariable.php');
include_once('phpSource/PhpDocComment.php');
include_once('phpSource/PhpDocElementFactory.php');

/**
 * Class that contains functionality for generating classes from a wsdl file
 *
 * @package Wsdl2PhpGenerator
 * @author Fredrik Wallgren <fredrik@wallgren.me>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Generator
{
  /**
   * A SoapClient for loading the WSDL
   * @var SoapClient
   * @access private
   */
  private $client = null;

  /**
   * DOM document used to load and parse the wsdl
   * @var DOMDocument
   * @access private
   */
  private $dom = null;

  /**
   * A phpSource code representation of the client
   *
   * @var \phpSource\PhpClass The service class
   */
  private $service;

  /**
   * An array of class objects that represents the complexTypes in the service
   *
   * @var array Array of \phpSource\PhpClass objects
   */
  private $types;

  /**
   * The validator to use
   *
   * @var Validator
   * @access private
   */
  private $validator;

  /**
   * This is the object that holds the current config
   *
   * @var Config
   * @access private
   */
  private $config;

  /**
   * Construct the generator
   */
  public function __construct()
  {
    $this->validator = new Validator();
    $this->service = null;
    $this->types = array();
  }

  /**
   * Generates php source code from a wsdl file
   *
   * @see Config
   * @param Config $config The config to use for generation
   * @access public
   */
  public function generate(Config $config)
  {
    $this->config = $config;

    $this->log(_('Starting generation'));

    $this->load();

    $this->savePhp();

    $this->log(_('Generation complete'));
  }

  /**
   * Load the wsdl file into php
   */
  private function load()
  {
    $wsdl = $this->config->getInputFile();

    try
    {
      $this->log(_('Loading the wsdl'));
      $this->client = new \SoapClient($wsdl);
    } 
    catch(\SoapFault $e)
    {
      throw new Exception('Error connectiong to to the wsdl. Error: '.$e->getMessage());
    }

    $this->log(_('Loading the DOM'));
    $this->dom = \DOMDocument::load($wsdl);

    $this->loadTypes();
    $this->loadService();
  }

  /**
   * Loads the service class
   *
   * @access private
   */
  private function loadService()
  {
    $serviceName = $this->dom->getElementsByTagNameNS('*', 'service')->item(0)->getAttribute('name');
    $serviceName = $this->validator->validateClass($serviceName);
    $this->service = new \phpSource\PhpClass($serviceName, $this->config->getClassExists(), 'SoapClient');

    $this->log(_('Generating class '.$serviceName));

    $this->log(_('Generating comment for '.$serviceName));

    $comment = new \phpSource\PhpDocComment();
    $comment->addParam(\phpSource\PhpDocElementFactory::getParam('string', 'wsdl', 'The wsdl file to use'));
    $comment->addParam(\phpSource\PhpDocElementFactory::getParam('array', 'config', 'A array of config values'));
    $comment->setAccess(\phpSource\PhpDocElementFactory::getPublicAccess());

    $source = '  foreach(self::$classmap as $key => $value)
  {
    if(!isset($options[\'classmap\'][$key]))
    {
      $options[\'classmap\'][$key] = $value;
    }
  }
  '.$this->generateServiceOptions($this->config).'
  parent::__construct($wsdl, $options);'.PHP_EOL;

    $this->log(_('Generating constructor for '.$serviceName));

    $function = new \phpSource\PhpFunction('public', '__construct', '$wsdl = \''.$this->config->getInputFile().'\', $options = array()', $source, $comment);

    $this->service->addFunction($function);

    $name = 'classmap';
    $comment = new \phpSource\PhpDocComment();
    $comment->setAccess(\phpSource\PhpDocElementFactory::getPrivateAccess());
    $comment->setVar(\phpSource\PhpDocElementFactory::getVar('array', $name, 'The defined classes'));
    
    $init = 'array('.PHP_EOL;
    foreach ($this->types as $type)
    {
      $init .= "  '".$type->getIdentifier()."' => '".$type->getIdentifier()."',".PHP_EOL;
    }
    $init = substr($init, 0, strrpos($init, ','));
    $init .= ')';
    $var = new \phpSource\PhpVariable('private static', $name, $init, $comment);
    $this->service->addVariable($var);

    $this->log(_('Adding classmap'));

    $this->log(_('Loading operations for '.$serviceName));

    // get operations
    $operations = $this->client->__getFunctions();
    foreach($operations as $operation)
    {
      $matches = array();
      if(preg_match('/^(\w[\w\d_]*) (\w[\w\d_]*)\(([\w\$\d,_ ]*)\)$/', $operation, $matches))
      {
        $returns = $matches[1];
        $call = $matches[2];
        $params = $matches[3];
      }
      else if(preg_match('/^(list\([\w\$\d,_ ]*\)) (\w[\w\d_]*)\(([\w\$\d,_ ]*)\)$/', $operation, $matches))
      {
        $returns = $matches[1];
        $call = $matches[2];
        $params = $matches[3];
      }
      else
      {
        // invalid function call
        throw new Exception('Invalid function call: '.$function);
      }

      $name = $this->validator->validateNamingConvention($call);

      $comment = new \phpSource\PhpDocComment();
      $comment->setAccess(\phpSource\PhpDocElementFactory::getPublicAccess());

      $source = '  return $this->__soapCall(\''.$name.'\', array(';
      foreach (explode(', ', $params) as $param)
      {
        $val = explode(' ', $param);
        if (count($val) == 1)
        {
          $source .= $val[0].', ';
        }
        else
        {
          $source .= $val[1].', ';
        }
        if (strlen(@$val[1]) > 0)
        {
          $comment->addParam(\phpSource\PhpDocElementFactory::getParam($val[0], $val[1], ''));
        }
      }
      // Remove last comma
      $source = substr($source, 0, -2);
      $source .= '));'.PHP_EOL;

      $function = new \phpSource\PhpFunction('public', $name, $params, $source, $comment);

      if ($this->service->functionExists($function->getIdentifier()) == false)
      {
        $this->log(_('Adding operation '.$name));
        $this->service->addFunction($function);
      }
    }

    $this->log(_('Done loading service'));
  }

  /**
   *
   * @param Config $config The config containing the values to use
   *
   * @return string Returns the string for the options array
   */
  private function generateServiceOptions(Config $config)
  {
    $ret = '';

    $this->log(_('Generating service options'));

    if (count($config->getOptionFeatures()) > 0)
    {
      $this->log(_('Adding option features'));
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
  }".PHP_EOL;
    }

    if (strlen($config->getWsdlCache()) > 0)
    {
      $this->log(_('Adding wsdl cache option'));

      $ret .= "
  if (isset(\$options['wsdl_cache']) == false)
  {
    \$options['wsdl_cache'] = ".$config->getWsdlCache();
      $ret .= ";
  }".PHP_EOL;
    }

    if (strlen($this->config->getCompression()) > 0)
    {
      $this->log(_('Adding compression'));

      $ret .= "
  if (isset(\$options['compression']) == false)
  {
    \$options['compression'] = ".$config->getCompression();
       $ret .= ";
  }".PHP_EOL;
    }

    return $ret;
  }

  /**
   * Loads all type classes
   *
   * @access private
   */
  private function loadTypes()
  {
    $this->log(_('Loading types'));

    $types = $this->client->__getTypes();

    foreach($types as $type)
    {
      $parts = explode("\n", $type);
      $className = explode(" ", $parts[0]);
      $className = $className[1];

      if( substr($className, -2, 2) == '[]' || substr($className, 0, 7) == 'ArrayOf')
      {
        // skip arrays
        continue;
      }

      $members = array();
      for($i = 1; $i < count($parts) - 1; $i++)
      {
        $parts[$i] = trim($parts[$i]);
        list($type, $member) = explode(" ", substr($parts[$i], 0, strlen($parts[$i])-1) );

        if(strpos($member, ':'))
        {
          $arr = explode(':', $member);
          $member = $arr[1];
        }

        $add = true;
        foreach($members as $mem)
        {
          if($mem['member'] == $member)
          {
            $add = false;
          }
        }

        if($add)
        {
          $members[] = array('member' => $member, 'type' => $type);
        }
      }

      // gather enumeration values
      $values = array();
      if(count($members) == 0)
      {
        $theNode = null;

        $typesNode  = $this->dom->getElementsByTagName('types')->item(0);
        $schemaList = $typesNode->getElementsByTagName('schema');

        for ($i = 0; $i < $schemaList->length; $i++)
        {
          $children = $schemaList->item($i)->childNodes;
          for ($j = 0; $j < $children->length; $j++)
          {
            $node = $children->item($j);
            if ($node instanceof DOMElement && $node->hasAttributes() && $node->attributes->getNamedItem('name')->nodeValue == $className)
            {
              $theNode = $node;
            }
          }
        }

        if($theNode)
        {
          $valueList = $theNode->getElementsByTagName('enumeration');
          if($valueList->length > 0)
          {
            for($i = 0; $i < $valueList->length; $i++)
            {
              $values[] = $valueList->item($i)->attributes->getNamedItem('value')->nodeValue;
            }
          }
        }
      }

      $this->log(_('Generating type '.$className));

      $class = new \phpSource\PhpClass($className, $this->config->getClassExists());

      $constructorComment = new \phpSource\PhpDocComment();
      $constructorComment->setAccess(\phpSource\PhpDocElementFactory::getPublicAccess());
      $constructorSource = '';
      $constructorParameters = '';

      foreach ($members as $varArr)
      {
		$type = $this->validator->validateType($varArr['type']);
        $name = $this->validator->validateNamingConvention($varArr['member']);
        $comment = new \phpSource\PhpDocComment();
        $comment->setVar(\phpSource\PhpDocElementFactory::getVar($type, $name, ''));
        $comment->setAccess(\phpSource\PhpDocElementFactory::getPublicAccess());
        $var = new \phpSource\PhpVariable('public', $name, '', $comment);
        $class->addVariable($var);

        $constructorSource .= '  $this->'.$name.' = $'.$name.';'.PHP_EOL;
        $constructorComment->addParam(\phpSource\PhpDocElementFactory::getParam($type, $name, ''));
        $constructorComment->setAccess(\phpSource\PhpDocElementFactory::getPublicAccess());
        $constructorParameters .= ', $'.$name;
      }

      $constructorParameters = substr($constructorParameters, 2); // Remove first comma
      $function = new \phpSource\PhpFunction('public', '__construct', $constructorParameters, $constructorSource, $constructorComment);

      // Only add the constructor if type constructor is selected
      if ($this->config->getNoTypeConstructor() == false)
      {
        $class->addFunction($function);

        $this->log(_('Adding constructor for '.$className));
      }

      $this->types[] = $class;
    }

    $this->log(_('Done loading types'));
  }

  /**
   * Save all the loaded classes to the configured output dir
   *
   * @throws Exception If no service is loaded
   * @throws Exception If the output dir does not exist and can't be created
   *
   * @access private
   */
  private function savePhp()
  {
    $outputDirectory = $this->config->getOutputDir();

    $this->log(_('Starting save to directory '. $outputDirectory));

    if ($this->service === null)
    {
      throw new Exception('No service loaded');
    }
    
    $useNamespace = (\strlen($this->config->getNamespaceName()) > 0);
	
	//Try to create output dir if non existing
    if (is_dir($outputDirectory) == false && is_file($outputDirectory) == false)
    {
      $this->log(_('Creating output dir'));
      if(mkdir($outputDirectory, 0777, true) == false)
      {
        throw new Wsdl2PhpException('Could not create output directory and it does not exist!');
      }
    }

    $validClasses = $this->config->getClassNamesArray();

    $file = null;

    if ($this->config->getOneFile())
    {
      // Check if the service class is in valid classes of if all classes should be generated
      if (count($validClasses) == 0 || count($validClasses) > 0 && in_array($this->service->getIdentifier(), $validClasses))
      {
        // Generate file and add all classes to it then save it
        $file = new \phpSource\PhpFile($this->service->getIdentifier());

        $this->log(_('Opening file '.$this->service->getIdentifier()));

        if ($useNamespace)
        {
          $file->addNamespace($this->config->getNamespaceName());
        }

        $file->addClass($this->service);

        $this->log(_('Adding service to file'));
      }

      foreach ($this->types as $class)
      {
        // Check if the class should be saved
        if (count($validClasses) == 0 || count($validClasses) > 0 && in_array($class->getIdentifier(), $validClasses))
        {
          if ($file == null)
          {
            $file = new \phpSource\PhpFile($class->getIdentifier());
          }
        
          $file->addClass($class);
          $this->log(_('Adding type to file '.$class->getIdentifier()));
        }
      }

      // Sanity check, if the user only wanted to generate non-existing classes
      if ($file != null)
      {
        $this->log(_('Saving file'));
        $file->save($outputDirectory);
      }
    }
    else
    {
      // Save types
      foreach ($this->types as $class)
      {
        // Check if the class should be saved
        if (count($validClasses) == 0 || count($validClasses) > 0 && in_array($class->getIdentifier(), $validClasses))
        {
          $file = new \phpSource\PhpFile($class->getIdentifier());

          if ($useNamespace)
          {
            $file->addNamespace($this->config->getNamespaceName());
          }

          $file->addClass($class);

          $this->log(_('Adding class '.$class->getIdentifier().' to file'));

          $file->save($outputDirectory);

          // Add the filename as dependency for the service
          $this->service->addDependency($class->getIdentifier().'.php');

          $this->log(_('Adding dependency'));
        }
      }

      // Check if the service class is in valid classes of if all classes should be generated
      if (count($validClasses) == 0 || count($validClasses) > 0 && in_array($this->service->getIdentifier(), $validClasses))
      {
        // Generate file and save the service class
        $file = new \phpSource\PhpFile($this->service->getIdentifier());

        $this->log(_('Opening file '.$this->service->getIdentifier()));

        if ($useNamespace)
        {
          $file->addNamespace($this->config->getNamespaceName());
        }

        $file->addClass($this->service);

        $this->log(_('Adding service to file'));

        $file->save($outputDirectory);

        $this->log(_('Saving file'));
      }
    }
  }

  /**
   * Logs a message to the standard output
   *
   * @param string $message The message to log
   */
  private function log($message)
  {
    if ($this->config->getVerbose() == true)
    {
      print $message.PHP_EOL;
    }
  }
}