<?php

use \Composer\Autoload\ClassLoader;
use \VCR\VCR;

require_once __DIR__ . '/../vendor/autoload.php';

VCR::configure()
  ->setCassettePath('tests/fixtures/vcr');
VCR::turnOn();
