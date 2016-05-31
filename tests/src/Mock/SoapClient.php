<?php

namespace Wsdl2PhpGenerator\Tests\Mock;

/**
 * Mock SoapClient implementation to use when testing.
 */
class SoapClient extends \SoapClient
{

    /**
     * The WSDL passed to the SoapClient.
     *
     * Normally parameters passed to an SoapClient instance cannot be retrieved. This makes it possible.
     *
     * @var string
     */
    public $wsdl;


    /**
     * The options passed to the SoapClient.
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
