<?php

namespace CarstenWalther\XliffGen\Domain\Model;

use CarstenWalther\XliffGen\Utility\ArrayUtility;

/**
 * Class AbstractModel
 *
 * @package CarstenWalther\XliffGen\Domain\Model
 */
abstract class AbstractModel
{
    /**
     * @return array
     * @throws \ReflectionException
     */
    public function toArray() : array
    {
        return ArrayUtility::objectToArray($this);
    }
}
