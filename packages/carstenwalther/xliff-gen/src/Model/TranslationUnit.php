<?php

namespace CarstenWalther\XliffGen\Model;

/**
 * Class TranslationUnit
 * @package CarstenWalther\XliffGen\Model
 */
class TranslationUnit
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
     * TranslationUnit constructor.
     */
    public function __construct()
    {

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
    public function getTarget() :? string
    {
        return $this->target;
    }

    /**
     * @param string|null $target
     *
     * @return TranslationUnit
     */
    public function setTarget(string $target = null) :? TranslationUnit
    {
        $this->target = $target;
        return $this;
    }
}
