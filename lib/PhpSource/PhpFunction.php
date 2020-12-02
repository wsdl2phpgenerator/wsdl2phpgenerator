<?php

/*
 * This file is part of the WSDL2PHPGenerator package.
 * (c) WSDL2PHPGenerator.
 */

namespace Wsdl2PhpGenerator\PhpSource;

/**
 * Class that represents the source code for a function in php.
 *
 * @author Fredrik Wallgren <fredrik.wallgren@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class PhpFunction extends PhpElement
{
    /**
     * @var string String containing the params to the function
     */
    private $params;

    /**
     * @var The code inside {}
     */
    private $source;

    /**
     * @var PhpDocComment A comment in phpdoc format that describes the function
     */
    private $comment;

    /**
     * @param string        $access
     * @param string        $identifier
     * @param string        $params
     * @param string        $source
     * @param PhpDocComment $comment
     */
    public function __construct($access, $identifier, $params, $source, PhpDocComment $comment = null)
    {
        $this->access     = $access;
        $this->identifier = $identifier;
        $this->params     = $params;
        $this->source     = $source;
        $this->comment    = $comment;
    }

    /**
     * @return string Returns the complete source code for the function
     */
    public function getSource()
    {
        $ret = ''.PHP_EOL;

        if ($this->comment !== null) {
            $ret .= $this->getSourceRow($this->comment->getSource());
        }

        $ret .= $this->getSourceRow($this->access.' function '.$this->identifier.'('.$this->params.')');
        $ret .= $this->getSourceRow('{');
        $ret .= $this->getSourceRow($this->source);
        $ret .= $this->getSourceRow('}');

        return $ret;
    }
}
