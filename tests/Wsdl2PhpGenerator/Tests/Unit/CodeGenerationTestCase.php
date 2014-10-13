<?php

namespace Wsdl2PhpGenerator\Tests\Unit;

use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;

/**
 * Base class for testing code generation.
 *
 * Contains various assertions for examining code.
 */
class CodeGenerationTestCase extends PHPUnit_Framework_TestCase
{

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
                    $message = sprintf(
                        'Attribute %s on %s is of type %s. DocBlock says it should be %s.',
                        $attributeName,
                        get_class($object),
                        get_class($object->{$attributeName}),
                        $docBlockType
                    );
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
        if (preg_match('/@var (\S+)/', $comment, $matches)) {
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

        $this->assertContains(
            $property,
            $classPropertyNames,
            sprintf(
                'Property "%s" not found among properties for class "%s" ("%s")',
                $property,
                $class->getName(),
                implode('", "', $classPropertyNames)
            )
        );
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
        $message = (empty($message)) ? sprintf(
            'Method "%s" not found among methods for class "%s" ("%s")',
            $method,
            $class->getName(),
            implode('", "', $classMethodNames)
        ) : $message;
        $this->assertContains($method, $classMethodNames, $message);
    }

    /**
     * Assert that a method has a property defined.
     *
     * @param ReflectionMethod $method The method.
     * @param ReflectionParameter|string $parameter The parameter or the name of it
     * @param string $message The message to show if the assertion fails.
     */
    protected function assertMethodHasParameter(\ReflectionMethod $method, $parameter)
    {
        $parameterName = ($parameter instanceof ReflectionParameter) ? $parameter->getName() : $parameter;

        $parameters = array();
        foreach ($method->getParameters() as $methodParameter) {
            $parameters[$methodParameter->getName()] = $methodParameter;
        }

        $message = (empty($message)) ? sprintf(
            'Parameter "%s" not found among parameter for method "%s->%s" ("%s")',
            $parameterName,
            $method->getDeclaringClass()->getName(),
            $method->getName(),
            implode('", "', array_keys($parameters))
        ) : $message;
        $this->assertContains($parameterName, array_keys($parameters), $message);

        // Main attributes for parameters should also be equal.
        if ($parameter instanceof ReflectionParameter) {
            $actualParameter = $parameters[$parameterName];
            $this->assertEquals(
                $actualParameter->getDefaultValue(),
                $parameter->getDefaultValue(),
                'Default values for parameters do not match.'
            );
            $this->assertEquals(
                $actualParameter->getClass(),
                $parameter->getClass(),
                'Type hinted class for parameters should match'
            );
        }
    }

    /**
     * Assert that a named parameter for a method has the expected type defined as a type hint.
     *
     * @param ReflectionMethod $method The method to test.
     * @param string $parameterName The name of the parameter.
     * @param string $type The name of the expected type.
     */
    protected function assertMethodParameterHasType(\ReflectionMethod $method, $parameterName, $type)
    {
        $this->assertMethodHasParameter($method, $parameterName);

        $type = ($type instanceof ReflectionClass) ? $type->getName() : $type;

        $parameter = null;
        foreach ($method->getParameters() as $p) {
            if ($p->getName() == $parameterName) {
                $parameter = $p;
                break;
            }
        }

        $parameterClass = ($parameter->getClass() instanceof ReflectionClass) ? $parameter->getClass()->getName() : '';
        $this->assertEquals(
            $type,
            $parameterClass,
            sprintf(
                'Parameter %s for method %s->%s has type %s. Expected %s.',
                $parameterName,
                $method->getDeclaringClass()->getName(),
                $method->getName(),
                $parameterClass,
                $type
            )
        );
    }

    /**
     * Assert that a named parameter for a method has the expected type defined in the DocBlock.
     *
     * @param ReflectionMethod $method The method to test.
     * @param string $parameterName The name of the parameter.
     * @param string $type The name of the expected type.
     */
    protected function assertMethodParameterDocBlockHasType(\ReflectionMethod $method, $parameterName, $type)
    {
        // Attempt to do some simple extraction of type declaration from the
        // DocBlock.
        $docBlockParameterType = null;
        if (preg_match('/@param (\S+) \$' . $parameterName . '/', $method->getDocComment(), $matches)) {
            $docBlockParameterType = $matches[1];
        }

        $this->assertEquals(
            $type,
            $docBlockParameterType,
            sprintf(
                'DocBlock form method %s->%s states that parameter %s has type %s. Expected %s.',
                $parameterName,
                $method->getDeclaringClass()->getName(),
                $method->getName(),
                $docBlockParameterType,
                $type
            )
        );
    }

    /**
     * Assert that a method has the expected type defined as the return type in the DocBlock.
     *
     * @param ReflectionMethod $method The method to test.
     * @param string $type The expected return type.
     */
    protected function assertMethodHasReturnType(\ReflectionMethod $method, $type)
    {
        // Attempt to do some simple extraction of type declaration from the
        // DocBlock.
        $docBlockReturnType = null;
        if (preg_match('/@return (\S*)/', $method->getDocComment(), $matches)) {
            $docBlockReturnType = $matches[1];
        }

        $this->assertEquals(
            $type,
            $docBlockReturnType,
            sprintf(
                'Method "%s->%s" has return type %s. Expected %s',
                $method->getDeclaringClass()->getName(),
                $method->getName(),
                $docBlockReturnType,
                $type
            )
        );
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

        $this->assertTrue(
            $class->isSubclassOf($baseClass->getName()),
            sprintf('Class "%s" is not subclass of "%s"', $class->getName(), $baseClass->getName())
        );
    }
}