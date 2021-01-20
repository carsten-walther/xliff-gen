<?php

namespace CarstenWalther\XliffGen\Utility;

/**
 * Class Extractor
 * @package CarstenWalther\XliffGen\Utility
 */
class Extractor
{
    const PATTERN = '/[\<\{]f\:translate[\s(](?>\bkey\b|\bid\b)[=:]\s?["\'\\\\]?[\'"]?([a-zA-Z\.]*)["\'\\\\]["\']?\,?\s?(?>\bdefault\b)?[:=]?\s?["\'\\\\>]?["\']?(.*)(?|\\\\"\)\}|\\\\\'\)\}|\"\)\}|\'\)\}|"\/>|\'\/>|\<\/f\:translate\>)/m';

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
    }

    /**
     * @return null|\CarstenWalther\XliffGen\Domain\Model\Xlf
     */
    public function extract() :? \CarstenWalther\XliffGen\Domain\Model\Xlf
    {
        $xlf = null;
        $matches = [];

        preg_match_all(self::PATTERN, $this->sourceString, $matches, PREG_SET_ORDER, 0);

        if (count($matches) > 0) {

            $xlf = new \CarstenWalther\XliffGen\Domain\Model\Xlf();

            $xlf->setSourceLanguage($this->configuration['sourceLanguage'] ?: null);
            $xlf->setTargetLanguage($this->configuration['targetLanguage'] ?: null);
            $xlf->setOriginal($this->configuration['original'] ?: null);
            $xlf->setProductName($this->configuration['productName'] ?: null);
            $xlf->setDate(new \DateTime());

            foreach ($matches as $match) {

                $translationUnit = new \CarstenWalther\XliffGen\Domain\Model\TranslationUnit();

                $translationUnit->setId($match[1]);
                $translationUnit->setResname($match[1]);
                $translationUnit->setSource($match[2]);

                if ($this->configuration['targetLanguage']) {
                    $translationUnit->setTarget($match[2]);
                }

                $xlf->addTranslationUnit($translationUnit);
            }
        }

        return $xlf;
    }
}
