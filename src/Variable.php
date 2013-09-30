<?php
/**
 * @package Wsdl2PhpGenerator
 */

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
     *
     * @var string The type
     */
    private $type;

    /**
     *
     * @var string The name
     */
    private $name;

    /**
     *
     * @var boolean Nillable
     */
    private $nillable;

    /**
     *
     * @param string $type
     * @param string $name
     */
    public function __construct($type, $name, $nillable)
    {
        $this->type = $type;
        $this->name = $name;
        $this->nillable = $nillable;
    }

    /**
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     *
     * @return boolean
     */
    public function getNillable()
    {
        return $this->nillable;
    }
}
