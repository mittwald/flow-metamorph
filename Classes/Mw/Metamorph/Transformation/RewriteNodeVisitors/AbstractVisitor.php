<?php
namespace Mw\Metamorph\Transformation\RewriteNodeVisitors;


use PhpParser\NodeVisitorAbstract;


class AbstractVisitor extends NodeVisitorAbstract
{


    protected $settings;


    protected $classMap;



    public function injectSettings(array $settings)
    {
        $this->settings = $settings;
    }



    public function setClassMap(array $classMap)
    {
        $this->classMap = $classMap;
    }

}