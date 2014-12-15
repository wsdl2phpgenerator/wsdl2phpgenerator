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
        $methodNames = $config->get('methodNames');
        if (empty($methodNames)) {
            return new DefaultFilter();
        } else {
            return new ServiceOperationFilter($config);
        }
    }
}
