<?php

namespace CarstenWalther\XliffGen\Utility;

/**
 * Class Extractor
 *
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
    public function extract() : ?\CarstenWalther\XliffGen\Domain\Model\Xlf
    {
        $xlf = null;
        $matches = [];

        $xlf = new \CarstenWalther\XliffGen\Domain\Model\Xlf();

        $xlf->setSourceLanguage($this->configuration['sourceLanguage'] ? : null);
        $xlf->setTargetLanguage($this->configuration['targetLanguage'] ? : null);
        $xlf->setOriginal($this->configuration['original'] ? : null);
        $xlf->setProductName($this->configuration['productName'] ? : null);
        $xlf->setDate(new \DateTime());

        preg_match_all(self::PATTERN, $this->sourceString, $matches, PREG_SET_ORDER, 0);

        if (count($matches) > 0) {

            foreach ($matches as $match) {

                $translationUnit = new \CarstenWalther\XliffGen\Domain\Model\TranslationUnit();

                $translationUnit->setId($match[1]);
                $translationUnit->setResname($match[1]);
                $translationUnit->setSource($match[2]);

                if ($this->configuration['targetLanguage']) {
                    $translationUnit->setTarget($match[2]);
                }

                $translationUnit->setPreserveSpace($this->shouldPreserveSpace($match[2], $match[2], $this->configuration['targetLanguage']));
                $translationUnit->setWrapWithCdata($this->shouldWrappedWithCdata($match[2]));

                $xlf->addTranslationUnit($translationUnit);
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
