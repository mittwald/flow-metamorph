<?php
namespace Mw\Metamorph\Domain\Model\Extension;


class AllMatcher implements ExtensionMatcher
{



    public function match($extensionKey)
    {
        return TRUE;
    }


}