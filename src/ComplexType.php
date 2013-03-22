<?php

/**
 * @package Generator
 */

/**
 * @see Type
 */
require_once dirname(__FILE__) . '/Type.php';

/**
 * @see Variable
 */
require_once dirname(__FILE__) . '/Variable.php';

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
     *
     * @var array The members in the type
     */
    private $members;

    /**
     * Construct the object
     *
     * @param string $name The identifier for the class
     * @param string $restriction The restriction(datatype) of the values
     */
    public function __construct($name)
    {
        parent::__construct($name, null);
        $this->members = array();
    }

    /**
     * Implements the loading of the class object
     * @throws Exception if the class is already generated(not null)
     */
    protected function generateClass()
    {
        if ($this->class != null) {
            throw new Exception("The class has already been generated");
        }

        $config = Generator::getInstance()->getConfig();

        $class = new PhpClass($this->phpIdentifier, $config->getClassExists());

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
                if ($config->getConstructorParamsDefaultToNull()) {
                    $constructorParameters .= ' = null';
                }

                if ($config->getCreateAccessors()) {
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
        if ($config->getNoTypeConstructor() == false) {
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
     */
    public function addMember($type, $name, $nillable)
    {
        $this->members[$name] = new Variable($type, $name, $nillable);
    }
}
