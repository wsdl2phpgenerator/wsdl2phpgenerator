<?php
namespace Wsdl2PhpGenerator;

/**
 * Common interface for classes that contains functionality for generating classes from a wsdl file.
 *
 * @package Wsdl2PhpGenerator
 * @author Fredrik Wallgren <fredrik.wallgren@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
interface GeneratorInterface
{

    /**
     * Returns the loaded config
     *
     * @return ConfigInterface The loaded config
     */
    public function getConfig();

    /**
     * Generates php source code from a wsdl file
     *
     * @param ConfigInterface $config The config to use for generation
     */
    public function generate(ConfigInterface $config);

}
