<?php

namespace Alejodevop\YowlOrm\Drivers\Mysql;
use Alejodevop\YowlOrm\Core\DBTableCreator;


class MySqlTableCreator extends DBTableCreator {

    public function getTableCreationQuery(): string{
        $sql = [
            "CREATE TABLE IF NOT EXISTS {$this->tableName}"
        ];
        $cols = [];
        foreach($this->columns as $col=>$config) {
            $cols[] = $this->resolveColumn($col, $config);
        }
        $sql[] = "(" . implode(', ', $cols) . ")";
        return implode(' ', $sql);
    }

    public function resolveColumn($colName, $colConfig = []) {
        if ($colName === $this->pk) {
            return $this->resolvePk($colName, $colConfig);
        } else {
            return $this->resolveField($colName, $colConfig);
        }
    }

    private function resolveField($field, $config = []) {
        $size = $config['size']?? null;
        $required = $config['required']?? null;
        $unique = $config['unique']?? false;
        $fieldType = $this->resolveType($config['type'] ?? null) . (is_null($size) ? '' : "({$size})");
        $output = ["{$field} {$fieldType}"];
        if ($required) {
            $output[] = "NOT NULL";
        }
        if ($unique) {
            $output[] = "UNIQUE";
        }
        return implode(' ', $output);
    }

    private function resolveType($type) {
        switch ($type) {
            case DBTableCreator::INT_FIELD: return "INT";
            case DBTableCreator::BOOL_FIELD: return "TINYINT";
            case DBTableCreator::STRING_FIELD: return "VARCHAR";
            case DBTableCreator::TEXT_FIELD: return "TEXT";
            case DBTableCreator::DATE_FIELD: return "DATE";
            case DBTableCreator::BIG_INT: return "BIGINT";
            case DBTableCreator::TIMESTAMP_FIELD: return "TIMESTAMP";
            default: return "";
        }
    }

    private function resolvePk($col, $colConfig = []) {
        $autoIncrement = $colConfig['autoIncrement'];
        $size = $colConfig['size']?? null;
        $type = $colConfig['type']??null;
        $fieldType = $this->resolveType($colConfig['type'] ?? null) . (is_null($size) ? '' : "({$size})");
        if ($type === DBTableCreator::STRING_FIELD) {
            return "{$col} {$fieldType} PRIMARY KEY NOT NULL";
        } else if ($type === DBTableCreator::INT_FIELD || $type === DBTableCreator::BIG_INT) {
            return "{$col} {$fieldType} UNSIGNED PRIMARY KEY " . ($autoIncrement? "AUTO_INCREMENT" : "") . " NOT NULL ";
        }
    }
    
}