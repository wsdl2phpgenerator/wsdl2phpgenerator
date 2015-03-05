<?php
namespace Wsdl2PhpGenerator\Tests\Functional;

class AutoloaderSkipTest extends FunctionalTestCase
{
    protected function getWsdlPath()
    {
        return $this->fixtureDir . '/abstract/abstract.wsdl';
    }

    /**
     * Set the autoloader to not generate the autoloader.
     */
    protected function configureOptions()
    {
        $this->config->set('generateAutoloader', false);
    }

    /**
     * Assert the autoloader was not created.
     */
    public function testRelativeImportPaths()
    {
        $this->assertFileNotGenerated('autoload.php');
    }
}
