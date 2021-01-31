<?php

namespace CarstenWalther\XliffGen\Domain\Model;

/**
 * Class TranslationUnit
 *
 * @package CarstenWalther\XliffGen\Domain\Model
 */
class TranslationUnit extends AbstractModel
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $resname;

    /**
     * @var string
     */
    protected $source;

    /**
     * @var string
     */
    protected $target;

    /**
     * @var bool
     */
    protected $preserveSpace;

    /**
     * @var bool
     */
    protected $wrapWithCdata;

    /**
     * @var array<\CarstenWalther\XliffGen\Domain\Model\AlternativeTranslation>
     */
    protected $alternativeTranslations;

    /**
     * TranslationUnit constructor.
     */
    public function __construct()
    {
        $this->alternativeTranslations = [];
    }

    /**
     * @return string
     */
    public function getId() : string
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return TranslationUnit
     */
    public function setId(string $id) : TranslationUnit
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getResname() : string
    {
        return $this->resname;
    }

    /**
     * @param string $resname
     *
     * @return TranslationUnit
     */
    public function setResname(string $resname) : TranslationUnit
    {
        $this->resname = $resname;
        return $this;
    }

    /**
     * @return string
     */
    public function getSource() : string
    {
        return $this->source;
    }

    /**
     * @param string $source
     *
     * @return TranslationUnit
     */
    public function setSource(string $source) : TranslationUnit
    {
        $this->source = $source;
        return $this;
    }

    /**
     * @return string
     */
    public function getTarget() : ?string
    {
        return $this->target;
    }

    /**
     * @param string|null $target
     *
     * @return TranslationUnit
     */
    public function setTarget(string $target = null) : ?TranslationUnit
    {
        $this->target = $target;
        return $this;
    }

    /**
     * @return bool
     */
    public function getPreserveSpace() : bool
    {
        return $this->preserveSpace;
    }

    /**
     * @param bool $preserveSpace
     *
     * @return TranslationUnit
     */
    public function setPreserveSpace(bool $preserveSpace) : TranslationUnit
    {
        $this->preserveSpace = $preserveSpace;
        return $this;
    }

    /**
     * @return bool
     */
    public function getWrapWithCdata() : bool
    {
        return $this->wrapWithCdata;
    }

    /**
     * @param bool $wrapWithCdata
     *
     * @return TranslationUnit
     */
    public function setWrapWithCdata(bool $wrapWithCdata) : TranslationUnit
    {
        $this->wrapWithCdata = $wrapWithCdata;
        return $this;
    }

    /**
     * @return array
     */
    public function getAlternativeTranslations() : array
    {
        return $this->alternativeTranslations;
    }

    /**
     * @param array $alternativeTranslations
     *
     * @return TranslationUnit
     */
    public function setAlternativeTranslations(array $alternativeTranslations) : TranslationUnit
    {
        $this->alternativeTranslations = $alternativeTranslations;
        return $this;
    }

    /**
     * @param \CarstenWalther\XliffGen\Domain\Model\AlternativeTranslation $alternativeTranslation
     *
     * @return $this
     */
    public function addAlternative(AlternativeTranslation $alternativeTranslation) : Xlf
    {
        $this->alternativeTranslations[] = $alternativeTranslation;
        return $this;
    }
}
