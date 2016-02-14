<?php

/**
 * @package Generator
 */
namespace Wsdl2PhpGenerator;

use \Exception;
use Zend\Code\Generator\ClassGenerator as ZendClassGenerator;
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
        $constructorParamsDefaultToNull = $this->config->get('constructorParamsDefaultToNull');

        $classBaseType = $this->getBaseTypeClass();

        $this->class = (new ZendClassGenerator())
            ->setName($this->phpIdentifier)
            ->setNamespaceName(
                empty($this->config->get('namespaceName'))
                    ? null
                    : $this->config->get('namespaceName'))
            ->setFlags(
                $this->abstract
                    ? ZendClassGenerator::FLAG_ABSTRACT
                    : null)
            ->setExtendedClass($classBaseType);

        $constructor = (new MethodGenerator('__construct'))
            ->setFlags(MethodGenerator::FLAG_PUBLIC)
            ->setDocBlock(new DocBlockGenerator());

        $constructorSource = '';
        $parentConstructorParameters = [];

        $this->class->addMethodFromGenerator($constructor);

        // Add base type members to constructor parameter list first and call base class constructor
        $parentMembers = $this->getBaseTypeMembers($this);
        if (!empty($parentMembers)) {
            foreach ($parentMembers as $member) {
                $type = Validator::validateType($member->getType());
                $name = Validator::validateAttribute($member->getName());
                $typeHint = Validator::validateTypeHint($type);

                if (!$member->getNullable()) {
                    $parentConstructorParameters[] =
                        self::addMethodParameter(
                            $constructor,
                            $name, $type, $typeHint,
                            $constructorParamsDefaultToNull
                        );
                }
            }

            $parentConstructorParameters = array_map(function (ParameterGenerator $parameter) {
                return '$' . $parameter->getName();
            }, $parentConstructorParameters);
            $constructorSource .= 'parent::__construct(' . implode(', ', $parentConstructorParameters) . ');' . PHP_EOL;
        }

        // Add member variables
        foreach ($this->members as $member) {
            $type = Validator::validateType($member->getType());
            $name = Validator::validateAttribute($member->getName());
            $typeHint = Validator::validateTypeHint($type);
            $nullable = $member->getNullable();

            if (!$nullable) {
                self::addMethodParameter(
                    $constructor,
                    $name,
                    $type,
                    $typeHint,
                    $constructorParamsDefaultToNull
                );
                $constructorSource .= self::generateSetterSource(
                    $name, $type, $constructorParamsDefaultToNull
                );
            }

            $this->addProperty($name, $type);
            $this->addGetter($name, $type);
            $this->addSetter($name, $type, $typeHint, $nullable);
        }

        $constructor->setBody($constructorSource);
    }

    /**
     * Add parameter to method
     *
     * @param MethodGenerator $method
     * @param $name
     * @param $type
     * @param $typeHint
     * @param $defaultToNull
     */
    private static function addMethodParameter(MethodGenerator $method, $name, $type, $typeHint, $defaultToNull)
    {

        $method->getDocBlock()->setTag(new ParamTag($name, $type));
        $parameter = (new ParameterGenerator())
            ->setName($name);

        if (!empty($typeHint)) {
            $parameter->setType($typeHint);
        }
        if ($defaultToNull) {
            $parameter->setDefaultValue(
                new ValueGenerator(null, ValueGenerator::TYPE_NULL)
            );
        }
        $method->setParameter($parameter);

        return $parameter;
    }

    /**
     * Add property to generated class
     *
     * @param string $name
     * @param string $type
     */
    private function addProperty($name, $type)
    {
        $property = (new PropertyGenerator())
            ->setDefaultValue(new PropertyValueGenerator(null, ValueGenerator::TYPE_NULL))
            ->setDocBlock(
                (new DocBlockGenerator())
                    ->setTag(new VarTag($name, $type))
            )
            ->setFlags(PropertyGenerator::FLAG_PROTECTED)
            ->setName($name);
        $this->class->addPropertyFromGenerator($property);
    }

    /**
     * Add setter method to generated class
     *
     * @param $name
     * @param $type
     * @param $typeHint
     * @param $nullable
     */
    private function addSetter($name, $type, $typeHint, $nullable)
    {
        $setter = (new MethodGenerator())
            ->setName('set' . ucfirst($name))
            ->setFlags(MethodGenerator::FLAG_PUBLIC)
            ->setDocBlock(new DocBlockGenerator())
            ->setBody(
                self::generateSetterSource($name, $type, $nullable) .
                'return $this;' . PHP_EOL
            );

        self::addMethodParameter($setter, $name, $type, $typeHint, $nullable && !empty($typeHint));
        $setter->getDocBlock()->setTag(new ReturnTag($this->phpNamespacedIdentifier));

        $this->class->addMethodFromGenerator($setter);


    }

    /**
     * Add getter method to generated class
     *
     * @param string $name
     * @param string $type
     */
    private function addGetter($name, $type)
    {
        $this->class->addMethodFromGenerator(
            (new MethodGenerator())
                ->setName('get' . ucfirst($name))
                ->setFlags(MethodGenerator::FLAG_PUBLIC)
                ->setDocBlock((new DocBlockGenerator())
                    ->setTag(new ReturnTag($type))
                )
                ->setBody(self::generateGetterSource($name, $type))
        );
    }

    /**
     * Generate source code for setting value in constructor and setter methods
     *
     * @param $name
     * @param $type
     * @param $nullable
     * @return string
     */
    private static function generateSetterSource($name, $type, $nullable)
    {
        if ($type == '\DateTime') {
            if ($nullable) {
                return '$this->' . $name . ' = $' . $name . ' ? $' . $name . '->format(\DateTime::ATOM) : null;' . PHP_EOL;
            } else {
                return '$this->' . $name . ' = $' . $name . '->format(\DateTime::ATOM);' . PHP_EOL;
            }
        } else {
            return  '$this->' . $name . ' = $' . $name . ';' . PHP_EOL;
        }
    }

    /**
     * Generate source code for getter method
     *
     * @param string $name validated parameter name
     * @param string $type validated parameter type
     * @return string
     */
    private static function generateGetterSource($name, $type)
    {
        if ($type == '\DateTime') {
            return
                  'if ($this->' . $name . ' == null) {' . PHP_EOL
                . '    return null;' . PHP_EOL
                . '} else {' . PHP_EOL
                . '    try {' . PHP_EOL
                . '        return new \DateTime($this->' . $name . ');' . PHP_EOL
                . '    } catch (\Exception $e) {' . PHP_EOL
                . '        return false;' . PHP_EOL
                . '    }' . PHP_EOL
                . '}' . PHP_EOL;
        } else {
            return '  return $this->' . $name . ';' . PHP_EOL;
        }
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
