<?php
/**
 * @package Wsdl2PhpGenerator
 */
namespace Wsdl2PhpGenerator;

/**
 * Very stupid datatype to use instead of array
 *
 * @package Wsdl2PhpGenerator
 * @author Fredrik Wallgren <fredrik.wallgren@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Variable
{
    /**
     * @var string The type
     */
    private $type;

    /**
     * @var string The name
     */
    private $name;

    /**
     * @var boolean nullable
     */
    private $nullable;

    /**
     * @param string $type
     * @param string $name
     * @param bool $nullable
     */
    public function __construct($type, $name, $nullable)
    {
        $this->type = $type;
        $this->name = $name;
        $this->nullable = $nullable;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return boolean
     */
    public function getNullable()
    {
        return $this->nullable;
    }

    /**
     * @return boolean
     */
    public function isArray()
    {
        return substr($this->type, -2, 2) == '[]';
    }
}
