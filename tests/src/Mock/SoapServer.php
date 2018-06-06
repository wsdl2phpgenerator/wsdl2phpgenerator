<?php

namespace Wsdl2PhpGenerator\Tests\Mock;

/**
 * Mock SoapServer implementation to use when testing.
 */
class SoapServer extends \SoapServer
{

    /**
     * The WSDL passed to the SoapServer.
     *
     * Normally parameters passed to an SoapServer instance cannot be retrieved. This makes it possible.
     *
     * @var string
     */
    public $wsdl;


    /**
     * The options passed to the SoapServer.
     *
     * @var array
     */
    public $options;

    public function __construct($wsdl, $options = array())
    {
        $this->wsdl = $wsdl;
        $this->options = $options;
    }

}
