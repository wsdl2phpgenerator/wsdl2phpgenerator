<?php

/**
 * @package Generator
 */
namespace Wsdl2PhpGenerator;

use \Exception;
use Wsdl2PhpGenerator\PhpSource\PhpDocComment;
use Wsdl2PhpGenerator\PhpSource\PhpDocElementFactory;
use Wsdl2PhpGenerator\PhpSource\PhpFunction;

/**
 * ArrayType
 *
 * @package Wsdl2PhpGenerator
 */
class ArrayType extends ComplexType
{
    /**
     * Field with array
     *
     * @var Variable
     */
    protected $field;

    /**
     * Type of array elements
     *
     * @var string
     */
    protected $arrayOf;

    /**
     * Implements the loading of the class object
     *
     * @throws Exception if the class is already generated(not null)
     */
    protected function generateClass()
    {
        parent::generateClass();

        // If it is child type, fallback to ComplexType. Can check this only when all
        // types are loaded. See Generator->loadTypes();
        if ($this->getBaseTypeClass() === null) {
            $this->implementArrayInterfaces();
        }
    }

    protected function implementArrayAccess()
    {
        $this->class->addImplementation('\\ArrayAccess');
        $description = 'ArrayAccess implementation';

        $name = Validator::validateAttribute($this->field->getName());
        $indentionStr = $this->config->get('indentionStr');

        $offsetExistsDock = new PhpDocComment();
        $offsetExistsDock->setDescription($description);
        $offsetExistsDock->addParam(PhpDocElementFactory::getParam('mixed', 'offset', 'An offset to check for'));
        $offsetExistsDock->setReturn(PhpDocElementFactory::getReturn('boolean', 'true on success or false on failure'));
        $offsetExists = new PhpFunction(
            'public',
            'offsetExists',
            $this->buildParametersString(
                array(
                    'offset' => 'mixed'
                ),
                false,
                false
            ),
            $indentionStr . 'return isset($this->' . $name . '[$offset]);',
            $offsetExistsDock
        );
        $this->class->addFunction($offsetExists);

        $offsetGetDock = new PhpDocComment();
        $offsetGetDock->setDescription($description);
        $offsetGetDock->addParam(PhpDocElementFactory::getParam('mixed', 'offset', 'The offset to retrieve'));
        $offsetGetDock->setReturn(PhpDocElementFactory::getReturn($this->arrayOf, ''));
        $offsetGet = new PhpFunction(
            'public',
            'offsetGet',
            $this->buildParametersString(
                array(
                    'offset' => 'mixed'
                ),
                false,
                false
            ),
            $indentionStr . 'return $this->' . $name . '[$offset];',
            $offsetGetDock
        );
        $this->class->addFunction($offsetGet);

        $offsetSetDock = new PhpDocComment();
        $offsetSetDock->setDescription($description);
        $offsetSetDock->addParam(PhpDocElementFactory::getParam('mixed', 'offset', 'The offset to assign the value to'));
        $offsetSetDock->addParam(PhpDocElementFactory::getParam($this->arrayOf, 'value', 'The value to set'));
        $offsetSetDock->setReturn(PhpDocElementFactory::getReturn('void', ''));
        $offsetSet = new PhpFunction(
            'public',
            'offsetSet',
            $this->buildParametersString(
                array(
                    'offset' => 'mixed',
                    'value' => $this->arrayOf
                ),
                false,
                false
            ),
            $indentionStr . 'if (!isset($offset)) {' . PHP_EOL .
            str_repeat($indentionStr, 2) . '$this->' . $name . '[] = $value;' . PHP_EOL .
            $indentionStr . '} else {' . PHP_EOL .
            str_repeat($indentionStr, 2) . '$this->' . $name . '[$offset] = $value;' . PHP_EOL .
            $indentionStr . '}',
            $offsetSetDock
        );
        $this->class->addFunction($offsetSet);

        $offsetUnsetDock = new PhpDocComment();
        $offsetUnsetDock->setDescription($description);
        $offsetUnsetDock->addParam(PhpDocElementFactory::getParam('mixed', 'offset', 'The offset to unset'));
        $offsetUnsetDock->setReturn(PhpDocElementFactory::getReturn('void', ''));
        $offsetUnset = new PhpFunction(
            'public',
            'offsetUnset',
            $this->buildParametersString(
                array(
                    'offset' => 'mixed',
                ),
                false,
                false
            ),
            $indentionStr . 'unset($this->' . $name . '[$offset]);',
            $offsetUnsetDock
        );
        $this->class->addFunction($offsetUnset);
    }

