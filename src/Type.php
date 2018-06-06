<?php

/**
 * @package Wsdl2PhpGenerator
 */
namespace Wsdl2PhpGenerator;

use Wsdl2PhpGenerator\PhpSource\PhpClass;

/**
 * Type is an abstract baseclass for all types in the wsdl
 *
 * @package Wsdl2PhpGenerator
 * @author Fredrik Wallgren <fredrik.wallgren@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
abstract class Type implements ClassGenerator
{

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var PhpClass The class used to create the type. This is not used by patterns
     */
    protected $class;

    /**
     * @var string The name of the type
     */
    protected $identifier;

    /**
     * @var string The name of the type used in php code ie. the validated name
     */
    protected $phpIdentifier;

    /**
     * @var string The name of the type used in php code with namespace (if needed) ie. the validated name
     */
    protected $phpNamespacedIdentifier;
    
    /**
     * @var string The datatype the simple type is of. This not used by complex types
     */
    protected $datatype;

    /**
     * The minimum construction
     *
     * @param ConfigInterface $config The configuration
     * @param string $name The identifier for the type
     * @param string $datatype The restriction(DataType)
     */
    public function __construct(ConfigInterface $config, $name, $datatype)
    {
        $this->config = $config;
        $this->class = null;
        $this->datatype = $datatype;
        $this->identifier = $name;

        $this->phpIdentifier = Validator::validateClass($name, $this->config->get('namespaceName'));
        $this->phpNamespacedIdentifier = $name;
        if ($this->config->get('namespaceName')) {
            $this->phpNamespacedIdentifier = '\\' . $this->config->get('namespaceName') . '\\' . $name;
        }
    }

    /**
     * The abstract function for subclasses to implement
     * This should load the class data into $class
     * This is called by getClass if not previously called
     */
    abstract protected function generateClass();

    /**
     * Getter for the class. Generates the class if it's null
     *
     * @return PhpClass
     */
    public function getClass()
    {
        if ($this->class == null) {
            $this->generateClass();
        }

        return $this->class;
    }

    /**
     * Getter for the datatype
     *
     * @return string
     */
    public function getDatatype()
    {
        return $this->datatype;
    }

    /**
     * Getter for the name
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return string The validated name of the type
     */
    public function getPhpIdentifier()
    {
        return $this->phpIdentifier;
    }
}
