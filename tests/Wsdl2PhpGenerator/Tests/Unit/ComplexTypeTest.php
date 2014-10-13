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
            'createAccessors' => true
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

}
