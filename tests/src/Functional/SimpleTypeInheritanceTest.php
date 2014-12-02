<?php

namespace src\Functional;


use Wsdl2PhpGenerator\Tests\Functional\FunctionalTestCase;

class SimpleTypeInheritanceTest extends FunctionalTestCase {
    public function testInheritance()
    {
        $this->assertEquals(array('_' => 'Communication_Usage_TypeEnumeration', 'Primary' => 'boolean'),
            $this->getPropertiesWithTypes('Communication_Usage_Type_ReferenceType'));
        $this->assertEquals(array('_' => 'string', 'someVar2' => 'string[]'),
            $this->getPropertiesWithTypes('Simple_Type_Inheritance'));
    }

    protected function getWsdlPath()
    {
        return $this->fixtureDir . '/abstract/simple_type_inheritance.wsdl';
    }

    private function getPropertiesWithTypes($className)
    {
        $class = new \ReflectionClass($className);
        $properties = $class->getProperties();
        $result = array();
        /** @var \ReflectionProperty $property */
        foreach ($properties as $property) {
            if (!preg_match('#@var (.*?)\$(.*?)\n#s', $property->getDocComment(), $annotations)) {
                continue;
            }
            $result[trim($annotations[2])] = trim($annotations[1]);
        }
        return $result;
    }
}

