<?php

namespace CarstenWalther\XliffGen\Domain\Model;

/**
 * Class Xlf
 * @package CarstenWalther\XliffGen\Domain\Model
 */
class Xlf
{
    /**
     * @var string
     */
    protected $sourceLanguage;

    /**
     * @var string
     */
    protected $targetLanguage;

    /**
     * @var string
     */
    protected $original;

    /**
     * @var \DateTime
     */
    protected $date;

    /**
     * @var string
     */
    protected $productName;

    /**
     * @var array<\CarstenWalther\XliffGen\Domain\Model\TranslationUnit>
     */
    protected $translationUnits;

    /**
     * Xlf constructor.
     */
    public function __construct()
    {
        $this->date = new \DateTime();
        $this->translationUnits = [];
    }

    /**
     * @return string
     */
    public function getSourceLanguage() :? string
    {
        return $this->sourceLanguage;
    }

    /**
     * @param string|null $sourceLanguage
     *
     * @return Xlf
     */
    public function setSourceLanguage(string $sourceLanguage = null) : Xlf
    {
        $this->sourceLanguage = $sourceLanguage ?: 'en';
        return $this;
    }

    /**
     * @return string
     */
    public function getTargetLanguage() :? string
    {
        return $this->targetLanguage;
    }

    /**
     * @param string|null $targetLanguage
     *
     * @return Xlf
     */
    public function setTargetLanguage(string $targetLanguage = null) : Xlf
    {
        $this->targetLanguage = $targetLanguage;
        return $this;
    }

    /**
     * @return string
     */
    public function getOriginal() :? string
    {
        return $this->original;
    }

    /**
     * @param string|null $original
     *
     * @return Xlf
     */
    public function setOriginal(string $original = null) : Xlf
    {
        $this->original = $original;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDate() : \DateTime
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     *
     * @return Xlf
     */
    public function setDate(\DateTime $date) : Xlf
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return string
     */
    public function getProductName() :? string
    {
        return $this->productName;
    }

    /**
     * @param string|null $productName
     *
     * @return Xlf
     */
    public function setProductName(string $productName = null) :? Xlf
    {
        $this->productName = $productName ?: 'your_extention_name';
        return $this;
    }

    /**
     * @return array<\CarstenWalther\XliffGen\Domain\Model\TranslationUnit>
     */
    public function getTranslationUnits() : array
    {
        return $this->translationUnits;
    }

    /**
     * @param array<\CarstenWalther\XliffGen\Domain\Model\TranslationUnit> $translationUnits
     *
     * @return Xlf
     */
    public function setTranslationUnits(array $translationUnits) : Xlf
    {
        $this->translationUnits = $translationUnits;
        return $this;
    }

    /**
     * @param \CarstenWalther\XliffGen\Domain\Model\TranslationUnit $translationUnit
     *
     * @return $this
     */
    public function addTranslationUnit(\CarstenWalther\XliffGen\Domain\Model\TranslationUnit $translationUnit) : Xlf
    {
        array_push($this->translationUnits, $translationUnit);
        return $this;
    }
}
