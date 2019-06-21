<?php
/**
 * @package Wsdl2PhpGenerator
 */
namespace Wsdl2PhpGenerator;

/**
 * Interface for classes where instances be used can generate a SOAP client or server service.
 */
interface ServiceInterface extends ClassGenerator
{
    /**
     * Returns an operation provided by the service based on its name.
     *
     * @param string $operationName The name of the operation.
     *
     * @return Operation|null The operation or null if it does not exist.
     */
    public function getOperation($operationName);

    /**
     * Returns the description of the service.
     *
     * @return string The service description.
     */
    public function getDescription();

    /**
     * Returns the identifier for the service ie. the name.
     *
     * @return string The service name.
     */
    public function getIdentifier();

    /**
     * Returns a type used by the service based on its name.
     *
     * @param string $identifier The identifier for the type.
     *
     * @return Type|null The type or null if the type does not exist.
     */

    public function getType($identifier);

    /**
     * Returns all types defined by the service.
     *
     * @return Type[] An array of types.
     */
    public function getTypes();

    /**
     * Generates the class if not already generated
     */
    public function generateClass();

    /**
     * Add an operation to the service.
     *
     * @param Operation $operation The operation to be added.
     */
    public function addOperation(Operation $operation);
}
