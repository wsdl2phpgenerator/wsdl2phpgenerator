<?php


namespace Wsdl2PhpGenerator\Tests\Unit;


use Wsdl2PhpGenerator\ComplexType;
use Wsdl2PhpGenerator\Config;

/**
 * Unit test for the ComplexType class.
 */
class ComplexTypeTest extends CodeGenerationTestCase
{

    protected $namespace = 'ComplexTypeTest';

    /**
     * Test handling of attributes of the DateTime type.
     */
    public function testDateTime()
    {
        // Add a mostly dummy configuration. We are not going to read or write any files here.
        // The important part is the accessors part.
        $config = new Config(array(
            'inputFile' => null,
            'outputDir' => null,
            'constructorParamsDefaultToNull' => true,
        ));
        $complexType = new ComplexType($config, 'ComplexTypeTestClass');
        $complexType->addMember('dateTime', 'dateTimeAttribute', false);

        $this->generateClass($complexType);

        $this->assertClassExists('ComplexTypeTestClass');

        $this->assertClassHasAttribute('dateTimeAttribute', 'ComplexTypeTestClass');
        $this->assertClassHasMethod('ComplexTypeTestClass', 'getDateTimeAttribute');
        $this->assertClassHasMethod('ComplexTypeTestClass', 'setDateTimeAttribute');

        $object = new \ComplexTypeTestClass(new \DateTime());
        $class = new \ReflectionClass($object);
        $this->assertMethodParameterHasType($class->getConstructor(), 'dateTimeAttribute', 'DateTime');
        $this->assertMethodParameterDocBlockHasType($class->getConstructor(), 'dateTimeAttribute', '\DateTime');

        $this->assertMethodHasReturnType($class->getMethod('getDateTimeAttribute'), '\DateTime');
        $this->assertMethodParameterHasType($class->getMethod('setDateTimeAttribute'), 'dateTimeAttribute', 'DateTime');
        $this->assertMethodParameterDocBlockHasType(
            $class->getMethod('setDateTimeAttribute'),
            'dateTimeAttribute',
            '\DateTime'
        );

        // Using reflection to set up bad datetime value as like SoapClass does it
        $property = 'dateTimeAttribute';
        $badDateTime = 'noDate';
        $this->setObjectProperty($object, $property, $badDateTime);
        $this->assertFalse($object->getDateTimeAttribute());

        // Test passing variable datetime formats available in SOAP, http://www.w3.org/TR/2001/REC-xmlschema-2-20010502/#dateTime
        $now = new \DateTime();
        foreach (array('Y-m-d\TH:i:s', 'Y-m-d\TH:i:sP', 'Y-m-d\TH:i:s.u', 'Y-m-d\TH:i:s.uP', 'Y-m-d\TH:i:s\Z', 'Y-m-d\TH:i:s.u\Z') as $format) {
            $this->setObjectProperty($object, $property, $now->format($format));
            $this->assertInstanceOf('\DateTime', $object->getDateTimeAttribute());
        }
    }

    /**
     * Test handling of attributes of the DateTime type for constructorParamsDefaultToNull
     */
    public function testDateTimeNullConstructorParams()
    {
        // Add constructorParamsDefaultToNull to default configuration
        $config = new Config(array(
            'inputFile' => null,
            'outputDir' => null,
            'constructorParamsDefaultToNull' => true,
        ));
        $complexType = new ComplexType($config, 'ComplexTypeDateTimeNullTestClass');
        $complexType->addMember('dateTime', 'dateTimeAttribute', false);

        $this->generateClass($complexType);

        $this->assertClassExists('ComplexTypeDateTimeNullTestClass');

        $object = new \ComplexTypeDateTimeNullTestClass(null);
        $this->assertNull($object->getDateTimeAttribute());
    }

