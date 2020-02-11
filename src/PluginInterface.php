<?php

namespace Wsdl2PhpGenerator;

/**
 * The plugin interface, which can be used to alter
 * service or types objects before save.
 * It can be also used to generate additional custom
 * classes
 *
 * @package Wsdl2PhpGenerator
 */

use Wsdl2PhpGenerator\ConfigInterface;
use Wsdl2PhpGenerator\Service;

interface PluginInterface
{
    /**
     * Processes service class and the list of corresponding types.
     * Optionally generates additional custom classes.
     *
     * @param  ConfigInterface $config
     * @param  Service $filteredService
     * @param  array $filteredTypes
     * @return void
     */
    public function process(ConfigInterface $config, Service $filteredService, array &$filteredTypes);
}
