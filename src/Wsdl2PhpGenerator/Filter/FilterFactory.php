<?php
namespace Wsdl2PhpGenerator\Filter;


use Wsdl2PhpGenerator\ConfigInterface;

class FilterFactory
{
    /**
     * @param ConfigInterface $config
     * @return FilterInterface
     */
    public function create(ConfigInterface $config)
    {
        return new DefaultFilter();
    }
} 