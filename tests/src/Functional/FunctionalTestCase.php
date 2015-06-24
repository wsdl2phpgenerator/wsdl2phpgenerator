<?php
namespace Wsdl2PhpGenerator\Tests\Functional;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use Wsdl2PhpGenerator\Config;
use Wsdl2PhpGenerator\Generator;
use Wsdl2PhpGenerator\Tests\Unit\CodeGenerationTestCase;

/**
 * Base class for functional tests for wsdl2phpgenerator.
 */
abstract class FunctionalTestCase extends CodeGenerationTestCase
{

    /**
     * @var string Path to the directory which will contain the generated code.
     */
    protected $outputDir;

    /**
     * @var Generator The generator which will execute the code generation.
     */
    protected $generator;

    /**
     * @var Config The configuration for the code generation.
     */
    protected $config;

    protected $fixtureDir = 'tests/fixtures/wsdl';

    /**
     * Storage of already generated classes from WSDL to avoid double declaring and fatals
     * @var array
     */
    private static $generatedTestCases = array();

    /**
     * @return string The path to the WSDL to generate code from.
     */
    abstract protected function getWsdlPath();

    /**
     * Subclasses can override this function to set options on $this->config
     */
    protected function configureOptions()
    {
    }

    protected function setup()
    {
        $class = new ReflectionClass($this);
        $this->outputDir = 'tests/generated/' . $class->getShortName();
        $this->generator = new Generator();
        $this->config = new Config(array(
            'inputFile' => $this->getWsdlPath(),
            'outputDir' => $this->outputDir
        ));
        $this->configureOptions();

        // We do not execute the code generation here to allow individual test cases
        // to update the configuration further before generating.

        if (!empty(self::$generatedTestCases[$class->getShortName()])) {
            return;
        }

        // Clear output dir before starting.
        if (is_dir($this->outputDir)) {
            // Remove any generated code.
            $this->deleteDir($this->outputDir);
        }

        // Generate the code.
        $this->generator->generate($this->config);

        self::$generatedTestCases[$class->getShortName()] = true;

        // Register the autoloader.
        require_once $this->outputDir . DIRECTORY_SEPARATOR . 'autoload.php';
    }

    /**
     * Recursively delete a directory and all contents.
     *
     * @param string $dir The directory to delete.
     */
    protected function deleteDir($dir)
    {
        // Implementation taken from http://stackoverflow.com/a/3352564.
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $fileInfo) {
            $deleteFunction = ($fileInfo->isDir() ? 'rmdir' : 'unlink');
            $deleteFunction($fileInfo->getRealPath());
        }

        // Finally remove the top directory.
        rmdir($dir);
    }

    /**
     * Assert that a generated file exists.
     *
     * @param string $filename The name of the file.
     * @param string $message The message to show if the assertion fails.
     */
    protected function assertGeneratedFileExists($filename, $message = '')
    {
        $this->assertFileExists($this->outputDir . '/' . $filename, $message);
    }

    /**
     * Assert that a file was not generated.
     *
     * @param string $filename The name of the file to test for.
     * @param string $message The message to show if the assertion fails.
     */
    protected function assertFileNotGenerated($filename, $message = '')
    {
        $this->assertFileNotExists($this->outputDir . '/' . $filename, $message);
    }


    /**
     * Assertion that tests that a class with a specific name has been
     * generated.
     *
     * @param string $className The name of the class to test for.
     * @param string $namespaceName Optional name of the namespace
     */
    protected function assertGeneratedClassExists($className, $namespaceName = null)
    {
        $file = $this->outputDir . DIRECTORY_SEPARATOR . $className . '.php';
        $this->assertFileExists($file);
        require_once $file;
        $this->assertClassExists($className, $namespaceName);
    }
}
