<?php


namespace Wsdl2PhpGenerator\Tests\Unit;


use Wsdl2PhpGenerator\ComplexType;
use Wsdl2PhpGenerator\Config;

/**
 * Unit test for the ComplexType class.
 */
class ComplexTypeTest extends CodeGenerationTestCase
{

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

        // Eval the source for the generated class. This is now pretty but currently the only way we can test whether
        // the generated code is as expected. Our own code generation library does not allow us to retrieve functions
        // from the representing class.
        eval($complexType->getClass()->getSource());
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
}
