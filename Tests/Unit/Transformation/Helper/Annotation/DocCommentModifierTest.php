<?php
namespace Mw\Metamorph\Tests\Transformation\Helper\Annotation;


use Mw\Metamorph\Transformation\Helper\Annotation\AnnotationRenderer;
use Mw\Metamorph\Transformation\Helper\Annotation\DocCommentModifier;
use TYPO3\Flow\Tests\UnitTestCase;


class DocCommentModifierTest extends UnitTestCase
{



    /**
     * @var DocCommentModifier
     */
    private $helper;



    public function setUp()
    {
        $this->helper = new DocCommentModifier();
    }



    public function testAnnotationsAreAddedToComment()
    {
        $input = <<<'EOT'
/**
 */
EOT;

        $expected = <<<'EOT'
/**
 * @Foo\Bar
 */
EOT;

        $annotation = new AnnotationRenderer('Foo', 'Bar');
        $this->assertEquals($expected, $this->helper->addAnnotationToDocCommentString($input, $annotation));
    }



    public function testAnnotationsAreAddedToCommentInNewParagraph()
    {
        $input = <<<'EOT'
/**
 * Hello world!
 * This is a lenghty comment!
 */
EOT;

        $expected = <<<'EOT'
/**
 * Hello world!
 * This is a lenghty comment!
 *
 * @Foo\Bar
 */
EOT;

        $annotation = new AnnotationRenderer('Foo', 'Bar');
        $this->assertEquals($expected, $this->helper->addAnnotationToDocCommentString($input, $annotation));
    }



    public function testAnnotationsAreAddedInSingleLineComments()
    {
        $input = <<<'EOT'
/** @var string */
EOT;

        $expected = <<<'EOT'
/**
 * @var string
 * @Foo\Bar
 */
EOT;

        $annotation = new AnnotationRenderer('Foo', 'Bar');
        $this->assertEquals($expected, $this->helper->addAnnotationToDocCommentString($input, $annotation));
    }

}