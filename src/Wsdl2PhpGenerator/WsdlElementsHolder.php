<?php

namespace Wsdl2PhpGenerator;


class WsdlElementsHolder {
    /**
     * @var Service
     */
    private $service;
    /**
     * An array of Type objects that represents the types in the service
     *
     * @var Type[]
     */
    private $types = array();

    function __construct() {
        $this->service = null;
    }

    public function addType($typeName, $type) {
        $this->types[$typeName] = $type;
    }

    /**
     * @param array|null $methods
     */
    public function filterByMethods($methods) {
        if (!$methods) {
            return $this;
        }
        $holder = new self();
        $holder->setService(clone $this->getService());
        foreach ($methods as $method) {
            $operation = $holder->getService()->getOperation($method);
            $types = array();
            foreach ($operation->getParams() as $param) {
                $holder->addType($param, $this->getType($param));
            }
            $returns = $operation->getReturns();
            $holder->addType($returns, $this->getType($returns));
            $this->combineTypesForMethod($holder, $holder->getTypes());
        }
        return $holder;
    }

    /**
     * @param self $holder
     * @param array $arrayOfTypes
     */
    private function combineTypesForMethod($holder, $arrayOfTypes) {
        /**
         * @var Type $type
         */
        foreach ($arrayOfTypes as $name => $type) {
            $workingType = $type;
            $typeValues = $type;
        }
    }

    /**
     * @return Service
     */
    public function getService() {
        return $this->service;
    }

    /**
     * @param string $type
     *
     * @return null|Type
     */
    public function getType($type) {
        return $this->hasType($type) ? $this->types[$type] : null;
    }
    /**
     * @return Type[]
     */
    public function getTypes() {
        return $this->types;
    }

    /**
     * @param Service $service
     */
    public function setService($service) {
        $this->service = $service;
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public function hasType($type) {
        return isset($this->types[$type]);
    }
}