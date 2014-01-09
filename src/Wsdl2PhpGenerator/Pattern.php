<?php
/**
 * @package Wsdl2PhpGenerator
 */
namespace Wsdl2PhpGenerator;

use \Exception;

/**
 * Pattern represents a simple type with restriction and a pattern
 *
 * @package Wsdl2PhpGenerator
 * @author Fredrik Wallgren <fredrik.wallgren@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Pattern extends Type
{
    /**
     * @var string The pattern string
     */
    private $value;

    /**
     * Construct the object
     *
     * @param ConfigInterface $config The configuration
     * @param string $name The identifier for the class
     * @param string $restriction The restriction(datatype) of the values
     */
    public function __construct(ConfigInterface $config, $name, $restriction)
    {
        parent::__construct($config, $name, $restriction);
        $this->value = '';
    }

    /**
     * Implements the loading of the class object
     * Always returns null because the pattern is not used as a class
     *
     * @throws Exception if the class is already generated(not null)
     * @return null
     */
    protected function generateClass()
    {
        if ($this->class != null) {
            throw new Exception("The class has already been generated");
        }

        return null;
    }

    /**
     * @return string The string pattern
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value The string pattern
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
}
