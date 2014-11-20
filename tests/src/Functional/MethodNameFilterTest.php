<?php
namespace Wsdl2PhpGenerator\Tests\Functional;

class MethodNameFilterTest extends FunctionalTestCase
{
    protected $namespace = 'MethodNameFilter';

    public function testFilterByMethodName()
    {
        $this->assertGeneratedFileExists('AbstractServiceService.php');
        $serviceClass = new \ReflectionClass(new MethodNameFilter\AbstractServiceService());
        $methods = array_map(function (\ReflectionMethod $method) {
            return $method->getName();
        }, $serviceClass->getMethods());
        $this->assertContains('echoLiteral', $methods);
        $this->assertNotContains('aEcho', $methods);
        $this->assertNotContains('echoDerived', $methods);
        $this->assertGeneratedFileExists('Author.php');
        $this->assertFileNotGenerated('BaseClass.php');
        $this->assertFileNotGenerated('Book.php');
        $this->assertFileNotGenerated('DerivedClass1.php');
        $this->assertFileNotGenerated('DerivedClass2.php');
        $this->assertFileNotGenerated('NicknameUserAuthor.php');
        $this->assertFileNotGenerated('UserAuthor.php');
    }

    protected function configureOptions()
    {
        $this->config->set('methodNames', array('echoLiteral'));
        $this->config->set('namespaceName', $this->namespace);
    }
    
    /**
     * @return string The path to the WSDL to generate code from.
     */
    protected function getWsdlPath()
    {
        return $this->fixtureDir . '/abstract/abstract.wsdl';
    }
}