    protected function implementIterator()
    {
        $this->class->addImplementation('\\Iterator');
        $description = 'Iterator implementation';

        $name = Validator::validateAttribute($this->field->getName());
        $indentionStr = $this->config->get('indentionStr');

        $currentDock = new PhpDocComment();
        $currentDock->setDescription($description);
        $currentDock->setReturn(PhpDocElementFactory::getReturn($this->arrayOf, 'Return the current element'));
        $current = new PhpFunction(
            'public',
            'current',
            $this->buildParametersString(
                array(),
                false,
                false
            ),
            $indentionStr . 'return current($this->' . $name . ');',
            $currentDock
        );
        $this->class->addFunction($current);

        $nextDock = new PhpDocComment();
        $nextDock->setDescription($description . PHP_EOL . 'Move forward to next element');
        $nextDock->setReturn(PhpDocElementFactory::getReturn('void', ''));
        $next = new PhpFunction(
            'public',
            'next',
            $this->buildParametersString(
                array(),
                false,
                false
            ),
            $indentionStr . 'next($this->' . $name . ');',
            $nextDock
        );
        $this->class->addFunction($next);

        $keyDock = new PhpDocComment();
        $keyDock->setDescription($description);
        $keyDock->setReturn(PhpDocElementFactory::getReturn('string|null', 'Return the key of the current element or null'));
        $key = new PhpFunction(
            'public',
            'key',
            $this->buildParametersString(
                array(),
                false,
                false
            ),
            $indentionStr . 'return key($this->' . $name . ');',
            $keyDock
        );
        $this->class->addFunction($key);

        $validDock = new PhpDocComment();
        $validDock->setDescription($description);
        $validDock->setReturn(PhpDocElementFactory::getReturn('boolean', 'Return the validity of the current position'));
        $valid = new PhpFunction(
            'public',
            'valid',
            $this->buildParametersString(
                array(),
                false,
                false
            ),
            $indentionStr . 'return $this->key() !== null;',
            $validDock
        );
        $this->class->addFunction($valid);

        $rewindDock = new PhpDocComment();
        $rewindDock->setDescription($description . PHP_EOL . 'Rewind the Iterator to the first element');
        $rewindDock->setReturn(PhpDocElementFactory::getReturn('void', ''));
        $rewind = new PhpFunction(
            'public',
            'rewind',
            $this->buildParametersString(
                array(),
                false,
                false
            ),
            $indentionStr . 'reset($this->' . $name . ');',
            $rewindDock
        );
        $this->class->addFunction($rewind);
    }

    protected function implementCountable()
    {
        $this->class->addImplementation('\\Countable');
        $description = 'Countable implementation';

        $name = Validator::validateAttribute($this->field->getName());
        $indentionStr = $this->config->get('indentionStr');

        $countDock = new PhpDocComment();
        $countDock->setDescription($description);
        $countDock->setReturn(PhpDocElementFactory::getReturn($this->arrayOf, 'Return count of elements'));
        $count = new PhpFunction(
            'public',
            'count',
            $this->buildParametersString(
                array(),
                false,
                false
            ),
            $indentionStr . 'return count($this->' . $name . ');',
            $countDock
        );
        $this->class->addFunction($count);
    }

    protected function implementArrayInterfaces()
    {
        $members = array_values($this->members);
        $this->field = $members[0];
        $this->arrayOf = Validator::validateType(substr($this->field->getType(), 0, -2));

        $this->implementArrayAccess();
        $this->implementIterator();
        $this->implementCountable();
    }
}
