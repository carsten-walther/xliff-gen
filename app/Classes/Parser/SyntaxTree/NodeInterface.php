<?php

namespace CarstenWalther\XliffGen\Parser\SyntaxTree;

/**
 * Class AbstractNode
 *
 * @package CarstenWalther\XliffGen\Parser\SyntaxTree
 */
interface NodeInterface
{
    /**
     * Returns all child nodes for a given node.
     *
     * @return array<\CarstenWalther\XliffGen\Parser\SyntaxTree\NodeInterface> A list of nodes
     */
    public function getChildNodes() : array;

    /**
     * Appends a sub node to this node. Is used inside the parser to append children
     *
     * @param NodeInterface $childNode The sub node to add
     * @return void
     */
    public function addChildNode(NodeInterface $childNode) : void;
}
