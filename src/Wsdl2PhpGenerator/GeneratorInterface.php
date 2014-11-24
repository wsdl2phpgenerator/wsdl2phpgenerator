<?php
namespace Wsdl2PhpGenerator;

// psr/log is intentionally not included with the project to keep dependencies
// to a minimum but the interface is still used to define logging within the
// codebase. A project which uses logging should include it itself.
use Psr\Log\LoggerInterface;

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
     * Generates php source code from a wsdl file
     *
     * @param ConfigInterface $config The config to use for generation
     */
    public function generate(ConfigInterface $config);

    /**
     * Inject a logger into the code generation process.
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger);

}
