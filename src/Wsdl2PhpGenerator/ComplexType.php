<?php

/**
 * @package Generator
 */
namespace Wsdl2PhpGenerator;

use \Exception;
use Wsdl2PhpGenerator\PhpSource\PhpClass;
use Wsdl2PhpGenerator\PhpSource\PhpDocComment;
use Wsdl2PhpGenerator\PhpSource\PhpDocElementFactory;
use Wsdl2PhpGenerator\PhpSource\PhpFunction;
use Wsdl2PhpGenerator\PhpSource\PhpVariable;

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
     * Base type that the type extends
     *
     * @var ComplexType
     */
    private $baseType;

    /**
     * The members in the type
     *
     * @var Variable[]
     */
    private $members;

    /**
     * Construct the object
     *
     * @param ConfigInterface $config The configuration
     * @param string $name The identifier for the class
     */
    public function __construct(ConfigInterface $config, $name)
    {
        parent::__construct($config, $name, null);
        $this->members = array();
        $this->baseType = null;
    }

    /**
     * Implements the loading of the class object
     *
     * @throws Exception if the class is already generated(not null)
     */
    protected function generateClass()
    {
        if ($this->class != null) {
            throw new Exception("The class has already been generated");
        }

        $class = new PhpClass(
            $this->phpIdentifier,
            false,
            $this->baseType !== null ? $this->baseType->getPhpIdentifier() : ''
        );

        // Add the base class as a dependency. Otherwise we risk referencing an undefined class.
        if (!empty($this->baseType) && !$this->config->get('oneFile') && !$this->config->get('noIncludes')) {
            $class->addDependency($this->baseType->getPhpIdentifier() . '.php');
        }

        $constructorComment = new PhpDocComment();
        $constructorSource = '';
        $constructorParameters = array();
        $accessors = array();

        // Add base type members to constructor parameter list first and call base class constructor
        if ($this->baseType !== null) {
            foreach ($this->baseType->getMembers() as $member) {
                $type = Validator::validateType($member->getType());
                $name = Validator::validateAttribute($member->getName());

                if (!$member->getNullable()) {
                    $constructorComment->addParam(PhpDocElementFactory::getParam($type, $name, ''));
                    if ($this->config->get('constructorParamsDefaultToNull')) {
                        // This is somewhat hacky but we do no have function parameters as a class yet.
                        $name .= ' = null';
                    }
                    $constructorParameters[$name] = Validator::validateTypeHint($type);
                }
            }
            $constructorSource .= '  parent::__construct(' . $this->buildParametersString($constructorParameters, false) . ');' . PHP_EOL;
        }

        // Add member variables
        foreach ($this->members as $member) {
            $type = Validator::validateType($member->getType());
            $name = Validator::validateAttribute($member->getName());
            $typeHint = Validator::validateTypeHint($type);

            $comment = new PhpDocComment();
            $comment->setVar(PhpDocElementFactory::getVar($type, $name, ''));
            if ($this->config->get('createAccessors')) {
                $var = new PhpVariable('protected', $name, 'null', $comment);
            } else {
                $var = new PhpVariable('public', $name, 'null', $comment);
            }
            $class->addVariable($var);

            if (!$member->getNullable()) {
                if ($type == '\DateTime') {
                    $constructorSource .= '  $this->' . $name . ' = $' . $name . '->format(\DateTime::ATOM);' . PHP_EOL;
                } else {
                    $constructorSource .= '  $this->' . $name . ' = $' . $name . ';' . PHP_EOL;
                }
                $constructorComment->addParam(PhpDocElementFactory::getParam($type, $name, ''));
                $constructorName = $name;
                if ($this->config->get('constructorParamsDefaultToNull')) {
                    // More hackery with with parameter names..
                    $constructorName .= ' = null';
                }
                $constructorParameters[$constructorName] = $typeHint;
            }

            if ($this->config->get('createAccessors')) {
                $getterComment = new PhpDocComment();
                $getterComment->setReturn(PhpDocElementFactory::getReturn($type, ''));
                $getterCode = '';
                if ($type == '\DateTime') {
                    $getterCode = '  if ($this->' . $name . ' == null) {' . PHP_EOL
                        . '    return null;' . PHP_EOL
                        . '  } else {' . PHP_EOL
                        . '    return \DateTime::createFromFormat(\DateTime::ATOM, $this->' . $name . ');' . PHP_EOL
                        . '  }' . PHP_EOL;
                } else {
                    $getterCode = '  return $this->' . $name . ';' . PHP_EOL;
                }
                $getter = new PhpFunction('public', 'get' . ucfirst($name), '', $getterCode, $getterComment);
                $accessors[] = $getter;

                $setterComment = new PhpDocComment();
                $setterComment->addParam(PhpDocElementFactory::getParam($type, $name, ''));
                $setterComment->setReturn(PhpDocElementFactory::getReturn($this->phpNamespacedIdentifier, ''));
                $setterCode = '';
                if ($type == '\DateTime') {
                    $setterCode = '  $this->' . $name . ' = $' . $name . '->format(\DateTime::ATOM);' . PHP_EOL;
                } else {
                    $setterCode = '  $this->' . $name . ' = $' . $name . ';' . PHP_EOL;
                }
                $setterCode .= '  return $this;' . PHP_EOL;
                $setter = new PhpFunction('public', 'set' . ucfirst($name), $this->buildParametersString(array($name => $typeHint)), $setterCode, $setterComment);
                $accessors[] = $setter;
            }
        }

        $function = new PhpFunction('public', '__construct', $this->buildParametersString($constructorParameters), $constructorSource, $constructorComment);

        // Only add the constructor if type constructor is selected
        if ($this->config->get('noTypeConstructor') == false) {
            $class->addFunction($function);
        }

        foreach ($accessors as $accessor) {
            $class->addFunction($accessor);
        }

        $this->class = $class;
    }

    /**
     * Set the base type
     *
     * @param ComplexType $type
     */
    public function setBaseType(ComplexType $type)
    {
        $this->baseType = $type;
    }

    /**
     * Adds the member. Owerwrites members with same name
     *
     * @param string $type
     * @param string $name
     * @param bool $nullable
     */
    public function addMember($type, $name, $nullable)
    {
        $this->members[$name] = new Variable($type, $name, $nullable);
    }

    /**
     * Get type member list
     *
     * @return Variable[]
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * Generate a string representing the parameters for a function e.g. "type1 $param1, type2 $param2, $param3"
     *
     * @param array $parameters A map of parameters. Keys are parameter names and values are parameter types.
     *                          Parameter types may be empty. In that case they are not used.
     * @param bool $includeType Whether to include the parameters types in the string
     * @return string The parameter string.
     */
    protected function buildParametersString(array $parameters, $includeType = true)
    {
        $parameterStrings = array();
        foreach ($parameters as $name => $type) {
            $parameterStrings[] = (!empty($type) && $includeType) ? $type . ' $' . $name : '$' . $name;
        }
        return implode(', ', $parameterStrings);
    }
}
