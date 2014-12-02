<?php

namespace Wsdl2PhpGenerator\Tests\Functional;


class MethodNameFilterTest extends FunctionalTestCase
{
    public function testFilterByMethodName()
    {
        $this->assertGeneratedFileExists('Method_Get_Book_Request.php');
        $this->assertGeneratedFileExists('Method_Get_Book_Response.php');
        $this->assertGeneratedFileExists('Book_Author.php');
        $this->assertGeneratedFileExists('Book_Request.php');
        $this->assertGeneratedFileExists('Book_Response.php');
        $this->assertGeneratedFileExists('Book_Response_Title.php');
        $this->assertGeneratedFileExists('BookShell_Service.php');
        $this->assertFileNotGenerated('Author_Response.php');
        $this->assertFileNotGenerated('Method_Get_Authors_Request.php');
        $this->assertFileNotGenerated('Method_Get_Authors_Response.php');
        $serviceClass = new \ReflectionClass('BookShell_Service');
        $methods = array_map(function (\ReflectionMethod $method) {
            return $method->getName();
        }, $serviceClass->getMethods());
        $this->assertContains('Get_Book', $methods);
        $this->assertNotContains('Get_Authors', $methods);
    }

    protected function configureOptions()
    {
        $this->config->set('methodNames', array('Get_Book'));
    }
    /**
     * @return string The path to the WSDL to generate code from.
     */
    protected function getWsdlPath()
    {
        return $this->fixtureDir . '/abstract/book_shell.wsdl';
    }
}
