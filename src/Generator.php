<?php
/**
 * @package Wsdl2PhpGenerator
 */

/**
 * Include the needed files
 */
require_once dirname(__FILE__).'/Config.php';
require_once dirname(__FILE__).'/Exception.php';
require_once dirname(__FILE__).'/Validator.php';
require_once dirname(__FILE__).'/Variable.php';
require_once dirname(__FILE__).'/Enum.php';
require_once dirname(__FILE__).'/ComplexType.php';
require_once dirname(__FILE__).'/Pattern.php';
require_once dirname(__FILE__).'/DocumentationManager.php';
require_once dirname(__FILE__).'/Service.php';
require_once dirname(__FILE__).'/OutputManager.php';

// Php code classes
require_once dirname(__FILE__).'/../lib/phpSource/PhpFile.php';

/**
 * Class that contains functionality for generating classes from a wsdl file
 *
 * @package Wsdl2PhpGenerator
 * @author Fredrik Wallgren <fredrik@wallgren.me>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class wsdl2phpGenerator
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
   * 
   *
   * @var Service The service class
   */
  private $service;

  /**
   * An array of Type objects that represents the types in the service
   *
   * @var array Array of Type objects
   * @see wsdl2phpType
   */
  private $types;

  /**
   * This is the object that holds the current config
   *
   * @var wsdl2phpConfig
   * @access private
   */
  private $config;

  /**
   *
   * @var wsdl2phpDocumentationManager A manager for the documentation
   */
  private $documentation;

  /**
   *
   * @var Generator The infamous singleton instance
   */
  private static $instance;

  /**
   * Construct the generator
   */
  public function __construct()
  {
    $this->service = null;
    $this->types = array();
    $this->enums = array();
    $this->simple = array();
    $this->documentation = new wsdl2phpDocumentationManager();

    if (self::$instance != null)
    {
      throw new wsdl2phpException('wsdl2phpGenerator is only supposed to be constructed once. Check your code!');
    }

    self::$instance = $this;
  }

  /**
   * Generates php source code from a wsdl file
   *
   * @see wsdl2phpConfig
   * @param wsdl2phpConfig $config The config to use for generation
   * @access public
   */
  public function generate(wsdl2phpConfig $config)
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
      $this->client = new SoapClient($wsdl);
    }
    catch(SoapFault $e)
    {
      throw new Exception('Error connectiong to to the wsdl. Error: '.$e->getMessage());
    }

    $this->log(_('Loading the DOM'));
    $this->dom = DOMDocument::load($wsdl);

    $this->documentation->loadDocumentation($this->dom);
    
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
    $name = $this->dom->getElementsByTagNameNS('*', 'service')->item(0)->getAttribute('name');

    $this->log(_('Starting to load service ').$name);

    $this->service = new wsdl2phpService($name, $this->types, $this->documentation->getServiceDescription());

    $functions = $this->client->__getFunctions();
    foreach($functions as $function)
    {
      $matches = array();
      if(preg_match('/^(\w[\w\d_]*) (\w[\w\d_]*)\(([\w\$\d,_ ]*)\)$/', $function, $matches))
      {
        $returns = $matches[1];
        $function = $matches[2];
        $params = $matches[3];
      }
      else if(preg_match('/^(list\([\w\$\d,_ ]*\)) (\w[\w\d_]*)\(([\w\$\d,_ ]*)\)$/', $function, $matches))
      {
        $returns = $matches[1];
        $function = $matches[2];
        $params = $matches[3];
      }
      else
      {
        // invalid function call
        throw new wsdl2phpException('Invalid function call: '.$function);
      }

      $this->log(_('Loading function ').$function);

      $this->service->addOperation($function, $params, $this->documentation->getFunctionDescription($function));
    }

    $this->log(_('Done loading service ').$name);
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

    foreach($types as $typeStr)
    {
      $parts = explode(PHP_EOL, $typeStr);
      $tArr = explode(" ", $parts[0]);
      $restriction = $tArr[0];
      $className = $tArr[1];

      if(substr($className, -2, 2) == '[]' || substr($className, 0, 7) == 'ArrayOf')
      {
        // skip arrays
        continue;
      }
      
      $type = null;
      $numParts = count($parts);
      // ComplexType
      if ($numParts > 1)
      {
        $type = new wsdl2phpComplexType($className);
        $this->log(_('Loading type ').$type->getPhpIdentifier());

        for($i = 1; $i < $numParts - 1; $i++)
        {
          $parts[$i] = trim($parts[$i]);
          list($typename, $name) = explode(" ", substr($parts[$i], 0, strlen($parts[$i])-1) );

          $name = $this->cleanNamespace($name);

          $type->addMember($typename, $name);
        }
      }
      else // Enum or Pattern
      {
        $typenode = $this->findTypenode($className);

        if ($typenode)
        {
          // If enum
          $enumerationList = $typenode->getElementsByTagName('enumeration');
          if ($enumerationList->length > 0)
          {
            $type = new wsdl2phpEnum($className, $restriction);
            $this->log(_('Loading enum ').$type->getPhpIdentifier());
            foreach ($enumerationList as $enum)
            {
              $type->addValue($enum->attributes->getNamedItem('value')->nodeValue);
            }
          }
          else // If pattern
          {
            $type = new wsdl2phpPattern($className, $restriction);
            $this->log(_('Loading pattern ').$type->getPhpIdentifier());
            $patternList = $typenode->getElementsByTagName('pattern');
            $type->setValue($patternList->item(0)->attributes->getNamedItem('value')->nodeValue);
          }
        }
      }

      if ($type != null)
      {
        $this->types[] = $type;
      }
    }

    $this->log(_('Done loading types'));
  }

  /**
   * Save all the loaded classes to the configured output dir
   *
   * @throws Exception If no service is loaded
   *
   * @access private
   */
  private function savePhp()
  {
    $service = $this->service->getClass();

    if ($service == null)
    {
      throw new wsdl2phpException('No service loaded');
    }
    
    $output = new wsdl2phpOutputManager($this->config);

    // Generate all type classes
    $types = array();
    foreach ($this->types as $type)
    {
      $class = $type->getClass();
      if ($class != null)
      {
        $types[] = $class;

        if ($this->config->getOneFile() == false)
        {
          $service->addDependency($class->getIdentifier().'.php');
        }
      }
    }
    
    $output->save($service, $types);
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

  /**
   * Returns the singleton of the generator class. This may be changed to a "better" solution but I don't know any of the top of my head
   * Used by different classes to get the loaded config
   *
   * @return wsdl2phpGenerator The dreaded singleton instance
   */
  public static function getInstance()
  {
    return self::$instance;
  }

  /**
   * Returns the loaded config
   *
   * @return wsdl2phpConfig The loaded config
   */
  public function getConfig()
  {
    return $this->config;
  }

  /**
   * Takes a string and removes the xml namespace if any
   *
   * @param string $str
   * @return string The string without namespace
   */
  private function cleanNamespace($str)
  {
    if(strpos($str, ':'))
    {
      $arr = explode(':', $str);
      $str = $arr[1];
    }

    return $str;
  }

  /**
   * Parses the type schema for a type with the name $name
   *
   * @param string $name
   * @return DOMElement|null Returns the typenode with the name $name if it finds it. Null otherwise
   */
  private function findTypenode($name)
  {
    $typenode = null;

    $schemaList = $this->dom->getElementsByTagName('types')->item(0)->getElementsByTagName('schema');

    foreach ($schemaList as $schema)
    {
      foreach ($schema->childNodes as $node)
      {
        if($node instanceof DOMElement)
        {
          if ($node->hasAttributes())
          {
            $t = $node->attributes->getNamedItem('name');
            if ($t)
            {
              if($t->nodeValue == $name)
              {
                $typenode = $node;
              }
            }
          }
        }
      }
    }

    return $typenode;
  }
}