<?php
namespace Wsdl2PhpGenerator\Tests\Functional;

use PHPUnit_Framework_TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use Wsdl2PhpGenerator\Config;
use Wsdl2PhpGenerator\Generator;

/**
 * Base class for functional tests for wsdl2phpgenerator.
 */
abstract class Wsdl2PhpGeneratorFunctionalTestCase extends PHPUnit_Framework_TestCase
{

    /**
     * @var string Path to the directory which will contain the generated code.
     */
    protected $outputDir;

    /**
     * @var Generator The generator which will execute the code generation.
     */
    protected $generator;

    /**
     * @var Config The configuration for the code generation.
     */
    protected $config;

    protected $fixtureDir = 'tests/fixtures/wsdl';

    /**
     * Storage of already generated classes from WSDL to avoid double delaring and fatals
     * @var array
     */
    private static $generatedTestCases = array();

    /**
     * @return string The path to the WSDL to generate code from.
     */
    abstract protected function getWsdlPath();

    /**
     * Subclasses can override this function to set options on $this->config
     */
    protected function configureOptions()
    {
    }

    protected function setUp()
    {
        $this->generateCode();
    }

    /**
     * Generate code from provided WSDL once to avoid redeclare fatal errors.
     * Individual test cases can update the configuration before generating.
     */
    protected function generateCode()
    {
        $class = new ReflectionClass($this);
        $this->outputDir = 'tests/generated/' . $class->getShortName();
        $this->generator = new Generator();
        $this->config = new Config($this->getWsdlPath(), $this->outputDir);
        $this->configureOptions();

        // We do not execute the code generation here to allow individual test cases
        // to update the configuration further before generating.

        if (!empty(self::$generatedTestCases[$class->getShortName()])) {
            return;
        }

        // Clear output dir before starting.
        if (is_dir($this->outputDir)) {
            // Remove any generated code.
            $this->deleteDir($this->outputDir);
        }

        // Generate the code.
        $this->generator->generate($this->config);

        self::$generatedTestCases[$class->getShortName()] = true;
    }

    /**
     * Recursively delete a directory and all contents.
     *
     * @param string $dir The directory to delete.
     */
    protected function deleteDir($dir)
    {
        // Implementation taken from http://stackoverflow.com/a/3352564.
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $fileInfo) {
            $deleteFunction = ($fileInfo->isDir() ? 'rmdir' : 'unlink');
            $deleteFunction($fileInfo->getRealPath());
        }

