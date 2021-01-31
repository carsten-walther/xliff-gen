<?php

namespace CarstenWalther\XliffGen\Domain\Repository;

use ArrayAccess;
use InvalidArgumentException;
use Iterator;

/**
 * Class AbstractCsvRepository
 *
 * @package CarstenWalther\XliffGen\Domain\Repository
 */
abstract class AbstractCsvRepository implements Iterator, ArrayAccess
{
    /**
     * @var string
     */
    protected $csvFile = '';

    /**
     * @var string
     */
    protected $basePath = '';

    /**
     * @var resource
     */
    protected $csv = null;

    /**
     * @var bool
     */
    protected $readOnly = false;

    /**
     * @var string
     */
    protected $delimiter = '';

    /**
     * @var string
     */
    protected $enclosure = '';

    /**
     * @var string
     */
    protected $escape = '';

    /**
     * @var object
     */
    protected $prev = null;

    /**
     * @var object
     */
    protected $current = null;

    /**
     * @var object
     */
    protected $next = null;

    /**
     * @var int
     */
    protected $key = null;

    /**
     * @var bool
     */
    protected $isFirst = false;

    /**
     * @var bool
     */
    protected $isLast = false;

    /**
     * AbstractRepository constructor.
     *
     * @param        $csvFile
     * @param false  $readOnly
     * @param string $delimiter
     * @param string $enclosure
     * @param string $escape
     */
    public function __construct($csvFile, $readOnly = false, $delimiter = ';', $enclosure = '"', $escape = "\\")
    {
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;
        $this->escape = $escape;
        $this->initCsvFile($csvFile);
        $this->readOnly = $readOnly;
        $this->initCsv();
    }

    /**
     * @param $csvFile
     *
     * @return $this
     */
    public function initCsvFile($csvFile) : AbstractCsvRepository
    {
        if (is_file($csvFile)) {
            if (is_readable($csvFile)) {
                $this->csvFile = $csvFile;
            } else {
                throw new InvalidArgumentException('CSV file must be readable.');
            }
        } else {
            throw new InvalidArgumentException('Path to CSV file must be valid.');
        }
        return $this;
    }

    /**
     * @return $this
     */
    protected function initCsv() : AbstractCsvRepository
    {
        if (!$this->csv) {
            $mode = $this->readOnly ? 'r' : 'r+';
            $this->csv = fopen($this->csvFile, $mode);
        }
        return $this;
    }

    /**
     * @param $basePath
     *
     * @return $this
     */
    public function setBasePath($basePath) : AbstractCsvRepository
    {
        $this->basePath = $basePath;
        return $this;
    }

    /**
     * @return array
     */
    public function findAll() : array
    {

    }

    /**
     * @return bool|float|int|string|null
     */
    function key()
    {
        return $this->key;
    }

    /**
     * @return object|null
     */
    function prev() : ?object
    {
        return $this->prev;
    }

    /**
     * @return object|null
     */
    function next() : ?object
    {
        return $this->next;
    }

    /**
     * @return void
     */
    function rewind()
    {
        rewind($this->csv);
    }

    /**
     * @return bool
     */
    function valid() : bool
    {
        return is_object($this->current());
    }

    /**
     * @return object
     */
    function current() : object
    {

    }

    /**
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset) : bool
    {
        return ($offset > -1 && $offset < count($this->csv));
    }

    /**
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->csv[$offset];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     *
     * @return $this|void
     */
    public function offsetSet($offset, $value) : AbstractCsvRepository
    {
        $this->csv[$offset] = $value;
        return $this;
    }

    /**
     * @param mixed $offset
     *
     * @return $this
     */
    public function offsetUnset($offset) : AbstractCsvRepository
    {
        unset($this->csv[$offset]);
        return $this;
    }

    /**
     * @return void
     */
    protected function resetRelatives()
    {
        $this->prev = null;
        $this->current = null;
        $this->next = null;
        $this->key = null;
        $this->valid = false;
        $this->isFirst = false;
        $this->isLast = false;
    }
}
