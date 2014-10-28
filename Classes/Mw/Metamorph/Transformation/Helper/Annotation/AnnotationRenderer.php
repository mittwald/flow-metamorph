<?php
namespace Mw\Metamorph\Transformation\Helper\Annotation;


use Helmich\Scalars\Types\String;

class AnnotationRenderer
{



    private $namespace;


    private $annotation;


    private $argument = NULL;


    private $parameters = [];



    public function __construct($namespace, $annotation)
    {
        $this->namespace  = $namespace;
        $this->annotation = $annotation;
    }



    public function addParameter($parameter, $value)
    {
        $this->parameters[$parameter] = $value;
        return $this;
    }



    public function setArgument($argument)
    {
        $this->argument = $argument;
        return $this;
    }



    public function render()
    {
        $parameterString = $this->renderParameterString();
        return '@' . $this->namespace . '\\' . $this->annotation . $parameterString;
    }



    private function renderParameterString()
    {
        $expressions = [];

        if (NULL !== $this->argument)
        {
            $expressions[] = $this->renderParameterValue($this->argument);
        }

        foreach ($this->parameters as $name => $value)
        {
            $expressions[] = $name . '=' . $this->renderParameterValue($value);
        }

        return count($expressions) ? '(' . implode(', ', $expressions) . ')' : '';
    }



    private function renderParameterValue($value)
    {
        if (is_string($value) || $value instanceof String)
        {
            return '"' . str_replace(['"'], ['\\"'], "$value") . '"';
        }
        else if (is_numeric($value))
        {
            return $value;
        }
        else if (is_bool($value))
        {
            return $value ? 'TRUE' : 'FALSE';
        }
        else if (is_array($value))
        {
            $isSequential = array_keys($value) === range(0, count($value) - 1);
            $expressions  = [];

            if ($isSequential)
            {
                foreach ($value as $key => $subvalue)
                {
                    $expressions[] = $this->renderParameterValue($subvalue);
                }
            }
            else
            {
                foreach ($value as $key => $subvalue)
                {
                    $expressions[] = $this->renderParameterValue($key) . ' = ' . $this->renderParameterValue($subvalue);
                }
            }
            return '{' . implode(', ', $expressions) . '}';
        }

        // Fallback: Force-typecast to string.
        return '' . $value;
    }


}