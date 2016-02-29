<?php

namespace Wsdl2PhpGenerator\Tests\Unit;

use Wsdl2PhpGenerator\ArrayType;
use Wsdl2PhpGenerator\Config;

/**
 * Unit test for the ArrayType class.
 */
class ArrayTypeTest extends CodeGenerationTestCase
{
    /**
     * The name of the class that we are generating
     * @var string
     */
    protected $testClassName = 'ArrayTypeTestClass';

    /**
     * The items that the generated class will contain
     * @var array
     */
    private $items;

    /**
     * The generated class
     * @var ArrayType
     */
    private $class;


    protected function setUp()
    {
        // Add a mostly dummy configuration. We are not going to read or write any files here.
        // The important part is the accessors part.
        $config = new Config(array(
            'inputFile' => null,
            'outputDir' => null,
            'constructorParamsDefaultToNull' => true,
        ));
        $arrayType = new ArrayType($config, $this->testClassName);
        $arrayType->addMember('Dummy[]', 'Dummy', false);

        // Generate the class and load it into memory
        $this->generateClass($arrayType);

        $this->items = array(
            'zero'  => 3,
            'one'   => FALSE,
            'two'   => 'good job',
            'three' => new \stdClass(),
            'four'  => array(),
        );
        $this->class = new \ArrayTypeTestClass($this->items);
    }

    /**
     * Test if class exists and is correctly defined.
     */
    public function testClassExists()
    {
        $this->assertClassExists($this->testClassName);
        $this->assertClassImplementsInterface($this->testClassName, 'ArrayAccess');
        $this->assertClassImplementsInterface($this->testClassName, 'Iterator');
        $this->assertClassImplementsInterface($this->testClassName, 'Countable');
    }

    /**
     * Test if class implements the ArrayAccess interface correctly.
     */
    public function testArrayAccessImplementation()
    {
        $this->assertClassHasMethod($this->testClassName, 'offsetExists');
        $this->assertClassHasMethod($this->testClassName, 'offsetGet');
        $this->assertClassHasMethod($this->testClassName, 'offsetSet');
        $this->assertClassHasMethod($this->testClassName, 'offsetUnset');

        foreach ($this->items as $k => $v) {
            // Tests offsetExists()
            $this->assertTrue(isset($this->class[$k]));

            // Tests offsetGet()
            $this->assertSame($this->items[$k], $this->class[$k]);
        }

        // Tests offsetExists()
        $this->assertFalse(isset($this->class['doesntExists']));

        // Tests offsetSet()
        $this->class['newItem'] = 'newValue';
        $this->assertSame('newValue', $this->class['newItem']);
        $this->class[] = 'newValue2';
        $this->assertSame('newValue2', $this->class[0]);
        $this->class[] = 'newValue3';
        $this->assertSame('newValue3', $this->class[1]);

        // Tests offsetUnset()
        unset($this->class['newItem']);
        $this->assertFalse(isset($this->class['newItem']));
    }

    /**
     * Test if class implements the Iterator interface correctly.
     */
    public function testIteratorImplementation()
    {
        $this->assertClassHasMethod($this->testClassName, 'current');
        $this->assertClassHasMethod($this->testClassName, 'key');
        $this->assertClassHasMethod($this->testClassName, 'next');
        $this->assertClassHasMethod($this->testClassName, 'rewind');
        $this->assertClassHasMethod($this->testClassName, 'valid');

        // Test all Iterator methods
        // (both cycles must pass)
        $itemCount = count($this->items);
        for ($n = 0; $n < 2; ++$n) {
            $i = 0;
            reset($this->items);
            foreach ($this->class as $key => $val) {
                if ($i >= $itemCount) {
                    $this->fail("Iterator overflow!");
                }
                $this->assertSame(key($this->items), $key);
                $this->assertSame(current($this->items), $val);
                next($this->items);
                ++$i;
            }
            $this->assertSame($itemCount, $i);
        }
    }

    /**
     * Test if class implements the Countable interface correctly.
     */
    public function testCountableImplementation()
    {
        $this->assertClassHasMethod($this->testClassName, 'count');

        $this->assertCount(count($this->items), $this->class);
    }
}
