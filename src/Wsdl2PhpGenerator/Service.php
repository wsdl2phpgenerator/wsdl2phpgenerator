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
class Service
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

        // Add prefix and suffix
        $name = $this->config->getPrefix() . $this->identifier . $this->config->getSuffix();

        // Generate a valid classname
        try {
            $name = Validator::validateClass($name);
        } catch (ValidationException $e) {
            $name .= 'Custom';
        }

        // uppercase the name
        $name = ucfirst($name);

        // Create the class object
        $comment = new PhpDocComment($this->description);
        $this->class = new PhpClass($name, $this->config->getClassExists(), '\SoapClient', $comment);

        // Create the constructor
        $comment = new PhpDocComment();
        $comment->addParam(PhpDocElementFactory::getParam('array', 'options', 'A array of config values'));
        $comment->addParam(PhpDocElementFactory::getParam('string', 'wsdl', 'The wsdl file to use'));
        $comment->setAccess(PhpDocElementFactory::getPublicAccess());

        $source = '  foreach (self::$classmap as $key => $value) {
    if (!isset($options[\'classmap\'][$key])) {
      $options[\'classmap\'][$key] = $value;
    }
  }
  ' . $this->generateServiceOptions() . '
  parent::__construct($wsdl, $options);' . PHP_EOL;

        $function = new PhpFunction('public', '__construct', 'array $options = array(), $wsdl = \'' . $this->config->getInputFile() . '\'', $source, $comment);

        // Add the constructor
        $this->class->addFunction($function);

        // Generate the classmap
        $name = 'classmap';
        $comment = new PhpDocComment();
        $comment->setAccess(PhpDocElementFactory::getPrivateAccess());
        $comment->setVar(PhpDocElementFactory::getVar('array', $name, 'The defined classes'));

        $init = 'array(' . PHP_EOL;
        foreach ($this->types as $type) {
            if ($type instanceof ComplexType) {
                $init .= "  '" . $type->getIdentifier() . "' => '" . $this->config->getNamespaceName() . "\\" . $type->getPhpIdentifier() . "'," . PHP_EOL;
            }
        }
        $init = substr($init, 0, strrpos($init, ','));
        $init .= ')';
        $var = new PhpVariable('private static', $name, $init, $comment);

        // Add the classmap variable
        $this->class->addVariable($var);

        // Add all methods
        foreach ($this->operations as $operation) {
            $name = Validator::validateNamingConvention($operation->getName());

            $comment = new PhpDocComment($operation->getDescription());
            $comment->setAccess(PhpDocElementFactory::getPublicAccess());
            $comment->setReturn(PhpDocElementFactory::getReturn($operation->getReturns(), ''));

            foreach ($operation->getParams() as $param => $hint) {
                $arr = $operation->getPhpDocParams($param, $this->types);
                $comment->addParam(PhpDocElementFactory::getParam($arr['type'], $arr['name'], $arr['desc']));
            }

            $source = '  return $this->__soapCall(\'' . $name . '\', array(' . $operation->getParamStringNoTypeHints() . '));' . PHP_EOL;

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

    /**
     * @return string Returns the string for the options array
     */
    private function generateServiceOptions()
    {
        $ret = '';

        if (count($this->config->getOptionFeatures()) > 0) {
            $i = 0;
            $ret .= "
  if (isset(\$options['features']) == false) {
    \$options['features'] = ";
            foreach ($this->config->getOptionFeatures() as $option) {
                if ($i++ > 0) {
                    $ret .= ' | ';
                }

                $ret .= $option;
            }

            $ret .= ";
  }" . PHP_EOL;
        }

        if (strlen($this->config->getWsdlCache()) > 0) {
            $ret .= "
  if (isset(\$options['wsdl_cache']) == false) {
    \$options['wsdl_cache'] = " . $this->config->getWsdlCache();
            $ret .= ";
  }" . PHP_EOL;
        }

        if (strlen($this->config->getCompression()) > 0) {
            $ret .= "
  if (isset(\$options['compression']) == false) {
    \$options['compression'] = " . $this->config->getCompression();
            $ret .= ";
  }" . PHP_EOL;
        }

        return $ret;
    }
}
