<?php

/**
 * @package Wsdl2PhpGenerator
 */
namespace Wsdl2PhpGenerator;

use Wsdl2PhpGenerator\PhpSource\PhpClass;
use Wsdl2PhpGenerator\PhpSource\PhpDocComment;
use Wsdl2PhpGenerator\PhpSource\PhpDocElementFactory;
use Wsdl2PhpGenerator\PhpSource\PhpFunction;
use Wsdl2PhpGenerator\PhpSource\PhpVariable;

/**
 * Service represents the service in the wsdl
 *
 * @package Wsdl2PhpGenerator
 * @author Fredrik Wallgren <fredrik.wallgren@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Service implements ClassGenerator
{

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var PhpClass The class used to create the service.
     */
    private $class;

    /**
     * @var string The name of the service
     */
    private $identifier;

    /**
     * @var Operation[] An array containing the operations of the service
     */
    private $operations;

    /**
     * @var string The description of the service used as description in the phpdoc of the class
     */
    private $description;

    /**
     * @var array An array of Types
     */
    private $types;

    /**
     * @param ConfigInterface $config Configuration
     * @param string $identifier The name of the service
     * @param array $types The types the service knows about
     * @param string $description The description of the service
     */
    public function __construct(ConfigInterface $config, $identifier, array $types, $description)
    {
        $this->config = $config;
        $this->identifier = $identifier;
        $this->types = $types;
        $this->description = $description;
        $this->operations = array();
    }

    /**
     * @return PhpClass Returns the class, generates it if not done
     */
    public function getClass()
    {
        if ($this->class == null) {
            $this->generateClass();
        }

        return $this->class;
    }

    /**
     * Generates the class if not already generated
     */
    public function generateClass()
    {
        $name = $this->identifier;

        // Generate a valid classname
        $name = Validator::validateClass($name, $this->config->get('namespaceName'));

        // uppercase the name
        $name = ucfirst($name);

        // Create the class object
        $comment = new PhpDocComment($this->description);
        $this->class = new PhpClass($name, false, $this->config->get('soapClientClass'), $comment);

        // Create the constructor
        $comment = new PhpDocComment();
        $comment->addParam(PhpDocElementFactory::getParam('array', 'options', 'A array of config values'));
        $comment->addParam(PhpDocElementFactory::getParam('string', 'wsdl', 'The wsdl file to use'));

        $source = '
  foreach (self::$classmap as $key => $value) {
    if (!isset($options[\'classmap\'][$key])) {
      $options[\'classmap\'][$key] = $value;
    }
  }' . PHP_EOL;
        $source .= '  $options = array_merge(' . var_export($this->config->get('soapClientOptions'), true) . ', $options);' . PHP_EOL;
        $source .= '  parent::__construct($wsdl, $options);' . PHP_EOL;

        $function = new PhpFunction('public', '__construct', 'array $options = array(), $wsdl = \'' . $this->config->get('inputFile') . '\'', $source, $comment);

        // Add the constructor
        $this->class->addFunction($function);

        // Generate the classmap
        $name = 'classmap';
        $comment = new PhpDocComment();
        $comment->setVar(PhpDocElementFactory::getVar('array', $name, 'The defined classes'));

        $init = array();
        foreach ($this->types as $type) {
            if ($type instanceof ComplexType) {
                $init[$type->getIdentifier()] = $this->config->get('namespaceName') . "\\" . $type->getPhpIdentifier();
            }
        }
        $var = new PhpVariable('private static', $name, var_export($init, true), $comment);

        // Add the classmap variable
        $this->class->addVariable($var);

        // Add all methods
        foreach ($this->operations as $operation) {
            $name = Validator::validateOperation($operation->getName());

            $comment = new PhpDocComment($operation->getDescription());
            $comment->setReturn(PhpDocElementFactory::getReturn($operation->getReturns(), ''));

            foreach ($operation->getParams() as $param => $hint) {
                $arr = $operation->getPhpDocParams($param, $this->types);
                $comment->addParam(PhpDocElementFactory::getParam($arr['type'], $arr['name'], $arr['desc']));
            }

            $source = '  return $this->__soapCall(\'' . $operation->getName() . '\', array(' . $operation->getParamStringNoTypeHints() . '));' . PHP_EOL;

            $paramStr = $operation->getParamString($this->types);

            $function = new PhpFunction('public', $name, $paramStr, $source, $comment);

            if ($this->class->functionExists($function->getIdentifier()) == false) {
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
     * @param string $returns
     */
    public function addOperation($name, $params, $description, $returns)
    {
        $this->operations[] = new Operation($name, $params, $description, $returns);
    }

}
