<?php

namespace CarstenWalther\XliffGen\Domain\Model;

/**
 * Class AlternativeTranslation
 *
 * @package CarstenWalther\XliffGen\Domain\Model
 */
class AlternativeTranslation extends AbstractModel
{
    /**
     * @var string
     */
    protected $language;

    /**
     * @var string
     */
    protected $string;

    /**
     * AlternativeTranslation constructor.
     */
    public function __construct()
    {

    }

    /**
     * @return string
     */
    public function getLanguage() : string
    {
        return $this->language;
    }

    /**
     * @param string $language
     *
     * @return AlternativeTranslation
     */
    public function setLanguage(string $language) : AlternativeTranslation
    {
        $this->language = $language;
        return $this;
    }

    /**
     * @return string
     */
    public function getString() : string
    {
        return $this->string;
    }

    /**
     * @param string $string
     *
     * @return AlternativeTranslation
     */
    public function setString(string $string) : AlternativeTranslation
    {
        $this->string = $string;
        return $this;
    }
}
