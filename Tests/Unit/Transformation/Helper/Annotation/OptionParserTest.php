<?php
namespace Mw\Metamorph\Tests\Transformation\Helper\Annotation;


use Mw\Metamorph\Transformation\Helper\Annotation\OptionParser;
use TYPO3\Flow\Tests\UnitTestCase;


class OptionParserTest extends UnitTestCase
{



    /**
     * @dataProvider getInAndOutput
     */
    public function testParsesOptionString($input, $expectedOutput)
    {
        $this->assertEquals($expectedOutput, (new OptionParser($input))->getValues());
    }



    public function getInAndOutput()
    {
        return [
            ['minimum = 3', ['minimum' => 3]],
            ['minimum = 3, maximum = 10', ['minimum' => 3, 'maximum' => 10]],
            ['foo = "bar", baz = true', ['foo' => 'bar', 'baz' => TRUE]]
        ];
    }

}