<?php

namespace CarstenWalther\XliffGen\Parser;

use CarstenWalther\XliffGen\Parser\SyntaxTree\AbstractNode;
use CarstenWalther\XliffGen\Parser\SyntaxTree\ViewHelperNode;
use ReflectionClass;

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
     * Get root node of this parsing state.
     *
     * @return \CarstenWalther\XliffGen\Parser\SyntaxTree\AbstractNode The root node
     */
    public function getRootNode()
    {
        return $this->rootNode;
    }

    /**
     * Set root node of this parsing state
     *
     * @param \CarstenWalther\XliffGen\Parser\SyntaxTree\AbstractNode $rootNode
     *
     * @return void
     */
    public function setRootNode(AbstractNode $rootNode) : void
    {
        $this->rootNode = $rootNode;
    }

    /**
     * Push a node to the node stack. The node stack holds all currently open
     * templating tags.
     *
     * @param \CarstenWalther\XliffGen\Parser\SyntaxTree\AbstractNode $node Node to push to node stack
     *
     * @return void
     */
    public function pushNodeToStack(AbstractNode $node) : void
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
     * @throws \ReflectionException
     */
    public function getNodesByViewHelperName(string $type, AbstractNode $node, array &$nodes = []) : array
    {
        if (is_object($node)) {
            $reflectionClass = new ReflectionClass(get_class($node));
            foreach ($reflectionClass->getProperties() as $property) {
                $property->setAccessible(true);
                if (is_array($property->getValue($node))) {
                    foreach ($property->getValue($node) as $key => $object) {
                        if (is_object($object) && get_class($object) === ViewHelperNode::class) {
                            if (is_array($object->getArguments())) {
                                foreach ($object->getArguments() as $argumentsKey => $argumentsValue) {
                                    if ($argumentsValue->hasChildNodes()) {
                                        $this->getNodesByViewHelperName($type, $argumentsValue, $nodes);
                                    }
                                }
                            }
                            if ($object->getViewHelperClassName() === $type) {
                                $nodes[] = $object;
                            }
                            $this->getNodesByViewHelperName($type, $object, $nodes);
                        }
                    }
                }
                $property->setAccessible(false);
            }
        }
        return $nodes;
    }
}
