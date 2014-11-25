<?php

namespace Wsdl2PhpGenerator\Tests\Mock;

/**
 * Mock SoapClient implementation to use when testing.
 */
class SoapClient extends \SoapClient
{

    /**
     * The options passed to the SoapClient.
     *
     * Normally options passed to an SoapClient instance cannot be retrieved. This makes it possible.
     *
     * @var array
     */
    public $options;

    public function __construct($wsdl, $options = array())
    {
        $this->options = $options;
    }

}
