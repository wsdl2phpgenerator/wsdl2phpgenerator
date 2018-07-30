<?php

if (class_exists('\VCR\PHPUnit\TestListener\VCRTestListener')) {
    /**
     * Class CommonVcrListener
     * Phpunit 6
     */
    class CommonVcrListener extends \VCR\PHPUnit\TestListener\VCRTestListener
    {
    }
} elseif (class_exists('\PHPUnit_Util_Log_VCR')) {
    /**
     * Class CommonVcrListener
     * PhpUnit 5
     */
    class CommonVcrListener extends \PHPUnit_Util_Log_VCR
    {
    }
} else {
    throw new RuntimeException('Unsupported PHPUnit VCR version');
}
