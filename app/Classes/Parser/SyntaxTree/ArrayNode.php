<?php

namespace CarstenWalther\XliffGen\Parser\SyntaxTree;

/**
 * Class ArrayNode
 *
 * @package CarstenWalther\XliffGen\Parser\SyntaxTree
 */
class ArrayNode extends \CarstenWalther\XliffGen\Parser\SyntaxTree\AbstractNode
{
    /**
     * An associative array. Each key is a string. Each value is either a literal, or an AbstractNode.
     *
     * @var array
     */
    protected $internalArray = [];

    /**
     * Constructor.
     *
     * @param array $internalArray Array to store
     */
    public function __construct(array $internalArray)
    {
        $this->internalArray = $internalArray;
    }

    /**
     * INTERNAL; DO NOT CALL DIRECTLY!
     *
     * @return array
     */
    public function getInternalArray() : array
    {
        return $this->internalArray;
    }
}
