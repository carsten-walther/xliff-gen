<?php

namespace CarstenWalther\XliffGen\Utility;

/**
 * Class Extractor
 *
 * @package CarstenWalther\XliffGen\Utility
 */
class Extractor
{
    /**
     * @var \CarstenWalther\XliffGen\Parser\Parser
     */
    protected $parser;

    /**
     * @var string
     */
    protected $sourceString;

    /**
     * @var array
     */
    protected $configuration;

    /**
     * Extractor constructor.
     *
     * @param string $sourceString
     * @param array  $configuration
     */
    public function __construct(string $sourceString, array $configuration = [])
    {
        $this->sourceString = $sourceString;
        $this->configuration = $configuration;

        $this->parser = new \CarstenWalther\XliffGen\Parser\Parser();
    }

    /**
     * @param string $namespace
     * @param string $method
     *
     * @return null|\CarstenWalther\XliffGen\Domain\Model\Xlf
     * @throws \Exception
     */
    public function extract($namespace = '', $method = '') : ?\CarstenWalther\XliffGen\Domain\Model\Xlf
    {
        $xlf = new \CarstenWalther\XliffGen\Domain\Model\Xlf();

        $xlf->setSourceLanguage($this->configuration['sourceLanguage'] ? : null);
        $xlf->setTargetLanguage($this->configuration['targetLanguage'] ? : null);
        $xlf->setOriginal($this->configuration['original'] ? : null);
        $xlf->setProductName($this->configuration['productName'] ? : null);
        $xlf->setDate(new \DateTime());

        /** @var \CarstenWalther\XliffGen\Parser\ParsingState $parsingState */
        $parsingState = $this->parser->parse($this->sourceString);

        $viewHelperName = $this->parser->resolveViewHelperName($namespace, $method);
        $nodes = $parsingState->getNodesByViewHelperName($viewHelperName, $parsingState->getRootNode());

        #Debug::var_dump($nodes);
        die();

        if ($nodes) {
            foreach ($nodes as $node) {
                /** @var \CarstenWalther\XliffGen\Parser\SyntaxTree\NodeInterface $node */
                $nodeArguments = $node->getArguments();

                $key = $resname = $nodeArguments['key']->getText();

                $source = $key;
                $target = '';

                $translationUnit = new \CarstenWalther\XliffGen\Domain\Model\TranslationUnit();
                $translationUnit->setId($key);
                $translationUnit->setResname($resname);

                if ($nodeArguments['default'] instanceof \CarstenWalther\XliffGen\Parser\SyntaxTree\TextNode) {
                    $source = $nodeArguments['default']->getText();
                    $target = $this->configuration['targetLanguage'] ? $source : '';
                }

                $translationUnit->setSource($source);
                $translationUnit->setTarget($target);
                $translationUnit->setWrapWithCdata($this->shouldWrappedWithCdata($source));
                $translationUnit->setPreserveSpace($this->shouldPreserveSpace($source, $source, $this->configuration['targetLanguage']));

                $xlf->addTranslationUnit($translationUnit);
            }
        }

        die();

        return $xlf;
    }

    /**
     * @param $value
     * @param $enValue
     * @param $targetLanguage
     *
     * @return bool
     */
    protected function shouldPreserveSpace($value, $enValue, $targetLanguage) : bool
    {
        $valueContainsSpacesOrLF = \strpos($value, '  ') !== false || \strpos($value, "\n") !== false;
        $enValueContainsSpacesOrLF = false;
        if ($targetLanguage !== 'default') {
            $enValueContainsSpacesOrLF = \strpos($enValue, '  ') !== false || \strpos($enValue, "\n") !== false;
        }
        return $valueContainsSpacesOrLF || $enValueContainsSpacesOrLF;
    }

    /**
     * @param $string
     *
     * @return bool
     */
    protected function shouldWrappedWithCdata($string) : bool
    {
        $shouldWrappedWithCdata = false;
        if ($string !== strip_tags($string)) {
            $shouldWrappedWithCdata = true;
        }
        return $shouldWrappedWithCdata;
    }
}
