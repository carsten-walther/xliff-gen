<?php

namespace CarstenWalther\XliffGen\Parser\SyntaxTree;

/**
 * Class ArrayNode
 *
 * @package CarstenWalther\XliffGen\Parser\SyntaxTree
 */
class ObjectAccessorNode extends AbstractNode
{
    /**
     * Object path which will be called. Is a list like "post.name.email"
     *
     * @var string
     */
    protected $objectPath;

    /**
     * Constructor. Takes an object path as input.
     *
     * The first part of the object path has to be a variable in the
     * TemplateVariableContainer.
     *
     * @param string $objectPath An Object Path, like object1.object2.object3
     */
    public function __construct(string $objectPath)
    {
        $this->objectPath = $objectPath;
    }


    /**
     * Internally used for building up cached templates; do not use directly!
     *
     * @return string
     * @Flow\Internal
     */
    public function getObjectPath() : string
    {
        return $this->objectPath;
    }
}
