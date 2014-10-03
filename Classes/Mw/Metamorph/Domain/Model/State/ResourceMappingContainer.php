<?php
namespace Mw\Metamorph\Domain\Model\State;


class ResourceMappingContainer
{



    use Reviewable;


    /**
     * @var ResourceMapping[]
     */
    protected $resourceMappings;



    public function hasResourceMapping($sourceFile)
    {
        return NULL !== $this->getResourceMapping($sourceFile);
    }



    public function getResourceMapping($sourceFile)
    {
        foreach ($this->resourceMappings as $resourceMapping)
        {
            if ($sourceFile === $resourceMapping->getSourceFile())
            {
                return $resourceMapping;
            }
        }
        return NULL;
    }



    public function getResourceMappings()
    {
        return $this->resourceMappings;
    }



    public function addResourceMapping(ResourceMapping $resourceMapping)
    {
        if (FALSE === $this->hasResourceMapping($resourceMapping->getSourceFile()))
        {
            $this->reviewed           = FALSE;
            $this->resourceMappings[] = $resourceMapping;
        }
    }
} 