<?php
namespace Wsdl2PhpGenerator\Tests\Functional;

class AutoloaderTest extends FunctionalTestCase
{
    protected function getWsdlPath()
    {
        return $this->fixtureDir . '/abstract/abstract.wsdl';
    }

    /**
     * Assert the autoloader was created with default config.
     */
    public function testRelativeImportPaths()
    {
        $this->assertGeneratedFileExists('autoload.php');
    }
}
