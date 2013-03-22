<?php

/**
 * Base class for functional tests for wsdl2phpgenerator.
 */
abstract class Wsdl2PhpGeneratorFunctionalTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @var string Path to the WSDL to use for generating code.
     */
    protected $wsdl;

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

    protected function setup()
    {
        $this->outputDir = __DIR__ . '/' . get_class($this) . 'Code';
        $this->generator = Generator::getInstance();
        $this->config = new Config($this->wsdl, $this->outputDir);

        // We do not execute the code generation here to allow individual test cases
        // to update the configuration further before generating.
    }

    protected function tearDown()
    {
        if (is_dir($this->outputDir)) {
            // Remove any generated code.
            $this->deleteDir($this->outputDir);
        }
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
     * Assert that a class has a method defined.
     *
     * @param string $methodName The name of the constant.
     * @param string $className The name of the class.
     * @param string $message The message to show if the assertion fails.
     */
    protected function assertClassHasMethod($methodName, $className, $message = '')
    {
        $class = new ReflectionClass($className);
        $this->assertTrue($class->hasMethod($methodName), $message);
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

}
