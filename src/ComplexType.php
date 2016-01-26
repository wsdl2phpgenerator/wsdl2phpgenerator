<?php

/**
 * @package Generator
 */
namespace Wsdl2PhpGenerator;

use \Exception;
use Wsdl2PhpGenerator\ZendCode\VarTag;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\DocBlock\Tag\ParamTag;
use Zend\Code\Generator\DocBlock\Tag\ReturnTag;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\ParameterGenerator;
use Zend\Code\Generator\PropertyGenerator;
use Zend\Code\Generator\PropertyValueGenerator;
use Zend\Code\Generator\ValueGenerator;

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
    protected $baseType;

    /**
     * The members in the type
     *
     * @var Variable[]
     */
    protected $members;

    /**
     * @var
     */
    protected $abstract;

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
        $this->abstract = false;
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

        $classBaseType = $this->getBaseTypeClass();

        $this->class = new ClassGenerator(
            $this->phpIdentifier,
            empty($this->config->get('namespaceName'))
                ? null
                : $this->config->get('namespaceName'),
            $this->abstract
                ? ClassGenerator::FLAG_ABSTRACT
                : null,
            $classBaseType
        );

        $constructor = new MethodGenerator('__construct');
        $constructor->setFlags(MethodGenerator::FLAG_PUBLIC);

        $constructorDocBlock = new DocBlockGenerator();

        $constructorSource = '';
        $parentConstructorParameters = array();

        $this->class->addMethodFromGenerator($constructor);

        // Add base type members to constructor parameter list first and call base class constructor
        $parentMembers = $this->getBaseTypeMembers($this);
        if (!empty($parentMembers)) {
            foreach ($parentMembers as $member) {
                $type = Validator::validateType($member->getType());
                $name = Validator::validateAttribute($member->getName());

                if (!$member->getNullable()) {
                    $constructorDocBlock->setTag(
                        new ParamTag($name, $type)
                    );

                    $parameter = new ParameterGenerator();
                    $parameter->setName($name);

                    $typeHint = Validator::validateTypeHint($type);
                    if (!empty($typeHint))
                    {
                        $parameter->setType($typeHint);
                    }

                    if ($type == '\DateTime' || $this->config->get('constructorParamsDefaultToNull')) {
                        $parameter->setDefaultValue(
                            new ValueGenerator(null, ValueGenerator::TYPE_NULL)
                        );
                    }

                    $constructor->setParameter($parameter);
                    $parentConstructorParameters[$name] = Validator::validateTypeHint($type);
                }
            }
            $constructorSource .= 'parent::__construct(' . $this->buildParametersString($parentConstructorParameters, false) . ');' . PHP_EOL;
        }

        // Add member variables
        foreach ($this->members as $member) {
            $type = Validator::validateType($member->getType());
            $name = Validator::validateAttribute($member->getName());
            $typeHint = Validator::validateTypeHint($type);

            $property = new PropertyGenerator();
            $property
                ->setDefaultValue(new PropertyValueGenerator(null, ValueGenerator::TYPE_NULL))
                ->setDocBlock(
                    (new DocBlockGenerator())
                        ->setTag(new VarTag($name, $type))
                )
                ->setFlags(PropertyGenerator::FLAG_PROTECTED)
                ->setName($name);

            $this->class->addPropertyFromGenerator($property);

            if (!$member->getNullable()) {
                if ($type == '\DateTime') {
                    if ($this->config->get('constructorParamsDefaultToNull')) {
                        $constructorSource .= '$this->' . $name . ' = $' . $name . ' ? $' . $name . '->format(\DateTime::ATOM) : null;' . PHP_EOL;
                    } else {
                        $constructorSource .= '$this->' . $name . ' = $' . $name . '->format(\DateTime::ATOM);' . PHP_EOL;
                    }
                } else {
                    $constructorSource .= '$this->' . $name . ' = $' . $name . ';' . PHP_EOL;
                }

                $constructorParameter = new ParameterGenerator();
                $constructorParameter->setName($name);
                if (!empty($typeHint)) {
                    $constructorParameter->setType($typeHint);
                }
                if ($type == '\DateTime' || $this->config->get('constructorParamsDefaultToNull')) {
                    $constructorParameter->setDefaultValue(
                        new ValueGenerator(null, ValueGenerator::TYPE_NULL)
                    );
                }

                $constructor->setParameter($constructorParameter);
                $constructorDocBlock->setTag(new ParamTag($name, $type));
            }

            $getter = new MethodGenerator();
            $getter
                ->setName('get' . ucfirst($name))
                ->setFlags(MethodGenerator::FLAG_PUBLIC)
                ->setDocBlock((new DocBlockGenerator())
                    ->setTag(new ReturnTag($type))
                );

            if ($type == '\DateTime') {
                $getterCode = '  if ($this->' . $name . ' == null) {' . PHP_EOL
                    . '    return null;' . PHP_EOL
                    . '  } else {' . PHP_EOL
                    . '    try {' . PHP_EOL
                    . '      return new \DateTime($this->' . $name . ');' . PHP_EOL
                    . '    } catch (\Exception $e) {' . PHP_EOL
                    . '      return false;' . PHP_EOL
                    . '    }' . PHP_EOL
                    . '  }' . PHP_EOL;
            } else {
                $getterCode = '  return $this->' . $name . ';' . PHP_EOL;
            }

            $getter->setBody($getterCode);
            $this->class->addMethodFromGenerator($getter);

            $setterParameter = new ParameterGenerator;
            $setterParameter->setName($name);

            if (isset($typeHint)) {
                $setterParameter->setType($typeHint);

                if ($member->getNullable()) {
                    $setterParameter->setDefaultValue(
                        new ValueGenerator(null, ValueGenerator::TYPE_NULL)
                    );
                }
            }

            $setter =  new MethodGenerator();
            $setter
                ->setParameter($setterParameter)
                ->setName('set' . ucfirst($name))
                ->setFlags(MethodGenerator::FLAG_PUBLIC)
                ->setDocBlock((new DocBlockGenerator())
                    ->setTag(new ParamTag($name, $type))
                    ->setTag(new ReturnTag($this->phpNamespacedIdentifier))
                );

            if ($type == '\DateTime') {
                if ($member->getNullable()) {
                    $setterCode = 'if ($' . $name . ' == null) {' . PHP_EOL
                        . '     $this->' . $name . ' = null;' . PHP_EOL
                        . '} else {' . PHP_EOL
                        . '    $this->' . $name . ' = $' . $name . '->format(\DateTime::ATOM);' . PHP_EOL
                        . '}' . PHP_EOL;
                } else {
                    $setterCode = '$this->' . $name . ' = $' . $name . '->format(\DateTime::ATOM);' . PHP_EOL;
                }
            } else {
                $setterCode = '$this->' . $name . ' = $' . $name . ';' . PHP_EOL;
            }
            $setterCode .= 'return $this;' . PHP_EOL;

            $setter->setBody($setterCode);
            $this->class->addMethodFromGenerator($setter);
        }

        if (!empty($constructorDocBlock->getTags())) {
            $constructor->setDocBlock($constructorDocBlock);
        }

        $constructor->setBody($constructorSource);
    }

    /**
     * Determine parent class
     *
     * @return string|null
     *   Returns a string containing the PHP identifier for the parent class
     *   or null if there is no applicable parent class.
     */
    public function getBaseTypeClass()
    {
        // If we have a base type which is different than the current class then extend that.
        // It is actually possible to have different classes with the same name as PHP SoapClient has a poor
        // understanding of namespaces. Two types with the same name but in different namespaces will have the same
        // identifier.
        if ($this->baseType !== null && $this->baseType !== $this) {
            return $this->baseType->getPhpIdentifier();
        }

        return null;
    }

    /**
     * Returns the base type for the type if any.
     *
     * @return ComplexType|null
     *   The base type or null if the type has no base type.
     */
    public function getBaseType()
    {
        return $this->baseType;
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
     * @return bool
     */
    public function getAbstract()
    {
        return $this->abstract;
    }

    /**
     * @param bool $abstract
     */
    public function setAbstract($abstract)
    {
        $this->abstract = $abstract;
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

    /**
     * Get members from base types all the way through the type hierarchy.
     *
     * @param ComplexType $type The type to retrieve base type members from.
     * @return Variable[] Member variables from all base types.
     */
    protected function getBaseTypeMembers(ComplexType $type)
    {
        if (empty($type->baseType)) {
            return array();
        }

        // Only get members from the base type if it differs from the current class. It is possible that they will be
        // the same due to poor handling of namespaces in PHP SoapClients.
        if ($type === $type->baseType) {
            return array();
        }

        return array_merge($this->getBaseTypeMembers($type->baseType), $type->baseType->getMembers());
    }
}
