<?php

namespace CarstenWalther\XliffGen\Domain\Repository;

use CarstenWalther\XliffGen\Domain\Model\Language;
use InvalidArgumentException;

/**
 * Class LanguageRepository
 *
 * @package CarstenWalther\XliffGen\Domain\Repository
 */
class LanguageRepository extends AbstractCsvRepository
{
    /**
     * @var \CarstenWalther\XliffGen\Domain\Model\Language
     */
    protected $language = null;

    /**
     * @var \CarstenWalther\XliffGen\Domain\Model\Language
     */
    protected $prev = null;

    /**
     * @var \CarstenWalther\XliffGen\Domain\Model\Language
     */
    protected $current = null;

    /**
     * @var \CarstenWalther\XliffGen\Domain\Model\Language
     */
    protected $next = null;

    /**
     * @param string $id
     *
     * @return \CarstenWalther\XliffGen\Domain\Model\Language
     */
    public function findById(string $id) : Language
    {
        $languages = $this->findAll();

        foreach ($languages as $count => $language) {
            if ($language->getId() === $id) {
                $this->language = $language;
                $this->key = $count;

                if ($count === 0) {
                    $this->isFirst = true;
                }

                if (count($languages) - 1 === $count) {
                    $this->isLast = true;
                }

                if (!$this->isFirst) {
                    $this->prev = $languages[$count - 1];
                }

                if (!$this->isLast) {
                    $this->next = $languages[$count + 1];
                }

                return $language;
            }
        }
    }

    /**
     * @return array<\CarstenWalther\XliffGen\Domain\Model\Language>
     */
    public function findAll() : array
    {
        $this->resetRelatives();

        if (is_resource($this->csv)) {
            $all = [];
            $this->rewind();

            while (!feof($this->csv)) {
                $row = fgetcsv($this->csv, 0, $this->delimiter, $this->enclosure, $this->escape);

                if (isset($row[0]) && $row[0]) {
                    $this->language = new Language();
                    $this->language->setId($row[0]);
                    $this->language->setTitle($row[1]);

                    if ($this->valid()) {
                        $all[] = $this->current();
                    }
                }
                $this->language = null;
            }
            return $all;
        } else {
            throw new InvalidArgumentException('$this->csv is no resource.');
        }
    }

    /**
     * @return \CarstenWalther\XliffGen\Domain\Model\Language
     */
    public function current() : object
    {
        return $this->language;
    }
}
