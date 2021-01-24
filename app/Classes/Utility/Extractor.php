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

        $parsedObjects = $this->parser->parse($this->sourceString);

        die('<pre>' . print_r($parsedObjects, true) . '</pre>');

        if (count($parsedObjects) > 0) {
            foreach ($parsedObjects as $parsedObject) {
                if ($namespace !== '' && $parsedObject['data']['namespace'] === $namespace && $parsedObject['data']['method'] === $method) {

                    $translationUnit = new \CarstenWalther\XliffGen\Domain\Model\TranslationUnit();

                    $translationUnit->setId($parsedObject['data']['attributes']['key']['data']['text']);
                    $translationUnit->setResname($parsedObject['data']['attributes']['key']['data']['text']);
                    $translationUnit->setSource($parsedObject['data']['attributes']['default']['data']['text']);
                    if ($this->configuration['targetLanguage']) {
                        $translationUnit->setTarget($parsedObject['data']['attributes']['default']['data']['text']);
                    }
                    $translationUnit->setWrapWithCdata($this->shouldWrappedWithCdata($parsedObject['data']['attributes']['default']['data']['text']));
                    $translationUnit->setPreserveSpace($this->shouldPreserveSpace($parsedObject['data']['attributes']['default']['data']['text'], $parsedObject['data']['attributes']['default']['data']['text'], $this->configuration['targetLanguage']));

                    $xlf->addTranslationUnit($translationUnit);
                }
            }
        }
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
