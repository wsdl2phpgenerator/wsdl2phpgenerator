<?php

namespace Wsdl2PhpGenerator\Tests\Unit\Console;

use Symfony\Component\Console\Tester\CommandTester;
use Wsdl2PhpGenerator\Console\GenerateCommand;
use Wsdl2PhpGenerator\ConfigInterface;
use Wsdl2PhpGenerator\Mock\MockGenerator;

/**
 * Unit test for the Generate command.
 *
 * This mainly verifies that input values are mapped to configuration options.
 *
 * @package Wsdl2PhpGenerator\Tests\Unit
 */
class GenerateCommandTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test that input WSDL and output directory is mapped correctly.
     */
    public function testBaseArguments()
    {
        $input = $this->generateInput();
        $config = $this->getConfigFromInput($input);
        $this->assertEquals($input['--input'], $config->getInputFile());
        $this->assertEquals($input['--output'], $config->getOutputDir());
    }

    /**
     * Test that missing required options triggers an error.
     */
    public function testMissingArguments()
    {
        $this->setExpectedException(
            'RuntimeException',
            'Not enough arguments. Please specify input and output options.'
        );
        $config = $this->getConfigFromInput();
    }

    /**
     * Test that feature options are mapped correctly.
     */
    public function testFeatureOption()
    {
        $input = $this->generateInput(array('--waitOneWayCalls' => true));
        $config = $this->getConfigFromInput($input);
        $this->assertEquals(array('SOAP_WAIT_ONE_WAY_CALLS'), $config->getOptionFeatures());
    }

    /**
     * Test that cache settings are mapped correctly.
     */
    public function testCache()
    {
        $input = $this->generateInput(array('--cacheBoth' => true));
        $config = $this->getConfigFromInput($input);
        $this->assertEquals('WSDL_CACHE_BOTH', $config->getWsdlCache());
    }

    /**
     * Test that all command line options are mapped to the correct configurations.
     */
    public function testAllOptions()
    {
        $this->assertConfig(array('--input', '-i'), true, 'inputFile');
        $this->assertConfig(array('--output', '-o'), true, 'outputDir');
        $this->assertConfig(array('--classes', '-c'), 'someClass', 'classNames');
        $this->assertConfig(array('--classExists', '-e'), true, 'classExists');
        $this->assertConfig('--createAccessors', true, 'createAccessors');
        $this->assertConfig('--constructorNull', true, 'constructorParamsDefaultToNull');
        $this->assertConfig('--gzip', true, 'compression');
        $this->assertConfig(array('--namespace', '-n'), 'SomeNamespace', 'namespaceName');
        $this->assertConfig('--noIncludes', true, 'noIncludes');
        $this->assertConfig(array('--noTypeConstructor', '-t'), true, 'noTypeConstructor');
        $this->assertConfig(array('--prefix', '-p'), 'SomePrefix', 'prefix');
        $this->assertConfig('--sharedTypes', true, 'sharedTypes');
        $this->assertConfig('--singleFile', true, 'oneFile');
        $this->assertConfig(array('--suffix', '-q'), 'SomeSuffix', 'suffix');

        $this->assertConfig('--cacheNone', true, function (ConfigInterface $config) {
                return $config->getWsdlCache() == 'WSDL_CACHE_NONE';
        });
        $this->assertConfig('--cacheDisk', true, function (ConfigInterface $config) {
                return $config->getWsdlCache() == 'WSDL_CACHE_DISK';
        });
        $this->assertConfig('--cacheMemory', true, function (ConfigInterface $config) {
                return $config->getWsdlCache() == 'WSDL_CACHE_MEMORY';
        });
        $this->assertConfig('--cacheBoth', true, function (ConfigInterface $config) {
                return $config->getWsdlCache() == 'WSDL_CACHE_BOTH';
        });

        $this->assertConfig('--singleElementArrays', true, function (ConfigInterface $config) {
                return in_array('SOAP_SINGLE_ELEMENT_ARRAYS', $config->getOptionFeatures());
        });
        $this->assertConfig('--waitOneWayCalls', true, function (ConfigInterface $config) {
                return in_array('SOAP_WAIT_ONE_WAY_CALLS', $config->getOptionFeatures());
        });
        $this->assertConfig('--xsiArrayType', true, function (ConfigInterface $config) {
                return in_array('SOAP_USE_XSI_ARRAY_TYPE', $config->getOptionFeatures());
        });
    }


    /**
     * Retrieve a generator configuration built from command input.
     *
     * @param array $input An array of input options in the format array('--option' => 'value', '-alias' => true).
     * @return ConfigInterface The configuration corresponding to the input.
     */
    protected function getConfigFromInput(array $input = array())
    {
        $generator = MockGenerator::instance();
        $command = new GenerateCommand();
        $command->setGenerator($generator);
        $tester = new CommandTester($command);
        $tester->execute($input);
        return $generator->getConfig();
    }

    /**
     * Generates a valid configuration array which contains required input.
     *
     * @param array $input An array of input options in the format array('--option' => 'value', '-alias' => true).
     * @return array An valid input array containing the provided arguments.
     */
    protected function generateInput(array $input = array())
    {
        return array_merge(array(
                '--input' => 'http://www.webservicex.net/CurrencyConvertor.asmx?WSDL',
                '--output' => '/tmp'), $input);
    }

    /**
     * Assert that the provided input is mapped correctly to a configuration value.
     *
     * @param string|array $inputKeys One or more input arguments or options.
     * @param mixed $inputValue The value for the argument/option.
     * @param string|callable $configMapping The name of the configuration option which should match the input or
     *  a function which when passed a configuration should return the value to compare the input to.
     * @param string $message The message to show if the two values does not match.
     */
    protected function assertConfig($inputKeys, $inputValue, $configMapping, $message = null)
    {
        if (!is_array($inputKeys)) {
            $inputKeys = array($inputKeys);
        }

        foreach ($inputKeys as $inputKey) {
            $input = $this->generateInput(array($inputKey => $inputValue));
            $config = $this->getConfigFromInput($input);

            if (!is_callable($configMapping)) {
                $configMapping = function (ConfigInterface $config) use ($configMapping) {
                    $configClass = new \ReflectionClass($config);
                    $getter = $configClass->getMethod('get' . ucfirst($configMapping));
                    return $getter->invoke($config);
                };
            }

            $this->assertEquals($inputValue, $configMapping($config), $message);
        }
    }

}
