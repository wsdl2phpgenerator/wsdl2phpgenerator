<?php

require_once 'PHPUnit/Extensions/OutputTestCase.php';

require_once dirname(__FILE__).'/../../cli/Cli.php';

class CliOutputTest extends PHPUnit_Extensions_OutputTestCase
{
  /**
   * @var Cli
   */
  protected $object;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    $this->object = new Cli('Test', 'test', '1.0');
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
  }

  // Validates the output format
  public function testShowUsage()
  {
    $this->expectOutputString('Usage: Test test'.PHP_EOL.'Version: 1.0'.PHP_EOL.PHP_EOL);
    $this->object->showUsage();
  }

  // Validates the standard -h flag
  public function testShowUsage2()
  {
    $this->expectOutputString('Usage: Test test'.PHP_EOL.'-h'."\t"."\t"."\t"."\t".'Help'.PHP_EOL.'Version: 1.0'.PHP_EOL.PHP_EOL);
    $this->object->validate(array('-h'));
  }

  // Validates the custom -h flag
  public function testShowUsage3()
  {
    $this->object->addFlag('-h', 'Show this help', true, false);
    $this->expectOutputString('Usage: Test test'.PHP_EOL.'-h'."\t"."\t"."\t"."\t".'Show this help'.PHP_EOL.'Version: 1.0'.PHP_EOL.PHP_EOL);
    $this->object->validate(array('-h'));
  }

  // Validates the missing parameter text
  public function testValidate()
  {
    $this->object->addFlag('-g', 'Test', true, true);
    $this->expectOutputString('Required parameter missing!'.PHP_EOL.'Usage: Test test'.PHP_EOL.'-g'."\t"."\t"."\t"."\t".'Test'.PHP_EOL.'-h'."\t"."\t"."\t"."\t".'Help'.PHP_EOL.'Version: 1.0'.PHP_EOL.PHP_EOL);
    $this->object->validate(array('-f'));
  }

  // Validates the missing input text
  public function testValidate2()
  {
    $this->object->addFlag('-g', 'Test', false, true);
    $this->expectOutputString('A flag that must have a parameter does not'.PHP_EOL.'Usage: Test test'.PHP_EOL.'-g'."\t"."\t"."\t"."\t".'Test'.PHP_EOL.'-h'."\t"."\t"."\t"."\t".'Help'.PHP_EOL.'Version: 1.0'.PHP_EOL.PHP_EOL);
    $this->object->validate(array('-g'));
  }
}

?>
