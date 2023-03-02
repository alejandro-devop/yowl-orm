<?php

namespace Alejodevop\YowlOrm\Core;

abstract class DBTableCreator {
    public const STRING_FIELD = 'string';
    public const DATE_FIELD = 'date';
    public const INT_FIELD = 'int';
    public const BOOL_FIELD = 'bool';
    public const TEXT_FIELD = 'text';

    public const BIG_INT = 'big-int';
    # 
    public const DATETIME_FIELD = 'datetime';
    public const TIMESTAMP_FIELD = 'time-stamp';

    protected $tableName;
    protected $columns = [];
    protected $pk;
    protected $fieldInControl;

    public abstract function getTableCreationQuery(): string;
    
    public function tableName(string $tableName): DBTableCreator {
        $this->tableName = $tableName;
        return $this;
    }
    public function addColumn($column, $options = [], $required = false): DBTableCreator {
        [
            'type' => $type, 
            'default' => $default,
            'autoIncrement' => $autoIncrement,
            'size' => $size,
            'uuid' => $uuid,
        ] = array_merge([
            'type' => self::STRING_FIELD,
            'default' => null,
            'autoIncrement' => false,
            'unique' => true,
            'size' => null,
            'uuid' => false,
        ], $options);
                
        $this->columns[$column] = [
            'type' => $type, 
            'default' => $default,
            'autoIncrement' => $autoIncrement,
            'size' => $size,
            'uuid' => $uuid,
            'required' => $required
        ];
        $this->fieldInControl = $column;
        return $this;
    }

    public function stringCol($column, $required = false): DBTableCreator {
        $options = ['type' => self::STRING_FIELD, 'size' => 255];
        return $this->addColumn($column, $options, $required);
    }

    public function textCol($column, $required = false): DBTableCreator {
        $options = ['type' => self::TEXT_FIELD, 'size' => 500];
        return $this->addColumn($column, $options, $required);
    }

    public function dateCol($column, $required = false): DBTableCreator {
        $options = ['type' => self::DATE_FIELD, 'size' => null];
        return $this->addColumn($column, $options, $required);
    }

    public function size($size = 10): DBTableCreator {
        if (isset($this->fieldInControl) && key_exists($this->fieldInControl, $this->columns)) {
            $this->columns[$this->fieldInControl]['size'] = $size;
        }
        return $this;
    }

    public function requried(): DBTableCreator {
        if (isset($this->fieldInControl) && key_exists($this->fieldInControl, $this->columns)) {
            $this->columns[$this->fieldInControl]['required'] = true;
        }
        return $this;
    }

    public function numberCol($column, $required = false): DBTableCreator {
        $options = ['type' => self::INT_FIELD, 'size' => 11];
        return $this->addColumn($column, $options, $required);
    }

    public function modifyField($column, $options = []) {
        if (key_exists($column, $this->columns)) {
            $this->columns[$column] = array_merge($this->columns[$column], $options);
        }
    }

    public function getCols(): mixed {
        return $this->columns;
    }

    public function getTable(): string {
        return $this->tableName;
    }

    public function pkBigInt($column): DBTableCreator {
        $options = [
            'type' => self::BIG_INT,
            'autoIncrement' => true,
            'size' => null
        ];
        $this->pk = $column;
        return $this->addColumn($column, $options, true);
    }

    public function pkCol($column, $uuid = false): DBTableCreator {
        $options = [
            'type' => $uuid? self::STRING_FIELD : self::INT_FIELD, 
            'autoIncrement' => !$uuid,
            'uuid' => $uuid,
            'size' => $uuid? 255 : 11
        ];
        $this->pk = $column;
        return $this->addColumn($column, $options, true);
    }

    public function getPk() {
        return $this->pk;
    }
}