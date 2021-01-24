<?php

namespace CarstenWalther\XliffGen\Domain\Model;

/**
 * Class Type
 * @package CarstenWalther\XliffGen\Domain\Model
 */
class Type
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $title;

    /**
     * Type constructor.
     */
    public function __construct()
    {

    }

    /**
     * @return array
     */
    public function toArray() : array
    {
        return array(
            'id' => $this->getId(),
            'title' => $this->getTitle()
        );
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
     * @return Type
     */
    public function setId(string $id) : Type
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return Type
     */
    public function setTitle(string $title) : Type
    {
        $this->title = $title;
        return $this;
    }
}
