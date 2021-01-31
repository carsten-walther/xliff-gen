<?php

namespace CarstenWalther\XliffGen\Parser\SyntaxTree;

use RuntimeException;

/**
 * Class TextNode
 *
 * @package CarstenWalther\XliffGen\Parser\SyntaxTree
 */
class TextNode extends AbstractNode
{
    /**
     * Contents of the text node
     *
     * @var string
     */
    protected $text;

    /**
     * Constructor.
     *
     * @param string $text text to store in this textNode
     *
     * @throws \Exception
     */
    public function __construct(string $text)
    {
        if (!is_string($text)) {
            throw new RuntimeException('Text node requires an argument of type string, "' . gettype($text) . '" given.');
        }
        $this->text = $text;
    }

    /**
     * Getter for text
     *
     * @return string The text of this node
     */
    public function getText() : string
    {
        return $this->text;
    }
}
