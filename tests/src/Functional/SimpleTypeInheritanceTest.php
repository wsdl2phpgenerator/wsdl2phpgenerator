<?php

namespace src\Functional;


use Wsdl2PhpGenerator\Tests\Functional\FunctionalTestCase;

/**
 * Test case to ensure that we support inheritance for simple types.
 */
class SimpleTypeInheritanceTest extends FunctionalTestCase
{

    protected $namespace = 'SimpleTypeInheritance';

    protected function getWsdlPath()
    {
        return $this->fixtureDir . '/simpletypeinheritance/simple_type_inheritance.wsdl';
    }

    protected function configureOptions()
    {
        $this->config->set('namespaceName', $this->namespace);
        // Use constructorParamsDefaultToNull to avoid having to pass arguments
        // when instantiating objects for assertions.
        $this->config->set('constructorParamsDefaultToNull', true);
    }

    public function testStringInheritance()
    {
        $object = new \SimpleTypeInheritance\Simple_Type_Inheritance();
        $this->assertAttributeDocBlockInternalType('string', '_', $object);
        // The actual DocBlock states that the attribute is string[]. This is
        // not a valid PHP type so we use array instead.
        $this->assertAttributeDocBlockInternalType('array', 'someVar2', $object);
    }

    public function testEnumInheritance()
    {
        $object = new \SimpleTypeInheritance\Communication_Usage_Type_ReferenceType();
        $this->assertAttributeDocBlockInternalType('Communication_Usage_TypeEnumeration', '_', $object);
        $this->assertAttributeDocBlockInternalType('boolean', 'Primary', $object);
    }

    public function testPatternInheritance()
    {
        $object = new \SimpleTypeInheritance\ContactMailAdress();
        $this->assertAttributeDocBlockInternalType('MailAdress', '_', $object);
        $this->assertAttributeDocBlockInternalType('string', 'contactPersonName', $object);
    }

}
