<?php


namespace Wsdl2PhpGenerator\Mock;


use Psr\Log\LoggerInterface;
use Wsdl2PhpGenerator\ConfigInterface;
use Wsdl2PhpGenerator\GeneratorInterface;

/**
 * Mock generator class.
 *
 * Main purpose of this is to enable retrieval of the the passed configuration without actually doing anything.
 *
 * @package Wsdl2PhpGenerator\Mock
 */
class MockGenerator implements GeneratorInterface
{

    static $instance;

    protected $config;

    /**
     * Initializes the single instance if it hasn't been, and returns it if it has.
     *
     * @return GeneratorInterface
     */
    public static function instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new MockGenerator();
        }
        return self::$instance;
    }

    /**
     * Returns the loaded config.
     *
     * @return ConfigInterface The loaded config.
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Generates php source code from a wsdl file.
     *
     * @param ConfigInterface $config The config to use for generation.
     */
    public function generate(ConfigInterface $config)
    {
        $this->config = $config;
    }

    public function setLogger(LoggerInterface $logger)
    {
        // TODO: Implement setLogger() method.
    }
}
