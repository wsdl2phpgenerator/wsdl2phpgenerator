<?php
namespace Wsdl2PhpGenerator\Filter;


use Wsdl2PhpGenerator\Service;

class DefaultFilter implements FilterInterface
{
    /**
     * @param Service $service
     * @return Service
     */
    public function filter(Service $service)
    {
        return $service;
    }
}
