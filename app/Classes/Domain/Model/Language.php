<?php

namespace CarstenWalther\XliffGen\Domain\Model;

/**
 * Class Language
 * @package CarstenWalther\XliffGen\Domain\Model
 */
class Language
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
     * Language constructor.
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
     * @return Language
     */
    public function setId(string $id) : Language
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
     * @return Language
     */
    public function setTitle(string $title) : Language
    {
        $this->title = $title;
        return $this;
    }
}
