<?php
namespace Mw\Metamorph\Transformation\Helper\Annotation;


class OptionParser
{



    private $values = [];



    public function __construct($optionString)
    {
        $assignments = array_map('trim', explode(',', $optionString));
        foreach ($assignments as $assignment)
        {
            list($left, $right) = array_map('trim', explode('=', $assignment));

            if (preg_match('/^\'(.*)\'$/', $right) || preg_match('/^"(.*)"$/', $right))
            {
                $right = trim($right, '\'"');
            }
            else if (strtolower($right) === 'true')
            {
                $right = TRUE;
            }
            else if (strtolower($right) === 'false')
            {
                $right = FALSE;
            }

            $this->values[$left] = $right;
        }
    }



    public function getValues()
    {
        return $this->values;
    }

}