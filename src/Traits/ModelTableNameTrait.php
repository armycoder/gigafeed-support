<?php

namespace Gigafeed\Support\Traits;

/**
 * Trait ModelTableNameTrait
 * @package Gigafeed\Support\Traits
 */
trait ModelTableNameTrait
{
    /**
     * Return the table name defined in the model
     *
     * @return mixed
     */
    public static function getTableName()
    {
        return with(new static)->getTable();
    }

    /**
     * Return the table prefix
     */
    public static function getTablePrefix()
    {
        return with(new static)->getPrefix();
    }

    public function getPrefix()
    {
        return $this->prefix;
    }
}
