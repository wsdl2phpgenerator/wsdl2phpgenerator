<?php

namespace Wsdl2PhpGenerator\Tests\Functional;

/**
 * Test handling of comments generation
 */
class CommentsGenerationTest extends FunctionalTestCase
{

    protected function getWsdlPath()
    {
        return $this->fixtureDir . '/commentsgeneration/commentsgeneration.wsdl';
    }

    public function testMainClassExists()
    {
        $this->assertGeneratedClassExists('CommentsGeneration');
        return new \ReflectionClass(new \CommentsGeneration());
    }

    public function testEmptyCommentWillNotBeGenerated()
    {
        $this->assertGeneratedClassExists('ToBe');
        $paramClass = new \ReflectionClass(new \ToBe());
        $this->assertFalse($paramClass->getDocComment());
    }

    /**
     * @depends testMainClassExists
     */
    public function testNewLinesRemovedFromBordersOfDescription(\ReflectionClass $mainClass)
    {
        /* @var $method \ReflectionMethod */
        $method = $mainClass->getMethod('ToBe');
        $this->assertContains('/**
     * To die, to sleep
     *
     * @param ToBe $parameters', $this->normalizeEOL($method->getDocComment()));
    }

    /**
     * @depends testMainClassExists
     */
    public function testThereIsNoNewLineAfterDescriptionByDefault(\ReflectionClass $mainClass)
    {
        $this->markTestIncomplete('Enable after generated classes will have docs');
        $this->assertContains('
     * And by opposing end them?
     */', $this->normalizeEOL($mainClass->getDocComment()));
    }

    /**
     * @depends testMainClassExists
     */
    public function testThereIsNoNewLineAboveTagsSection(\ReflectionClass $mainClass)
    {
        $property = $mainClass->getProperty('classmap');
        $this->assertContains('/**
     * @var array', $this->normalizeEOL($property->getDocComment()));
    }

    /**
     * Normalizes the newline.
     *
     * Windows uses CR+LF for the newline.
     * This method converts the newline to UNIX style.
     *
     * @param string $string
     *
     * @return string
     */
    private function normalizeEOL($string)
    {
        return str_replace("\r", '', $string);
    }
}
