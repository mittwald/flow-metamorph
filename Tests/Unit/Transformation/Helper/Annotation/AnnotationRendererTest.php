<?php
namespace Mw\Metamorph\Tests\Transformation\Helper\Annotation;


use Mw\Metamorph\Transformation\Helper\Annotation\AnnotationRenderer;
use TYPO3\Flow\Tests\UnitTestCase;


class AnnotationRendererTest extends UnitTestCase
{



    public function testAnnotationIsRenderedWithoutParameters()
    {
        $annotation = new AnnotationRenderer('Flow', 'Validate');
        $this->assertEquals('@Flow\\Validate', $annotation->render());
    }



    public function testAnnotationIsRenderedWithScalarStringParameter()
    {
        $annotation = new AnnotationRenderer('Flow', 'Validate');
        $annotation->addParameter('type', 'NotEmpty');
        $this->assertEquals('@Flow\\Validate(type="NotEmpty")', $annotation->render());
    }



    public function testAnnotationIsRenderedWithScalarBooleanParameter()
    {
        $annotation = new AnnotationRenderer('Flow', 'Validate');
        $annotation->addParameter('really', TRUE);
        $this->assertEquals('@Flow\\Validate(really=TRUE)', $annotation->render());
    }



    public function testAnnotationIsRenderedWithScalarIntegerParameter()
    {
        $annotation = new AnnotationRenderer('Flow', 'Validate');
        $annotation->addParameter('count', 5);
        $this->assertEquals('@Flow\\Validate(count=5)', $annotation->render());
    }



    public function testAnnotationIsRenderedWithDictParameter()
    {
        $annotation = new AnnotationRenderer('Flow', 'Validate');
        $annotation->addParameter('options', ['minimum' => 5, 'maximum' => 10]);
        $this->assertEquals('@Flow\\Validate(options={"minimum" = 5, "maximum" = 10})', $annotation->render());
    }



    public function testAnnotationIsRenderedWithListParameter()
    {
        $annotation = new AnnotationRenderer('ORM', 'OneToMany');
        $annotation->addParameter('cascade', ['remove']);
        $this->assertEquals('@ORM\\OneToMany(cascade={"remove"})', $annotation->render());
    }



    public function testArgumentIsPassedUnnamed()
    {
        $annotation = new AnnotationRenderer('Flow', 'Scope');
        $annotation->setArgument('singleton');
        $this->assertEquals('@Flow\\Scope("singleton")', $annotation->render());
    }

}