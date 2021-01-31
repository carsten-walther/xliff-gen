<?php

namespace CarstenWalther\XliffGen\Generator;

use CarstenWalther\XliffGen\Domain\Model\TranslationUnit;
use CarstenWalther\XliffGen\Domain\Model\Xlf;
use CarstenWalther\XliffGen\Parser\Parser;
use CarstenWalther\XliffGen\Parser\SyntaxTree\TextNode;
use DateTime;
use function strpos;

/**
 * Class Extractor
 *
 * @package CarstenWalther\XliffGen\Generator
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

        $this->parser = new Parser();
    }

    /**
     * @param string $namespace
     * @param string $method
     *
     * @return null|\CarstenWalther\XliffGen\Domain\Model\Xlf
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function extract($namespace = '', $method = '') : ?Xlf
    {
        $xlf = new Xlf();

        $xlf->setVersion($this->configuration['version'] ? : null);
        $xlf->setType($this->configuration['type'] ? : null);
        $xlf->setSourceLanguage($this->configuration['sourceLanguage'] ? : null);
        $xlf->setTargetLanguage($this->configuration['targetLanguage'] ? : null);
        $xlf->setOriginal($this->configuration['original'] ? : null);
        $xlf->setProductName($this->configuration['productName'] ? : null);
        $xlf->setDate(new DateTime());

        /** @var \CarstenWalther\XliffGen\Parser\ParsingState $parsingState */
        $parsingState = $this->parser->parse($this->sourceString);

        $viewHelperName = $this->parser->resolveViewHelperName($namespace, $method);
        $nodes = $parsingState->getNodesByViewHelperName($viewHelperName, $parsingState->getRootNode());

        if (is_array($nodes) && count($nodes) > 0) {
            foreach ($nodes as $node) {
                /** @var \CarstenWalther\XliffGen\Parser\SyntaxTree\NodeInterface $node */
                $nodeArguments = $node->getArguments();

                $key = $resname = $nodeArguments['key']->getText();

                $source = $key;
                $target = '';

                $translationUnit = new TranslationUnit();
                $translationUnit->setId($key);
                $translationUnit->setResname($resname);

                if ($nodeArguments['default'] instanceof TextNode) {
                    $source = $nodeArguments['default']->getText();
                }

                $translationUnit->setSource($source);
                $translationUnit->setTarget($target);
                $translationUnit->setWrapWithCdata($this->shouldWrappedWithCdata($source));
                $translationUnit->setPreserveSpace($this->shouldPreserveSpace($target, $source, $this->configuration['targetLanguage']));

                $xlf->addTranslationUnit($translationUnit);
            }
        }

        if ($this->configuration['targetLanguage'] && $this->configuration['translateTargetLanguages']) {

            $texts = [];
            foreach ($xlf->getTranslationUnits() as $key => $translationUnit) {
                $texts[$key] = $translationUnit->getSource();
            }

            if (mb_strlen(implode('', $texts), '8bit') <= 51200) {
                $translator = new Translator($texts, $this->configuration['sourceLanguage'], $this->configuration['targetLanguage']);
                $translatedTexts = $translator->translate();
            } else {
                $translatedTexts = $texts;
            }

            foreach ($xlf->getTranslationUnits() as $key => $translationUnit) {
                $translationUnit->setTarget($translatedTexts[$key]);
            }
        }

        return $xlf;
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

    /**
     * @param $value
     * @param $enValue
     * @param $targetLanguage
     *
     * @return bool
     */
    protected function shouldPreserveSpace($value, $enValue, $targetLanguage) : bool
    {
        $valueContainsSpacesOrLF = strpos($value, '  ') !== false || strpos($value, "\n") !== false;
        $enValueContainsSpacesOrLF = false;
        if ($targetLanguage !== 'default') {
            $enValueContainsSpacesOrLF = strpos($enValue, '  ') !== false || strpos($enValue, "\n") !== false;
        }
        return $valueContainsSpacesOrLF || $enValueContainsSpacesOrLF;
    }
}
