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
            $this->config->getClassExists(),
            $this->baseType !== null ? $this->baseType->getPhpIdentifier() : ''
        );

        // Add the base class as a dependency. Otherwise we risk referencing an undefined class.
        if (!empty($this->baseType) && !$this->config->getOneFile() && !$this->config->getNoIncludes()) {
            $class->addDependency($this->baseType->getPhpIdentifier() . '.php');
        }

        $constructorComment = new PhpDocComment();
        $constructorComment->setAccess(PhpDocElementFactory::getPublicAccess());
        $constructorSource = '';
        $constructorParameters = '';
        $accessors = array();

        // Add base type members to constructor parameter list first and call base class constructor
        $parentMembers = $this->getBaseTypeMembers($this);
        if (!empty($parentMembers)) {
            foreach ($parentMembers as $member) {
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
            $constructorSource .= '  parent::__construct(' . str_replace(' = null', '', substr($constructorParameters, 2)) . ');' . PHP_EOL;
        }

        // Add member variables
        foreach ($this->members as $member) {
            $type = Validator::validateType($member->getType());
            $name = Validator::validateAttribute($member->getName());

            $comment = new PhpDocComment();
            $comment->setVar(PhpDocElementFactory::getVar($type, $name, ''));
            $comment->setAccess(PhpDocElementFactory::getPublicAccess());
            $var = new PhpVariable('public', $name, 'null', $comment);
            $class->addVariable($var);

            if (!$member->getNillable()) {
                $constructorSource .= '  $this->' . $name . ' = $' . $name . ';' . PHP_EOL;
                $constructorComment->addParam(PhpDocElementFactory::getParam($type, $name, ''));
                $constructorComment->setAccess(PhpDocElementFactory::getPublicAccess());
                $constructorParameters .= ', $' . $name;
                if ($this->config->getConstructorParamsDefaultToNull()) {
                    $constructorParameters .= ' = null';
                }
            }

            if ($this->config->getCreateAccessors()) {
                $getterComment = new PhpDocComment();
                $getterComment->setReturn(PhpDocElementFactory::getReturn($type, ''));
                $getter = new PhpFunction('public', 'get' . ucfirst($name), '', '  return $this->' . $name . ';' . PHP_EOL, $getterComment);
                $accessors[] = $getter;

                $setterComment = new PhpDocComment();
                $setterComment->addParam(PhpDocElementFactory::getParam($type, $name, ''));
                $setterComment->setReturn(PhpDocElementFactory::getReturn($this->phpNamespacedIdentifier, ''));
                $source  = '  $this->' . $name . ' = $' . $name . ';' . PHP_EOL . '  return $this;' . PHP_EOL;
                $setter = new PhpFunction('public', 'set' . ucfirst($name), '$' . $name, $source, $setterComment);
                $accessors[] = $setter;
            }
        }

        $constructorParameters = substr($constructorParameters, 2); // Remove first comma
        $function = new PhpFunction('public', '__construct', $constructorParameters, $constructorSource, $constructorComment);

        // Only add the constructor if type constructor is selected
        if ($this->config->getNoTypeConstructor() == false) {
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
     * @param bool $nillable
     */
    public function addMember($type, $name, $nillable)
    {
        $this->members[$name] = new Variable($type, $name, $nillable);
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
        $members = array();
        if (!empty($type->baseType)) {
            $members = array_merge($this->getBaseTypeMembers($type->baseType), $type->baseType->getMembers());
        }
        return $members;
    }
}
