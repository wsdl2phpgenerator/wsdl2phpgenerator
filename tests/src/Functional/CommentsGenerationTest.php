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
     * @param ToBe $parameters', $method->getDocComment());
    }

    /**
     * @depends testMainClassExists
     */
    public function testThereIsNoNewLineAfterDescriptionByDefault(\ReflectionClass $mainClass)
    {
        $this->markTestIncomplete('Enable after generated classes will have docs');
        $this->assertContains('
     * And by opposing end them?
     */', $mainClass->getDocComment());
    }

    /**
     * @depends testMainClassExists
     */
    public function testThereIsNoNewLineAboveTagsSection(\ReflectionClass $mainClass)
    {
        $property = $mainClass->getProperty('classmap');
        $this->assertContains('/**
     * @var array', $property->getDocComment());
    }
}