    /**
     * Test handling of name generation with keywords when namespacing is not used.
     */
    public function testKeywordNoNamespaceNameGeneration()
    {
        // Dummy configuration.
        $config = new Config(array(
                'inputFile' => null,
                'outputDir' => null
            ));
        // Iterator is an existing interface.
        $complexType = new ComplexType($config, 'Iterator');
        // Class variables cannot start with a number.
        $complexType->addMember('int', '1var', false);

        $this->generateClass($complexType);
        $this->assertClassExists('IteratorCustom');

        $this->assertClassHasAttribute('a1var', 'IteratorCustom');

        // stdClass is an existing class name.
        $complexType = new ComplexType($config, 'stdClass');
        // Class variables cannot start with a dash.
        $complexType->addMember('int', '-var', false);

        $this->generateClass($complexType);
        $this->assertClassExists('stdClassCustom');

        $this->assertClassHasAttribute('avar', 'stdClassCustom');
    }

    /**
     * Test handling of name generation with keywords when namespacing is used.
     */
    public function testKeywordNamespaceNameGeneration()
    {
        // More dummy configuration. The important part is the namespace.
        $config = new Config(array(
                'inputFile' => null,
                'outputDir' => null,
                'namespaceName' => $this->namespace,
            ));
        // Iterator is an existing interface.
        $complexType = new ComplexType($config, 'Iterator');
        $this->generateClass($complexType, $this->namespace);
        $this->assertClassExists('Iterator', $this->namespace);

        // stdClass is an existing class name.
        $complexType = new ComplexType($config, 'stdClass');
        $this->generateClass($complexType, $this->namespace);
        $this->assertClassExists('stdClass', $this->namespace);
    }

    /**
     * Test fluent setters.
     */
    public function testFluentSetters()
    {
        $config = new Config(array(
            'inputFile' => null,
            'outputDir' => null,
        ));

        $complexType = new ComplexType($config, 'Fluent');
        $complexType->addMember('string', 'attribute', true);

        $this->generateClass($complexType);

        // When calling a setter the returned value should be the same as the
        // object where the setter was called.
        $object = new \Fluent();
        $returnValue = $object->setAttribute('value');
        $this->assertEquals($object, $returnValue);

        // The setter should also have its own class as its return type.
        $class = new \ReflectionClass($object);
        $this->assertMethodHasReturnType(
            $class->getMethod('setAttribute'),
            $class->getName()
        );
    }

    /**
     * Test classes that extend themselves.
     */
    public function testExtendingOwnClass()
    {
        // It is actually possible to have a type which extends itself. This is caused by the poor understanding of PHP
        // namespaces. Two types with the same name but in different namespaces will have the same identifier.
        $config = new Config(array(
            'inputFile' => null,
            'outputDir' => null,
        ));

        $type = new ComplexType($config, 'ExtendOwn');
        $type->setBaseType($type);

        $this->generateClass($type);

        $object = new \ExtendOwn();
        $class = new \ReflectionClass($object);
        $this->assertEmpty($class->getParentClass());
    }

    /**
     * Test setters for nullable typed members.
     */
    public function testNullableTypedMembers()
    {
        $config = new Config(array(
            'inputFile' => null,
            'outputDir' => null,
        ));

        $type = new ComplexType($config, 'NullableDateTime');
        // Add a member which has a type (datetime) and is nullable.
        $type->addMember('datetime', 'aDateTime', true);

        $this->generateClass($type);

        $object = new \NullableDateTime();
        // If the member is nullable then we should also be able to pass null to the setter without causing an error.
        $object->setADateTime(null);
        // Obviously the returned member value should be null as well.
        $this->assertNull($object->getADateTime());
    }


    /**
     * Sets object property value using reflection.
     *
     * @param mixed $object The object to set the value on.
     * @param string $propertyName The name of the property to set.
     * @param mixed $value The value to set.
     */
    private function setObjectProperty($object, $propertyName, $value)
    {
        $class = new \ReflectionClass($object);
        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($object, $value);
        $property->setAccessible(false);
    }
}
