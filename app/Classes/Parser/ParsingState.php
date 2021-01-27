<?php

namespace CarstenWalther\XliffGen\Parser;

use CarstenWalther\XliffGen\Utility\DebugUtility;

/**
 * Class ParsingState
 *
 * @package CarstenWalther\XliffGen\Parser
 */
class ParsingState
{
    /**
     * Root node reference
     *
     * @var \CarstenWalther\XliffGen\Parser\SyntaxTree\RootNode
     */
    protected $rootNode;

    /**
     * Array of node references currently open.
     *
     * @var array
     */
    protected $nodeStack = [];

    /**
     * Set root node of this parsing state
     *
     * @param \CarstenWalther\XliffGen\Parser\SyntaxTree\AbstractNode $rootNode
     * @return void
     */
    public function setRootNode(\CarstenWalther\XliffGen\Parser\SyntaxTree\AbstractNode $rootNode) : void
    {
        $this->rootNode = $rootNode;
    }

    /**
     * Get root node of this parsing state.
     *
     * @return \CarstenWalther\XliffGen\Parser\SyntaxTree\AbstractNode The root node
     */
    public function getRootNode()
    {
        return $this->rootNode;
    }

    /**
     * Push a node to the node stack. The node stack holds all currently open
     * templating tags.
     *
     * @param \CarstenWalther\XliffGen\Parser\SyntaxTree\AbstractNode $node Node to push to node stack
     * @return void
     */
    public function pushNodeToStack(\CarstenWalther\XliffGen\Parser\SyntaxTree\AbstractNode $node) : void
    {
        $this->nodeStack[] = $node;
    }

    /**
     * Get the top stack element, without removing it.
     *
     * @return \CarstenWalther\XliffGen\Parser\SyntaxTree\AbstractNode the top stack element.
     */
    public function getNodeFromStack() : SyntaxTree\AbstractNode
    {
        return $this->nodeStack[count($this->nodeStack) - 1];
    }

    /**
     * Pop the top stack element (=remove it) and return it back.
     *
     * @return \CarstenWalther\XliffGen\Parser\SyntaxTree\AbstractNode the top stack element, which was removed.
     */
    public function popNodeFromStack() : SyntaxTree\AbstractNode
    {
        return array_pop($this->nodeStack);
    }

    /**
     * Count the size of the node stack
     *
     * @return integer Number of elements on the node stack (i.e. number of currently open Fluid tags)
     */
    public function countNodeStack() : int
    {
        return count($this->nodeStack);
    }

    /**
     * @param string                                                  $type
     * @param \CarstenWalther\XliffGen\Parser\SyntaxTree\AbstractNode $node
     * @param array                                                   $nodes
     *
     * @return mixed
     */
    public function getNodesByViewHelperName(string $type, \CarstenWalther\XliffGen\Parser\SyntaxTree\AbstractNode $node, array &$nodes = []) : array
    {
        $nodeArray = \CarstenWalther\XliffGen\Utility\ArrayUtility::objectToArray($node);

        $result = \CarstenWalther\XliffGen\Utility\ArrayUtility::arraySearchRecursive($type, $nodeArray);


        DebugUtility::var_dump($result);




        return [];

        /*
        if ($node->hasChildNodes()) {
            foreach ($node->getChildNodes() as $childNode) {
                if ($childNode && $childNode instanceof \CarstenWalther\XliffGen\Parser\SyntaxTree\ViewHelperNode) {
                    if ($childNode->getViewHelperClassName() === $type) {
                        $nodes[] = $childNode;
                    } else {

                        Debug::var_dump($childNode);

                        #$this->getNodesByViewHelperName($type, $childNode, $nodes);
                    }
                }
            }
        }

        return $nodes;
        */
    }
}
