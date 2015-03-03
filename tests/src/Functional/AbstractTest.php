<?php

namespace Wsdl2PhpGenerator\Tests\Functional;

/**
 * Test handling of abstract types and extensions.
 */
class AbstractTest extends FunctionalTestCase
{

    protected function getWsdlPath()
    {
        return $this->fixtureDir . '/abstract/abstract.wsdl';
    }

    public function testAbstract()
    {
        // The base service class should be available.
        $this->assertGeneratedClassExists('AbstractServiceService');

        // AbstractServiceService contains an operation called echo. This is a PHP keyword and should thus have been
        // renamed in the generation process to avoid conflicts.
        $serviceClass = new \ReflectionClass('AbstractServiceService');
        $methods = array_map(function (\ReflectionMethod $method) {
            return $method->getName();
        }, $serviceClass->getMethods());
        $this->assertNotContains('echo', $methods, 'Class should not contain a method called echo. It is a reserved keyword');
        $this->assertContains('aEcho', $methods, 'Class should contain a method with a derived name for echo since it is a reserved keyword');

        // Complex types UserAuthor and NonUserAuthor extends the User type. That relationship should be converted to
        // subclasses in the generated code.
        $subClassMessage = 'A class representing a type which extends another type should be a subclass of the corresponding class.';
        $this->assertClassSubclassOf('UserAuthor', 'Author', $subClassMessage);
        $this->assertClassSubclassOf('NonUserAuthor', 'Author', $subClassMessage);
        // Same goes for DerivedClass1, DerivedClass2 and BaseClass.
        $this->assertClassSubclassOf('DerivedClass1', 'BaseClass', $subClassMessage);
        $this->assertClassSubclassOf('DerivedClass2', 'BaseClass', $subClassMessage);

        // The constructor for subclasses should at least have the same parameters as the constructor of the class they
        // extend. They should also appear in the same order.
        $baseClass = new \ReflectionClass('Author');
        $baseConstructor = $baseClass->getConstructor();
        $subClass = new \ReflectionClass('UserAuthor');
        $subClassConstructor = $subClass->getConstructor();
        foreach ($baseConstructor->getParameters() as $parameter) {
            $this->assertMethodHasParameter($subClassConstructor, $parameter, $parameter->getPosition());
        }
        $subSubClass = new \ReflectionClass('NicknameUserAuthor');
        $subSubClassConstructor = $subSubClass->getConstructor();
        foreach ($baseConstructor->getParameters() as $parameter) {
            $this->assertMethodHasParameter($subSubClassConstructor, $parameter, $parameter->getPosition());
        }
        foreach ($subClassConstructor->getParameters() as $parameter) {
            $this->assertMethodHasParameter($subSubClassConstructor, $parameter, $parameter->getPosition());
        }

    }

    public function testAbstractClass()
    {
        $this->assertGeneratedClassExists('Author');
        $abstractClass = new \ReflectionClass('Author');

        $this->assertTrue($abstractClass->isAbstract());
    }
}
