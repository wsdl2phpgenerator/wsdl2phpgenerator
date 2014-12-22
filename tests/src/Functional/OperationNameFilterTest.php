<?php
namespace Wsdl2PhpGenerator\Tests\Functional;

/**
 * Function test case for the operationName configuration option.
 */
class OperationNameFilterTest extends FunctionalTestCase
{
    protected $namespace = 'OperationNameFilter';

    /**
     * Test that files are generated as expected.
     */
    public function testFilterByOperationName()
    {
        $this->assertGeneratedFileExists('AbstractServiceService.php');
        $this->assertClassHasMethod('\OperationNameFilter\AbstractServiceService', 'echoLiteral');
        $this->assertClassNotHasMethod('\OperationNameFilter\AbstractServiceService', 'aEcho');
        $this->assertClassNotHasMethod('\OperationNameFilter\AbstractServiceService', 'echoDerived');
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
        $this->config->set('operationNames', array('echoLiteral'));
        $this->config->set('namespaceName', $this->namespace);
    }
    
    protected function getWsdlPath()
    {
        return $this->fixtureDir . '/abstract/abstract.wsdl';
    }
}
