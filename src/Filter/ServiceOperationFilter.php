<?php

namespace Wsdl2PhpGenerator\Filter;

use Wsdl2PhpGenerator\ComplexType;
use Wsdl2PhpGenerator\ConfigInterface;
use Wsdl2PhpGenerator\Enum;
use Wsdl2PhpGenerator\Service;
use Wsdl2PhpGenerator\Type;
use Wsdl2PhpGenerator\Variable;

/**
 * Filter that leaves only selected operations and types used by these
 * operations.
 */
class ServiceOperationFilter implements FilterInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var array
     */
    private $methods;

    /**
     * @param ConfigInterface $config
     */
    public function __construct($config)
    {
        $this->config = $config;
        $this->methods = $config->get('operationNames');
    }

    /**
     * @inheritdoc
     */
    public function filter(Service $service)
    {
        $operations = array();
        $types  = array();
        foreach ($this->methods as $method) {
            $methodTypes = array();
            $operation = $service->getOperation($method);
            if (!$operation) {
                continue;
            }

            // Discover types used in params
            foreach ($operation->getParams() as $param => $hint) {
                $arr = $operation->getPhpDocParams($param, $service->getTypes());
                $type = $service->getType($arr['type']);
                if (!empty($type)) {
                    $methodTypes[] = $type;
                }
            }
            // Discover types used in returns
            $returns = $operation->getReturns();

            $type = $service->getType($returns);
            if ($type !== null) {
                $methodTypes[] = $type;
            }

            foreach ($methodTypes as $type) {
                $methodTypes = array_merge($methodTypes, $this->findUsedTypes($service, $type)) ;
            }
            $operations[] = $operation;
            $types = array_merge($types, $methodTypes);
        }
        // Remove duplicated using standard equality checks. Default string
        // comparison does not work here.
        $types = array_unique($types, SORT_REGULAR);
        $filteredService = new Service($this->config, $service->getIdentifier(), $types, $service->getDescription());
        // Pull created service with operations
        foreach ($operations as $operation) {
            $filteredService->addOperation($operation);
        }
        return $filteredService;
    }

    /**
     * Function to find all needed types.
     *
     * @param Service $service Source service with all types and operations
     * @param Type $type Type to extract types from.
     *
     * @return Type[]
     *   All identified types referred to including the current type.
     */
    private function findUsedTypes($service, Type $type)
    {
        if ($type instanceof Enum) {
            return array($type);
        }
        if (!$type instanceof ComplexType) {
            return array();
        }

        $foundTypes = array($type);

        // Process Base type
        $baseType = $type->getBaseType();
        if ($baseType) {
            $foundTypes = array_merge($foundTypes, $this->findUsedTypes($service, $baseType));
        }

        $members = $type->getMembers();
        foreach ($members as $member) {
            /** @var Variable $member */
            // Remove array mark from type name
            $memberTypeName = str_replace('[]', '', $member->getType());
            $memberType = $service->getType($memberTypeName);
            if (!$memberType) {
                continue;
            }

            $foundTypes = array_merge($foundTypes, $this->findUsedTypes($service, $memberType));
        }

        return $foundTypes;
    }
}
