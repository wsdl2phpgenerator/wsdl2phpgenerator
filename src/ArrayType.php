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
            '  return isset($this->' . $this->field->getName() . '[$offset]);',
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
            '  return $this->' . $this->field->getName() . '[$offset];',
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
            '  if (!isset($offset)) {' . PHP_EOL .
            '    $this->' . $this->field->getName() . '[] = $value;' . PHP_EOL .
            '  } else {' . PHP_EOL .
            '    $this->' . $this->field->getName() . '[$offset] = $value;' . PHP_EOL .
            '  }',
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
            '  unset($this->' . $this->field->getName() . '[$offset]);',
            $offsetUnsetDock
        );
        $this->class->addFunction($offsetUnset);
    }

    protected function implementIterator()
    {
        $this->class->addImplementation('\\Iterator');
        $description = 'Iterator implementation';

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
            '  return current($this->' . $this->field->getName() . ');',
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
            '  next($this->' . $this->field->getName() . ');',
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
            '  return key($this->' . $this->field->getName() . ');',
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
            '  return $this->key() !== null;',
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
            '  reset($this->' . $this->field->getName() . ');',
            $rewindDock
        );
        $this->class->addFunction($rewind);
    }

    protected function implementCountable()
    {
        $this->class->addImplementation('\\Countable');
        $description = 'Countable implementation';

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
            '  return count($this->' . $this->field->getName() . ');',
            $countDock
        );
        $this->class->addFunction($count);
    }

    protected function implementArrayInterfaces()
    {
        $members = array_values($this->members);
        $this->field = $members[0];
        $this->arrayOf = substr($this->field->getType(), 0, -2);

        $this->implementArrayAccess();
        $this->implementIterator();
        $this->implementCountable();
    }
}
