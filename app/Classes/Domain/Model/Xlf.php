<?php

namespace CarstenWalther\XliffGen\Domain\Model;

use DateTime;

/**
 * Class Xlf
 *
 * @package CarstenWalther\XliffGen\Domain\Model
 */
class Xlf extends AbstractModel
{
    public const VERSION_1_0 = '1.0';
    public const VERSION_1_2 = '1.2';

    /**
     * @var string
     */
    protected $version;

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
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $authorName;

    /**
     * @var string
     */
    protected $authorEmail;

    /**
     * Xlf constructor.
     */
    public function __construct()
    {
        $this->date = new DateTime();
        $this->translationUnits = [];
    }

    /**
     * @return string
     */
    public function getVersion() : string
    {
        return $this->version ? : self::VERSION_1_0;
    }

    /**
     * @param string $version
     *
     * @return Xlf
     */
    public function setVersion(string $version) : Xlf
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @return string
     */
    public function getSourceLanguage() : ?string
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
        $this->sourceLanguage = $sourceLanguage ? : 'en';
        return $this;
    }

    /**
     * @return string
     */
    public function getTargetLanguage() : ?string
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
    public function getOriginal() : ?string
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
    public function getDate() : DateTime
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     *
     * @return Xlf
     */
    public function setDate(DateTime $date) : Xlf
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return string
     */
    public function getProductName() : ?string
    {
        return $this->productName;
    }

    /**
     * @param string|null $productName
     *
     * @return Xlf
     */
    public function setProductName(string $productName = null) : ?Xlf
    {
        $this->productName = $productName ? : 'your_extention_name';
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
    public function addTranslationUnit(TranslationUnit $translationUnit) : Xlf
    {
        $this->translationUnits[] = $translationUnit;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription() : string
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return Xlf
     */
    public function setDescription(string $description) : Xlf
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getType() : string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return Xlf
     */
    public function setType(string $type) : Xlf
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getAuthorName() : string
    {
        return $this->authorName;
    }

    /**
     * @param string $authorName
     *
     * @return Xlf
     */
    public function setAuthorName(string $authorName) : Xlf
    {
        $this->authorName = $authorName;
        return $this;
    }

    /**
     * @return string
     */
    public function getAuthorEmail() : string
    {
        return $this->authorEmail;
    }

    /**
     * @param string $authorEmail
     *
     * @return Xlf
     */
    public function setAuthorEmail(string $authorEmail) : Xlf
    {
        $this->authorEmail = $authorEmail;
        return $this;
    }
}
