<?php

namespace Wsdl2PhpGenerator\Tests\Functional;


class MethodNameFilterTest extends FunctionalTestCase
{
    public function testFilterByMethodName()
    {
        $this->assertGeneratedFileExists('AbstractServiceService.php');
        $this->assertGeneratedFileExists('Author.php');
        $this->assertGeneratedFileExists('Book.php');
        $this->assertFileNotGenerated('BaseClass.php');
        $this->assertFileNotGenerated('DerivedClass1.php');
        $this->assertFileNotGenerated('DerivedClass2.php');
        $this->assertFileNotGenerated('NicknameUserAuthor.php');
        $this->assertFileNotGenerated('NonUserAuthor.php');
        $this->assertFileNotGenerated('UserClass2.php');
        $serviceClass = new \ReflectionClass('AbstractServiceService');
        $methods = array_map(function (\ReflectionMethod $method) {
            return $method->getName();
        }, $serviceClass->getMethods());
        $this->assertContains('aEcho', $methods);
        $this->assertNotContains('echoLiteral', $methods);
        $this->assertNotContains('echoDerived', $methods);
    }

    protected function configureOptions()
    {
        $this->config->set('methodNames', array('echo'));
    }
    /**
     * @return string The path to the WSDL to generate code from.
     */
    protected function getWsdlPath()
    {
        return $this->fixtureDir . '/abstract/abstract.wsdl';
    }
}
