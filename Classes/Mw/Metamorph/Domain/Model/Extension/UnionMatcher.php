<?php
namespace Mw\Metamorph\Domain\Model\Extension;


class UnionMatcher implements ExtensionMatcher
{



    /** @var \Mw\Metamorph\Domain\Model\Extension\ExtensionMatcher[] */
    private $matchers;



    /**
     * @param \Mw\Metamorph\Domain\Model\Extension\ExtensionMatcher[] $matchers
     */
    public function __construct(array $matchers)
    {
        $this->matchers = $matchers;
    }



    public function match($extensionKey)
    {
        foreach ($this->matchers as $matcher)
        {
            if ($matcher->match($extensionKey))
            {
                return TRUE;
            }
        }
        return FALSE;
    }
}