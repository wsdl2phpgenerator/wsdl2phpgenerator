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
     * The members in the type
     *
     * @var array
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

        $class = new PhpClass($this->phpIdentifier, $this->config->getClassExists());

        $constructorComment = new PhpDocComment();
        $constructorComment->setAccess(PhpDocElementFactory::getPublicAccess());
        $constructorSource = '';
        $constructorParameters = '';
        $accessors = array();

        // Add member variables
        foreach ($this->members as $member) {
            $type = '';

            try {
                $type = Validator::validateType($member->getType());
            } catch (ValidationException $e) {
                $type .= 'Custom';
            }

            $name = Validator::validateNamingConvention($member->getName());
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

                if ($this->config->getCreateAccessors()) {
                    $getterComment = new PhpDocComment();
                    $getterComment->setReturn(PhpDocElementFactory::getReturn($type, ''));
                    $getter = new PhpFunction('public', 'get' . ucfirst($name), '', '  return $this->' . $name . ';' . PHP_EOL, $getterComment);
                    $accessors[] = $getter;

                    $setterComment = new PhpDocComment();
                    $setterComment->addParam(PhpDocElementFactory::getParam($type, $name, ''));
                    $setter = new PhpFunction('public', 'set' . ucfirst($name), '$' . $name, '  $this->' . $name . ' = $' . $name . ';' . PHP_EOL, $setterComment);
                    $accessors[] = $setter;
                }
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
}
