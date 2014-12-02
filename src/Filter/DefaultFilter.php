<?php
namespace Wsdl2PhpGenerator\Filter;


use Wsdl2PhpGenerator\Service;
use Wsdl2PhpGenerator\Type;

class DefaultFilter implements FilterInterface
{
    /**
     * @param Service $service
     * @return Service
     */
    public function filter($service)
    {
        return $service;
    }
}
