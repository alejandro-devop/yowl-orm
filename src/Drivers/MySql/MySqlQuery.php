<?php

namespace Alejodevop\YowlOrm\Drivers\MySql;
use Alejodevop\YowlOrm\Core\DBQuery;

/**
 * Implementation of a query in mysql
 * @author Alejandro Quiroz <alejandro.devop@gmail.com>
 * @version 1.0.0
 * @since 1.0.0
 */
class MySqlQuery extends DBQuery {	
	 
	/**
     * Builds the implementation of a select in mysql
	 * @return string
	 */
	public function getSelectQuery(): string {
        $joins = $this->resolveJoins();
        $joinColumns = count($this->joinColumns) > 0 ? ", " . implode(', ', $this->joinColumns) : "";
        $columns = $this->buildColumns(true) . $joinColumns;

        if (isset($this->max)) {
            $columns = "MAX(" . $this->max . ") as " . $this->maxAlias;
        }

        $table = $this->getTable();
        $alias = $this->getAlias();
        $query = ["SELECT {$columns} FROM $table AS {$alias}"];
        $query[] = $joins;
        $query[] = $this->resolveConditions(); 
        $query[] = $this->resolveOrdering();
        $query[] = $this->resolveLimit();
        $queryToExecute = implode(' ', $query);
        return $queryToExecute;
	}
	
	/**
     * Builds the implementation of an insert query in mysql
	 * @return string
	 */
	public function getInsertQuery(): string {
        $columns = $this->buildColumns(false);
        $values = $this->buildValues();
        $table = $this->getTable();
        $query = "INSERT INTO {$table}($columns) VALUES ({$values})";
        return $query;
	}
	
	/**
     * Builds the implementation of an update query in mysql.
	 * @return string
	 */
	public function getUpdateQuery(): string {
        $values = $this->buildValuesUpdate();
        $table = $this->getTable();
        $query = ["UPDATE {$table} SET {$values}"];
        $query[] = $this->resolveConditions();
        return implode(' ', $query);
	}
	
	/**
     * Builds the implementation of an update query in mysql.
	 * @return string
	 */
	public function getDeleteQuery(): string {
        $table = $this->getTable();
        $query = ["DELETE FROM {$table}"];
        $query[] = $this->resolveConditions();
        return implode(' ', $query);
	}

    /**
     * Builds the conditions using mysql syntax.
     *
     * @return string
     */
    public function resolveConditions(): string {
        $result = [];
        $conditions = $this->getConditions();
        foreach($conditions as $condition) {
            ['type' => $type] = $condition;
            if ($type === 'condition') {
                $condResult = $this->resolveCondition($condition);
                if (is_null($condResult)) continue;
                $result[] = $condResult;
            } else if ($type === 'connector') {
                $result[] = $condition['connector'];
            } else {
                continue;
            }
        }
        return count($result) > 0? "WHERE " . implode(' ', $result) : "";
    }

    /**
     * Resolve a given condition
     *
     * @param [type] $condition
     * @return string
     */
    public function resolveCondition($condition): string {
        [
            'operator' => $operator, 
            'field' => $field, 
            'compare' => $compare,
            'from' => $from,
            'to' => $to
        ] = $condition;

        switch($operator) {
            case DBQuery::COND_EQUALITY: return "`{$field}` = '{$compare}'";
            case DBQuery::COND_NON_EQUALITY: return "`{$field}` <> '{$compare}'";
            case DBQuery::COND_IS_NULL: return "`{$field}` IS NULL";
            case DBQuery::COND_IS_NOT_NULL: return "`{$field}` IS NOT NULL";
            case DBQuery::COND_LIKE: return $this->resolveLikeCondition($condition);
            case DBQuery::COND_NOT_LIKE: return $this->resolveLikeCondition($condition, true);
            case DBQuery::COND_IN: return $this->resolveInCondition($condition);
            case DBQuery::COND_NOT_IN: return $this->resolveInCondition($condition, true);
            case DBQuery::COND_IS_BETWEEN: return "`{$field}` BETWEEN '{$from}' AND '{$to}'";
            case DBQuery::COND_GT: return "`{$field}` > '{$compare}'";
            case DBQuery::COND_GET: return "`{$field}` >= '{$compare}'";
            case DBQuery::COND_LT: return "`{$field}` < '{$compare}'";
            case DBQuery::COND_LET: return "`{$field}` <= '{$compare}'";
            default: return null;
        }
    }
    /**
     * Builds the implementation of a in condition in mysql.
     *
     * @param array $condition
     * @param boolean $not
     * @return string
     */
    public function resolveInCondition(array $condition, $not = false): string {
        [
            'field' => $field, 
            'values' => $values
        ] = $condition;
        return "`{$field}`" . ($not? " NOT " : " ") . "IN (". 
        implode(', ', array_map(fn ($value) => "'{$value}'", $values)) . 
        ")";
    }

    /**
     * Builds the implementation of a like condition in mysql.
     *
     * @param array $condition
     * @param boolean $not
     * @return string
     */
    public function resolveLikeCondition(array $condition, bool $not = false): string {
        [
            'field' => $field, 
            'compare' => $compare,
            'options' => $options,
        ] = $condition;
        $value = "{$compare}";
        if ($options === DBQuery::LIKE_ANY_WHERE) $value = "%{$compare}%";
        else if ($options === DBQuery::LIKE_BEGIN) $value = "{$compare}%";
        else if ($options === DBQuery::LIKE_END) $value = "%{$compare}";
        return "`{$field}`" . ($not? " NOT " : "") . " LIKE '{$value}'";
    }

    public function resolveOrdering(): string|null {
        $output = [];
        foreach($this->order as $item) {
            [$field, $asc] = $item;
            $ouput[] = $field . " " . ($asc ? "ASC" : "DESC");
        }
        if (count($output) > 0) {
            return "ORDER BY " . implode(', ', $ouput);
        }
        return null;
    }

    public function resolveLimit(): string|null {
        if (isset($this->limit) && !isset($this->offset)) {
            return "LIMIT " . $this->limit;
        } else if (isset($this->limit) && isset($this->offset)) {
            return "LIMIT " . $this->limit . " OFFSET " . $this->offset;
        }
        return null;
    }

    public function resolveJoins(): string | null {
        $output = [];
        foreach($this->joins as $join) {
            [
                'relTable' => $table,
                'alias' => $alias,
                'relPk' => $relPk,
                'tablePk' => $tablePk,
                'fields' => $fields,
                'goesTo' => $goesTo,
                'goesFrom' => $goesFrom
            ] = array_merge(['goesTo' => null, 'fields' => [], 'goesFrom' => null], $join);

            $targetAlias = !is_null($goesTo)? $goesTo : $alias;
            $join = "LEFT JOIN {$table} AS {$alias} ON {$goesFrom}.{$relPk} = {$targetAlias}.{$tablePk}";
            foreach ($this->aliasesMap as $table=>$tableAlias) {
                $join = str_replace("$table.", "$tableAlias.", $join);
            }
            $output[] = $join;
            $this->joinColumns = array_merge(
                $this->joinColumns, 
                array_map(fn ($field) => "{$alias}.{$field} as {$alias}__{$field}", $fields)
            );
        }
        return implode(' ', $output);
    }
}