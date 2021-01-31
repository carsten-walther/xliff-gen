<?php

namespace CarstenWalther\XliffGen\Generator;

use CarstenWalther\XliffGen\Translator\Service\Watson;

/**
 * Class Translator
 *
 * @package CarstenWalther\XliffGen\Generator
 */
class Translator
{
    /**
     * @var string
     */
    protected $source;

    /**
     * @var
     */
    protected $target;

    /**
     * @var string[]
     */
    protected $text;

    /**
     * @var array
     */
    protected $availableLanguages;

    /**
     * Translator constructor.
     *
     * @param string[] $text
     * @param string   $source
     * @param string   $target
     */
    public function __construct(array $text, string $source, string $target)
    {
        $this->text = $text;
        $this->source = $source;
        $this->target = $target;
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
     */
    public function translate() : array
    {
        $translatorService = new Watson();
        $translatorService->initialize();
        $this->availableLanguages = $this->sanitizeLanguages($translatorService->languages());
        if (in_array($this->target, $this->availableLanguages, true)) {
            return $this->sanitizeTranslations($translatorService->translate($this->text, '', $this->source, $this->target));
        }
        return [];
    }

    /**
     * @param array $array
     *
     * @return array
     */
    private function sanitizeLanguages(array $array) : array
    {
        $result = [];
        foreach ($array as $item) {
            $result[] = $item->language;
        }
        return $result;
    }

    /**
     * @param array $array
     *
     * @return array
     */
    private function sanitizeTranslations(array $array) : array
    {
        $result = [];
        foreach ($array as $item) {
            $result[] = $item->translation;
        }
        return $result;
    }
}
