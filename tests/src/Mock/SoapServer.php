<?php

namespace Wsdl2PhpGenerator\Tests\Mock;

/**
 * Mock SoapServer implementation to use when testing.
 */
class SoapServer extends \SoapServer
{

    /**
     * The options passed to the SoapServer.
     *
     * Normally options passed to an SoapServer instance cannot be retrieved. This makes it possible.
     *
     * @var array
     */
    public $options;

    public function __construct($wsdl, $options = array())
    {
        $this->options = $options;
    }

}