        // Finally remove the top directory.
        rmdir($dir);
    }

    /**
     * Assert that a generated file exists.
     *
     * @param $filename The name of the file.
     * @param string $message The message to show if the assertion fails.
     */
    protected function assertGeneratedFileExists($filename, $message = '')
    {
        $this->assertFileExists($this->outputDir . '/' . $filename, $message);
    }

    /**
     * Assert that a class is defined.
     *
     * @param string $className The name of the class.
     * @param string $message The Message to show if the assertion fails.
     */
    protected function assertClassExists($className, $message = '')
    {
        $this->assertTrue(class_exists($className), $message);
    }

    /**
     * Assertion that tests that a class with a specific name has been
     * generated.
     *
     * @param string $className The name of the class to test for.
     * @param string $namespaceName Optional name of the namespace
     */
    protected function assertGeneratedClassExists($className, $namespaceName = null)
    {
        $file = $this->outputDir . '/' . $className . '.php';
        $this->assertFileExists($file);
        require_once $file;
        if ($namespaceName) {
            $namespaceName = '\\' . $namespaceName . '\\';
        }
        $this->assertClassExists($namespaceName . $className);
    }

    /**
     * Assertion that checks that there is type consistency for a value between
     * expectations, actual values and DocBlock.
     *
     * @param $type The expected internal type of the attribute value.
     * @param $attributeName The name of the attribute.
     * @param $object The object.
     * @param string $message The Message to show if the assertion fails.
     */
    protected function assertAttributeTypeConsistency($type, $attributeName, $object, $message = '')
    {
        $this->assertAttributeInternalType($type, $attributeName, $object, $message);
        $this->assertAttributeDocBlockInternalType($type, $attributeName, $object, $message);
        $this->assertAttributeTypeMatchesDocBlock($attributeName, $object, $message);
    }

    /**
     * Assertion that tests that the value of an attribute matches the type
     * declaration in the associated DocBlock if available.
     *
     * @param $attributeName The name of the attribute.
     * @param $object The object.
     * @param string $message The Message to show if the assertion fails.
     */
    protected function assertAttributeTypeMatchesDocBlock($attributeName, $object, $message = '')
    {
        $docBlockType = $this->getAttributeDocBlockType($attributeName, $object);
        if ($docBlockType) {
            if (class_exists($docBlockType)) {
                // If the DocBlock declares that the value should be a class then check¨
                // that the actual attribute value matches.
                if (empty($message)) {
                    $message = sprintf('Attribute %s on %s is of type %s. DocBlock says it should be %s.',
                        $attributeName, get_class($object), get_class($object->{$attributeName}), $docBlockType);
                }
                $this->assertTrue(is_a($object->{$attributeName}, $docBlockType), $message);
            } else {
                // Else we have a primitive type so just check for that.
                $this->assertAttributeInternalType($docBlockType, $attributeName, $object, $message);
            }
        }
    }

    /**
     * Assertion that tests that the type declaration for an attribute is as
     * expected.
     *
     * @param $type The expected type.
     * @param $attributeName The name of the attribute.
     * @param $object The object.
     */
    protected function assertAttributeDocBlockInternalType($type, $attributeName, $object, $message = '')
    {
        $docBlockType = $this->getAttributeDocBlockType($attributeName, $object);
        if ($docBlockType) {
            if ($type === 'bool') {
                $type = 'boolean';
            }
            if (class_exists($docBlockType)) {
                $docBlockType = 'object';
            }
            $this->assertEquals($type, $docBlockType, $message);
        }
    }

    /**
     * Returns the declared type of an attribute in the DocBlock.
     *
     * @param $attributeName The name of the attribute.
     * @param $object The object.
     * @return string|null The declared type of the attribute.
     */
    protected function getAttributeDocBlockType($attributeName, $object)
    {
        $docBlockType = null;

        $attribute = new ReflectionProperty($object, $attributeName);
        $comment = $attribute->getDocComment();
        // Attempt to do some simple extraction of type declaration from the
        // DocBlock.
        if (preg_match('/@var (\w+)/', $comment, $matches)) {
            $value = $attribute->getValue($object);
            $docBlockType = $matches[1];
        }

        return $docBlockType;
    }

    /**
     * Assert that a class has a constant defined.
     *
     * @param string $constName The name of the constant.
     * @param string $className The name of the class.
     * @param string $message The message to show if the assertion fails.
     */
    protected function assertClassHasConst($constName, $className, $message = '')
    {
        $class = new ReflectionClass($className);
        $this->assertTrue($class->hasConstant($constName), $message);
    }

    /**
     * Assert that a class has a property defined.
     *
     * @param ReflectionClass|string $class The class or the name of it.
     * @param ReflectionProperty|string $property The property or the name of it.
     * @param string $message The message to show if the assertion fails.
     */
    protected function assertClassHasProperty($class, $property, $message = '')
    {
        $class = (!$class instanceof ReflectionClass) ? new ReflectionClass($class) : $class;
        $property = ($property instanceof ReflectionProperty) ? $property->getName() : $property;

        $classPropertyNames = array();
        foreach ($class->getProperties() as $classProperty) {
            $classPropertyNames[] = $classProperty->getName();
        }

        $this->assertContains($property, $classPropertyNames, sprintf('Property "%s" not found among properties for class "%s" ("%s")', $property, $class->getName(), implode('", "', $classPropertyNames)));
    }

    /**
     * Assert that a class has a method defined.
     *
     * @param ReflectionClass|string $class The class or the name of it.
     * @param ReflectionMethod|string $method The method or the name of it.
     * @param string $message The message to show if the assertion fails.
     */
    protected function assertClassHasMethod($class, $method, $message = '')
    {
        $class = (!$class instanceof ReflectionClass) ? new ReflectionClass($class) : $class;
        $method = ($method instanceof ReflectionMethod) ? $method->getName() : $method;

        $classMethodNames = array();
        foreach ($class->getMethods() as $classMethod) {
            $classMethodNames[] = $classMethod->getName();
        }
        $message = (empty($message)) ? sprintf('Method "%s" not found among methods for class "%s" ("%s")', $method, $class->getName(), implode('", "', $classMethodNames)) : $message;
        $this->assertContains($method, $classMethodNames, $message);
    }

    /**
     * Assert that a method has a property defined.
     *
     * @param ReflectionMethod $method The method.
     * @param ReflectionParameter $parameter The parameter.
     * @param int $position The expected position (from 0) of the parameter in the list of parameters for the method.
     */
    protected function assertMethodHasParameter(\ReflectionMethod $method, ReflectionParameter $parameter, $position = NULL)
    {
        $parameters = array();
        foreach ($method->getParameters() as $methodParameter) {
            $parameters[$methodParameter->getName()] = $methodParameter;
        }
        $message = sprintf('Parameter "%s" not found among parameters for method "%s->%s" ("%s")', $parameter->getName(), $method->getDeclaringClass()->getName(), $method->getName(), implode('", "', array_keys($parameters)));
        $this->assertContains($parameter->getName(), array_keys($parameters), $message);

        if ($position !== NULL) {
            $parameterNames = array_keys($parameters);
            $message = sprintf('Parameter "%s" not found at position %s for parameters for method "%s->%s" ("%s")', $parameter->getName(), $position, $method->getDeclaringClass()->getName(), $method->getName(), implode('", "', array_keys($parameters)));
            $this->assertEquals($parameter->getName(), $parameterNames[$position], $message);
        }

        // Main attributes for parameters should also be equal.
        $actualParameter = $parameters[$parameter->getName()] ;
        if ($parameter->isDefaultValueAvailable()) {
            $this->assertEquals($actualParameter->getDefaultValue(), $parameter->getDefaultValue(), 'Default values for parameters do not match.');
        }
        $this->assertEquals($actualParameter->getClass(), $parameter->getClass(), 'Type hinted class for parameters should match');
    }

    /**
     * Assert that a class is a subclass of another class.
     *
     * @param ReflectionClass|string $class The subclass of the name of it.
     * @param ReflectionClass|string $baseClass The parent class of the name of it.
     * @param string $message The message to show if the assertion fails.
     */
    protected function assertClassSubclassOf($class, $baseClass, $message = '')
    {
        $class = (!$class instanceof ReflectionClass) ? new ReflectionClass($class) : $class;
        $baseClass = (!$baseClass instanceof ReflectionClass) ? new ReflectionClass($baseClass) : $baseClass;

        $this->assertTrue($class->isSubclassOf($baseClass->getName()), sprintf('Class "%s" is not subclass of "%s"', $class->getName(), $baseClass->getName()));
    }

}
