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
     * Generates constructor parameters from baseType tree
     *
     * @param ComplexType $baseType
     * @param PhpDocComment $constructorComment
     * @param string $constructorParameters
     */
    protected function generateConstructorFromBaseType(ComplexType $baseType, PhpDocComment $constructorComment, &$constructorParameters)
    {
        if ($baseType->baseType !== null) {
            $this->generateConstructorFromBaseType($baseType->baseType, $constructorComment, $constructorParameters);
        }
        foreach ($baseType->getMembers() as $member) {
            $type = Validator::validateType($member->getType());
            $name = Validator::validateAttribute($member->getName());

            if (!$member->getNillable()) {
                $constructorComment->addParam(PhpDocElementFactory::getParam($type, $name, ''));
                $constructorComment->setAccess(PhpDocElementFactory::getPublicAccess());
                $constructorParameters .= ', $' . $name;
                if ($this->config->getConstructorParamsDefaultToNull()) {
                    $constructorParameters .= ' = null';
                }
            }
        }
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
            $var = new PhpVariable('protected', $name, 'null', $comment);
            $class->addVariable($var);

            if (!$member->getNullable()) {
                if ($type == '\DateTime') {
                    $constructorSource .= '  $this->' . $name . ' = $' . $name . '->format(\DateTime::ATOM);' . PHP_EOL;
                } else {
                    $constructorSource .= '  $this->' . $name . ' = $' . $name . ';' . PHP_EOL;
                }
                $constructorComment->addParam(PhpDocElementFactory::getParam($type, $name, ''));
                $constructorParameters[$name] = $typeHint;
            }

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

        $constructor = new PhpFunction(
            'public',
            '__construct',
            $this->buildParametersString(
                $constructorParameters,
                true,
                $this->config->get('constructorParamsDefaultToNull')
            ),
            $constructorSource,
            $constructorComment
        );
        $class->addFunction($constructor);

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
     * @param bool $defaultNull Whether to set the default value of parameters to null.
     * @return string The parameter string.
     */
    protected function buildParametersString(array $parameters, $includeType = true, $defaultNull = false)
    {
        $parameterStrings = array();
        foreach ($parameters as $name => $type) {
            $parameterString = '$' . $name;
            if (!empty($type) && $includeType) {
                $parameterString = $type . ' ' . $parameterString;
            }
            if ($defaultNull) {
                $parameterString .= ' = null';
            }
            $parameterStrings[] = $parameterString;
        }
        return implode(', ', $parameterStrings);
    }
}
