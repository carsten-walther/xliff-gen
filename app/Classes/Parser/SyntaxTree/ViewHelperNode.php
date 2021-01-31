<?php

namespace CarstenWalther\XliffGen\Parser\SyntaxTree;

use SplObjectStorage;

/**
 * Class ViewHelperNode
 *
 * @package CarstenWalther\XliffGen\Parser\SyntaxTree
 */
class ViewHelperNode extends AbstractNode
{
    /**
     * Class name of view helper
     *
     * @var string
     */
    protected $viewHelperClassName;

    /**
     * Arguments of view helper - References to RootNodes.
     *
     * @var array<NodeInterface>
     */
    protected $arguments = [];

    /**
     * A mapping RenderingContext -> ViewHelper to only re-initialize ViewHelpers
     * when a context change occurs.
     *
     * @var \SplObjectStorage
     */
    protected $viewHelpersByContext = null;

    /**
     * Constructor.
     *
     * @param string $viewHelper The view helper
     * @param array  $arguments  <NodeInterface> Arguments of view helper - each value is a RootNode.
     */
    public function __construct(string $viewHelper, array $arguments)
    {
        $this->viewHelpersByContext = new SplObjectStorage();
        $this->arguments = $arguments;
        $this->viewHelperClassName = $viewHelper;
    }

    /**
     * Get class name of view helper
     *
     * @return string Class Name of associated view helper
     */
    public function getViewHelperClassName() : string
    {
        return $this->viewHelperClassName;
    }

    /**
     * INTERNAL - only needed for compiling templates
     *
     * @return array
     */
    public function getArguments() : array
    {
        return $this->arguments;
    }
}
