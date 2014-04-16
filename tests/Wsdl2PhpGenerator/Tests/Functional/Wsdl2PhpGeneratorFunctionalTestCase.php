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
     * @return string The path to the WSDL to generate code from.
     */
    protected abstract function getWsdlPath();

    protected function setup()
    {
        $class = new ReflectionClass($this);
        $this->outputDir = 'tests/generated/' . $class->getShortName();
        $this->generator = new Generator();
        $this->config = new Config(array(
            'inputFile' => $this->getWsdlPath(),
            'outputDir' =>$this->outputDir
        ));

        // We do not execute the code generation here to allow individual test cases
        // to update the configuration further before generating.

        // Clear output dir before starting.
        if (is_dir($this->outputDir)) {
            // Remove any generated code.
            $this->deleteDir($this->outputDir);
        }

        // Generate the code.
        $this->generator->generate($this->config);

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
     */
    protected function assertGeneratedClassExists($className)
    {
        $file = $this->outputDir . '/' . $className . '.php';
        $this->assertFileExists($file);
        require_once $file;
        $this->assertClassExists($className);
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
     * @param string $message The message to show if the assertion fails.
     */
    protected function assertMethodHasParameter(\ReflectionMethod $method, ReflectionParameter $parameter)
    {
        $parameterNames = array();
        foreach ($method->getParameters() as $methodParameter) {
            $parameterNames[] = $parameter->getName();
        }
        $message = (empty($message)) ? sprintf('Parameter "%s" not found among parameter for method "%s->%s" ("%s")', $parameter->getName(), $method->getDeclaringClass()->getName(), $method->getName(), implode('", "', $parameterNames)) : $message;
        $this->assertContains($parameter, $method->getParameters(), $message);
    }

}
