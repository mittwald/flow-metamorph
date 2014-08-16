<?php
namespace Mw\Metamorph\Domain\Model\Extension;


class PatternExtensionMatcher implements ExtensionMatcher
{


    public function __construct($pattern)
    {
        $this->pattern = $pattern;
    }



    public function match($extensionKey)
    {
        return preg_match($this->pattern, $extensionKey);
    }


}