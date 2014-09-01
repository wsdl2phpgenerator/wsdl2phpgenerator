<?php
/**
 * @package wsdl2phpTest
 */
namespace Wsdl2PhpGenerator\Tests\Unit\PhpSource;

use PHPUnit_Framework_TestCase;
use Wsdl2PhpGenerator\PhpSource\PhpDocComment;
use Wsdl2PhpGenerator\PhpSource\PhpDocElement;
use Wsdl2PhpGenerator\PhpSource\PhpDocElementFactory;
use Wsdl2PhpGenerator\ConfigInterface;
use Wsdl2PhpGenerator\Config;

/**
 * Test class for PhpDocComment.
 *
 * @package wsdl2phpTest
 */
class PhpDocCommentTest extends PHPUnit_Framework_TestCase
{
    /**
     *
     * @var ConfigInterface
     */
    private $config;

    /**
     *
     * @var PhpDocComment
     */
    private $comment;

    protected function setUp()
    {
        $this->config  = new Config('inputFile.xml', '/tmp/output');
        $this->comment = new PhpDocComment($this->config);
    }

    public function testCommentsGeneratePublicByDefault()
    {
        $this->comment->setAccess(PhpDocElementFactory::getAccess('public'));

        $this->assertContains(' * @access public', $this->comment->getSource());
    }

    public function testCommentsGeneratedWithoutPublic()
    {
        $this->comment->setAccess(PhpDocElementFactory::getAccess('public'));
        $this->assertContains(' * @access public', $this->comment->getSource());

        $this->comment->setAccess(PhpDocElementFactory::getAccess('private'));
        $this->assertContains(' * @access private', $this->comment->getSource());

        // Now enable option
        $this->config->setCommentsWithoutPublicAccess(true);

        $this->comment->setAccess(PhpDocElementFactory::getAccess('public'));
        $this->assertNotContains(' * @access public', $this->comment->getSource());

        $this->comment->setAccess(PhpDocElementFactory::getAccess('private'));
        $this->assertContains(' * @access private', $this->comment->getSource());
    }

    public function testCommentsGeneratedWithoutGaps()
    {
        // By default gaps are in comments
        $this->comment->setDescription('
            Here we have
    some gaps in comments, that usually
            goes to us from some formatting or word wrapping.

            Or we just dont want to have these new lines.
            Only doubled one leaves.

            Other
            gaps
            are
        gone.

');
        $this->assertContains('
 * Here we have
 * some gaps in comments, that usually
 * goes to us from some formatting or word wrapping.
 *
 * Or we just dont want to have these new lines.
 * Only doubled one leaves.
 *
 * Other
 * gaps
 * are
 * gone.
', $this->comment->getSource());

        // Disabling it...
        $this->config->setCommentsDescriptionWithoutGaps(true);
        $this->assertContains('
 * Here we have some gaps in comments, that usually goes to us from some formatting or word wrapping.
 *
 * Or we just dont want to have these new lines. Only doubled one leaves.
 *
 * Other gaps are gone.
', $this->comment->getSource());
    }
}
