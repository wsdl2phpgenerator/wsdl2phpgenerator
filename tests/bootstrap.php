<?php

use \Composer\Autoload\ClassLoader;
use \VCR\VCR;

require_once __DIR__ . '/../vendor/autoload.php';

$classloader = new ClassLoader();
$classloader->set('Wsdl2PhpGenerator', array('tests'));
$classloader->register();

VCR::configure()
  ->setCassettePath('tests/fixtures/vcr');
VCR::turnOn();
