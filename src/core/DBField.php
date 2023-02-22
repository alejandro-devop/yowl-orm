<?php

namespace Alejodevop\YowlOrm\Core;

/**
 * Represents a database field
 * @version 1.0.0
 * @since 1.0.0
 */
class DBField {
    const STRING = 'string';
    const NUMBER = 'number';
    const DATE = 'date';

    private string $name;
    private string $type;
    private bool $isNullable = false;
    private mixed $default;
    private bool $isPrimary = false;
    private int $maxLength = 0;
    private mixed $extra;

    /**
     * Sets the field name.
     *
     * @param string $name
     * @return DBField
     */
    public function setName(string $name): DBField {
        $this->name = $name;
        return $this;
    }

    /**
     * Sets the field type
     *
     * @param string $type
     * @return DBField
     */
    public function setType(string $type): DBField {
        $this->type = $type;
        return $this;
    }

    /**
     * Set if the field is nullable
     *
     * @param boolean $isNullable
     * @return DBField
     */
    public function isNullable(bool $isNullable): DBField {
        $this->isNullable = $isNullable;
        return $this;
    }
    /**
     * Set default value for the field
     *
     * @param mixed $default
     * @return DBField
     */
    public function setDefault(mixed $default): DBField {
        $this->default = $default;
        return $this;
    }
    /**
     * Set the length of the field
     *
     * @param mixed $length
     * @return DBField
     */
    public function setLength(mixed $length): DBField {
        $this->maxLength = $length;
        return $this;
    }
    /**
     * Set if the field is primary key
     *
     * @param boolean $isPrimary
     * @return DBField
     */
    public function isPrimary(bool $isPrimary): DBField {
        $this->isPrimary = $isPrimary;
        return $this;
    }
    /**
     * Set extra data for the field
     *
     * @param mixed $extra
     * @return DBField
     */
    public function setExtra(mixed $extra): DBField {
        $this->extra = $extra;
        return $this;
    }
    /**
     * Get the field length
     *
     * @return mixed
     */
    public function getLength() {
        return $this->maxLength;
    }

    public function __debugInfo() {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'size' => $this->maxLength,
            'isNull' => $this->isNullable,
            'isPrimary' => $this->isPrimary,
            'default' => $this->default,
            'extra' => $this->extra
        ];
    }
}