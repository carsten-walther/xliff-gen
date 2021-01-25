<?php

namespace CarstenWalther\XliffGen\Parser\SyntaxTree;

/**
 * Class AbstractNode
 *
 * @package CarstenWalther\XliffGen\Parser\SyntaxTree
 */
class AbstractNode implements \CarstenWalther\XliffGen\Parser\SyntaxTree\NodeInterface
{
    /**
     * List of Child Nodes.
     *
     * @var array<\CarstenWalther\XliffGen\Parser\SyntaxTree\NodeInterface>
     */
    protected $childNodes = [];

    /**
     * Returns all child nodes for a given node.
     *
     * @return array<\CarstenWalther\XliffGen\Parser\SyntaxTree\NodeInterface> A list of nodes
     */
    public function getChildNodes() : array
    {
        return $this->childNodes;
    }

    /**
     * Appends a sub node to this node. Is used inside the parser to append children
     *
     * @param NodeInterface $childNode The sub node to add
     * @return void
     */
    public function addChildNode(NodeInterface $childNode) : void
    {
        $this->childNodes[] = $childNode;
    }
}
