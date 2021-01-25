<?php

namespace CarstenWalther\XliffGen\Parser\SyntaxTree;

/**
 * Class BooleanNode
 *
 * @package CarstenWalther\XliffGen\Parser\SyntaxTree
 */
class BooleanNode extends \CarstenWalther\XliffGen\Parser\SyntaxTree\AbstractNode
{
    /**
     * If no comparator was found, the syntax tree node should be
     * converted to boolean.
     *
     * @var AbstractNode
     */
    protected $syntaxTreeNode;

    /**
     * Constructor. Parses the syntax tree node and fills $this->leftSide, $this->rightSide,
     * $this->comparator and $this->syntaxTreeNode.
     *
     * @param AbstractNode $syntaxTreeNode
     * @throws \Exception
     */
    public function __construct(AbstractNode $syntaxTreeNode)
    {
        $this->syntaxTreeNode = $syntaxTreeNode;
    }

    /**
     * @return AbstractNode
     */
    public function getSyntaxTreeNode() : AbstractNode
    {
        return $this->syntaxTreeNode;
    }
}
