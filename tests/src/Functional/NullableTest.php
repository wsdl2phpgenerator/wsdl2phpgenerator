<?php

namespace Wsdl2PhpGenerator\Tests\Functional;

/**
 * Test handling of elements which can be null. These should be optional.
 */
class NullableTest extends FunctionalTestCase
{
    protected $namespace = 'NullableTest';

    protected function getWsdlPath()
    {
        return $this->fixtureDir . '/abstract/abstract.wsdl';
    }

    protected function configureOptions()
    {
        $this->config->set('namespaceName', $this->namespace);
    }

    /**
     * Test whether an element where minOccurs is set to 0 is optional.
     */
    public function testMinOccurs0Nullable()
    {
        // The type DerivedClass1 has an element someVar1 where minOccurs is set to 0.
        $this->assertGeneratedClassExists('DerivedClass1', $this->namespace);
        $class = new \ReflectionClass('\NullableTest\DerivedClass1');

        foreach ($class->getConstructor()->getParameters() as $parameter) {
            if ($parameter->getName() == 'someVar1') {
                // Since we need to test if the default value is in fact null we need to check for an available default
                // value first.
                $this->assertTrue($parameter->isDefaultValueAvailable());
                $this->assertEquals(null, $this->$parameter->getDefaultValue());
            }
        }
    }

}
