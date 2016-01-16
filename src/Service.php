<?php

/**
 * @package Wsdl2PhpGenerator
 */
namespace Wsdl2PhpGenerator;

use Wsdl2PhpGenerator\PhpSource\PhpClass;
use Wsdl2PhpGenerator\ZendCode\VarTag;
use Zend\Code\Generator\ClassGenerator as ZendClassGenerator;
use Zend\Code\Generator\DocBlock\Tag\ParamTag;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\ParameterGenerator;
use Zend\Code\Generator\PropertyGenerator;
use Zend\Code\Generator\PropertyValueGenerator;
use Zend\Code\Generator\ValueGenerator;
use Zend\Code\Generator\DocBlock\Tag\ReturnTag;

/**
 * Service represents the service in the wsdl
 *
 * @package Wsdl2PhpGenerator
 * @author Fredrik Wallgren <fredrik.wallgren@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Service implements ClassGenerator
{

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var ZendClassGenerator The class used to create the service.
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
     * @var Type[] An array of Types
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
        $this->description = $description;
        $this->operations = array();
        $this->types = array();
        foreach ($types as $type) {
            $this->types[$type->getIdentifier()] = $type;
        }
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
     * Returns an operation provided by the service based on its name.
     *
     * @param string $operationName The name of the operation.
     *
     * @return Operation|null The operation or null if it does not exist.
     */
    public function getOperation($operationName)
    {
        return isset($this->operations[$operationName])? $this->operations[$operationName]: null;
    }

    /**
     * Returns the description of the service.
     *
     * @return string The service description.
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns the identifier for the service ie. the name.
     *
     * @return string The service name.
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Returns a type used by the service based on its name.
     *
     * @param string $identifier The identifier for the type.
     *
     * @return Type|null The type or null if the type does not exist.
     */
    public function getType($identifier)
    {
        return isset($this->types[$identifier])? $this->types[$identifier]: null;
    }
    /**
     * Returns all types defined by the service.
     *
     * @return Type[] An array of types.
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * Generates the class if not already generated
     */
    public function generateClass()
    {
        $name = $this->identifier;

        // Generate a valid classname
        $name = Validator::validateClass($name, $this->config->get('namespaceName'));

        // uppercase the name
        $name = ucfirst($name);

        // Create the class object
        $this->class = new ZendClassGenerator();
        $this->class->setName($name);
        if ($this->config->get('namespaceName'))
        {
            $this->class->setNamespaceName($this->config->get('namespaceName'));
        }
        $this->class->setExtendedClass($this->config->get('soapClientClass'));
        $this->class->setDocBlock(
            (new DocBlockGenerator)
                ->setLongDescription($this->description)
        );

        $constructorComment = new DocBlockGenerator();
        $constructorComment->setTag(new ParamTag('options', 'array', 'A array of config values'));
        $constructorComment->setTag(new ParamTag('wsdl', 'string', 'The wsdl file to use'));

        $source = '
foreach (self::$classmap as $key => $value) {
    if (!isset($options[\'classmap\'][$key])) {
        $options[\'classmap\'][$key] = $value;
    }
}' . PHP_EOL;
        $source .= '$options = array_merge(' . var_export($this->config->get('soapClientOptions'), true) . ', $options);' . PHP_EOL;
        $source .= 'if (!$wsdl) {' . PHP_EOL;
        $source .= '    $wsdl = \'' . $this->config->get('inputFile') . '\';' . PHP_EOL;
        $source .= '}' . PHP_EOL;
        $source .= 'parent::__construct($wsdl, $options);' . PHP_EOL;

        $this->class->addMethodFromGenerator(
            (new MethodGenerator())
                ->setBody($source)
                ->setParameter(
                    (new ParameterGenerator())
                        ->setName('options')
                        ->setType('array')
                        ->setDefaultValue(array())
                )
                ->setParameter(
                    (new ParameterGenerator())
                        ->setName('wsdl')
                        ->setDefaultValue(
                            new ValueGenerator(null, ValueGenerator::TYPE_NULL)
                        )
                )
                ->setDocBlock($constructorComment)
                ->setName('__construct')
                ->setFlags(MethodGenerator::FLAG_PUBLIC)
        );

        $init = array();
        foreach ($this->types as $type) {
            if ($type instanceof ComplexType) {
                $init[$type->getIdentifier()] = $this->config->get('namespaceName') . "\\" . $type->getPhpIdentifier();
            }
        }

        $classmapValue = new PropertyValueGenerator();
        $classmapValue
            ->setType(PropertyValueGenerator::TYPE_ARRAY)
            ->setValue($init);

        $this->class->addPropertyFromGenerator(
            (new PropertyGenerator)
                ->setDefaultValue($classmapValue)
                ->setName('classmap')
                ->setFlags(PropertyGenerator::FLAG_PRIVATE | PropertyGenerator::FLAG_STATIC)
                ->setDocBlock(
                    (new DocBlockGenerator)
                        ->setTag(
                            new VarTag('classmap', 'array', 'The defined classes')
                        )
                )
        );


        // Add all methods
        foreach ($this->operations as $operation) {
            $name = Validator::validateOperation($operation->getName());

            $docBlock = new DocBlockGenerator();
            $docBlock->setTag(
                new ReturnTag($operation->getReturns())
            );

            $method = new MethodGenerator();
            $method->setName($name);
            $method->setDocBlock($docBlock);
            $method->setFlags(MethodGenerator::FLAG_PUBLIC);

            foreach ($operation->getParams() as $param => $hint) {
                $arr = $operation->getPhpDocParams($param, $this->types);

                $docBlock->setTag(
                    new ParamTag($arr['name'], $arr['type'], $arr['desc'])
                );

                $method->setParameter(
                    (new ParameterGenerator())
                        //TODO: realy need ltrim's here, or need to change Operation behaviour?
                        ->setName(ltrim($param, '$'))
                        ->setType(
                            $this->class->getNamespaceName() . '\\' . $hint
                        )
                );
            }

            $source = 'return $this->__soapCall(\'' . $operation->getName() . '\', array(' . $operation->getParamStringNoTypeHints() . '));' . PHP_EOL;

            if ($this->class->hasMethod($method->getName()) == false) {
                $this->class->addMethodFromGenerator($method);
            }

            $method->setBody($source);
        }
    }

    /**
     * Add an operation to the service.
     *
     * @param Operation $operation The operation to be added.
     */
    public function addOperation(Operation $operation)
    {
        $this->operations[$operation->getName()] = $operation;
    }
}
