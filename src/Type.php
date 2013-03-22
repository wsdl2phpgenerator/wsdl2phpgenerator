<?php

/**
 * @package Wsdl2PhpGenerator
 */

/**
 * @see PhpClass
 */
require_once dirname(__FILE__) . '/../lib/phpSource/PhpClass.php';

/**
 * @see Validator
 */
require_once dirname(__FILE__) . '/Validator.php';

/**
 * Type is an abstract baseclass for all types in the wsdl
 *
 * @package Wsdl2PhpGenerator
 * @author Fredrik Wallgren <fredrik.wallgren@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
abstract class Type
{
    /**
     *
     * @var PhpClass The class used to create the type. This is not used by patterns
     */
    protected $class;

    /**
     *
     * @var string The name of the type
     */
    protected $identifier;

    /**
     *
     * @var string The name of the type used in php code ie. the validated name
     */
    protected $phpIdentifier;

    /**
     *
     * @var string The datatype the simple type is of. This not used by complex types
     */
    protected $datatype;

    /**
     * The minimum construction
     *
     * @param string $name The identifier for the type
     * @param string $datatype The restriction(DataType)
     */
    public function __construct($name, $datatype)
    {
        $this->class = null;
        $this->datatype = $datatype;
        $this->identifier = $name;

        $config = Generator::getInstance()->getConfig();

        // Add prefix and suffix
        $name = $config->getPrefix() . $this->identifier . $config->getSuffix();

        try {
            $name = Validator::validateClass($name);
        } catch (ValidationException $e) {
            $name .= 'Custom';
        }

        $this->phpIdentifier = $name;
    }

    /**
     * The abstract function for subclasses to implement
     * This should load the class data into $class
     * This is called by getClass if not previosly called
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
     *
     * @return string The validated name of the type
     */
    public function getPhpIdentifier()
    {
        return $this->phpIdentifier;
    }
}
