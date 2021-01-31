<?php

namespace CarstenWalther\XliffGen\Domain\Repository;

use CarstenWalther\XliffGen\Domain\Model\Type;
use InvalidArgumentException;

/**
 * Class TypeRepository
 *
 * @package CarstenWalther\XliffGen\Domain\Repository
 */
class TypeRepository extends AbstractCsvRepository
{
    /**
     * @var \CarstenWalther\XliffGen\Domain\Model\Type
     */
    protected $type = null;

    /**
     * @var \CarstenWalther\XliffGen\Domain\Model\Type
     */
    protected $prev = null;

    /**
     * @var \CarstenWalther\XliffGen\Domain\Model\Type
     */
    protected $current = null;

    /**
     * @var \CarstenWalther\XliffGen\Domain\Model\Type
     */
    protected $next = null;

    /**
     * @param string $id
     *
     * @return \CarstenWalther\XliffGen\Domain\Model\Type
     */
    public function findById(string $id) : Type
    {
        $types = $this->findAll();

        foreach ($types as $count => $type) {
            if ($type->getId() === $id) {
                $this->type = $type;
                $this->key = $count;

                if ($count === 0) {
                    $this->isFirst = true;
                }

                if (count($types) - 1 === $count) {
                    $this->isLast = true;
                }

                if (!$this->isFirst) {
                    $this->prev = $types[$count - 1];
                }

                if (!$this->isLast) {
                    $this->next = $types[$count + 1];
                }

                return $type;
            }
        }
    }

    /**
     * @return array<\CarstenWalther\XliffGen\Domain\Model\Type>
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
                    $this->type = new Type();
                    $this->type->setId($row[0]);
                    $this->type->setTitle($row[1]);

                    if ($this->valid()) {
                        $all[] = $this->current();
                    }
                }
                $this->type = null;
            }
            return $all;
        } else {
            throw new InvalidArgumentException('$this->csv is no resource.');
        }
    }

    /**
     * @return \CarstenWalther\XliffGen\Domain\Model\Type
     */
    public function current() : object
    {
        return $this->type;
    }
}
