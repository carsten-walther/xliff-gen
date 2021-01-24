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

        #die('<pre>' . print_r($parsedObjects, true) . '</pre>');

        if (count($parsedObjects) > 0) {
            foreach ($parsedObjects as $parsedObject) {


                if ($namespace !== '' && $parsedObject['content']['namespace'] === $namespace) {
                    if ($parsedObject['content']['method'] === $method) {

                        $translationUnit = new \CarstenWalther\XliffGen\Domain\Model\TranslationUnit();

                        #die('<pre>' . print_r($parsedObject['arguments']['default'], true) . '</pre>');

                        $translationUnit->setId($parsedObject['arguments']['key']['content']['text']);
                        $translationUnit->setResname($parsedObject['arguments']['key']['content']['text']);

                        foreach ($parsedObject['arguments']['default'] as $default) {

                            if (is_array($default) && array_key_exists('content', $default)) {
                                $translationUnit->setSource($default['content']['text']);
                                if ($this->configuration['targetLanguage']) {
                                    $translationUnit->setTarget($default['content']['text']);
                                }
                                $translationUnit->setWrapWithCdata($this->shouldWrappedWithCdata($default['content']['text']));
                                $translationUnit->setPreserveSpace($this->shouldPreserveSpace($default['content']['text'], $default['content']['text'], $this->configuration['targetLanguage']));
                            }
                        }

                        $xlf->addTranslationUnit($translationUnit);
                    }
                }
            }
        }

        die('<pre>' . print_r($xlf, true) . '</pre>');

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
