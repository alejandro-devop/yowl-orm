<?php

namespace Alejodevop\YowlOrm\Core;

/**
 * Class which must be implemented for every valida Database query.
 * @author Alejandro Quiroz <alejandro.devop@gmail.com>
 * @version 1.0.0
 * @since 1.0.0
 */
abstract class DBQuery {
    const COND_EQUALITY = 'equals';
    const COND_NON_EQUALITY = 'not-equals';
    const COND_LIKE = 'like';
    const COND_NOT_LIKE = 'not-like';
    const COND_IN = 'in';
    const COND_NOT_IN = 'not-in';
    const COND_IS_NULL = 'is-null';
    const COND_IS_NOT_NULL = 'is-not-null';
    const COND_IS_BETWEEN = 'is-between';
    const COND_GT = 'greater-then';
    const COND_GET = 'greater-equals-to';
    const COND_LT = 'lower-then';
    const COND_LET = 'lower-equals-to';

    const LIKE_ANY_WHERE = 'all';
    const LIKE_BEGIN = 'begin';
    const LIKE_END = 'end';
    protected $aliasesMap = [];
    /**
     * The separator which will be used for the next condition.
     *
     * @var string
     */
    protected string $connector = 'AND';
    protected string $max;
    protected string $maxAlias;
    /**
     * Columns to be used by the current query.
     *
     * @var array
     */
    protected array $columnNames = [];
    /**
     * Values to be used by the current query.
     *
     * @var array
     */
    protected array $values = [];
    /**
     * Joins to be used for a select query.
     *
     * @var array
     */
    protected array $joins = [];
    protected array $joinColumns = [];
    /**
     * Conditions to be used for the current query.
     */

    protected array $conditions = [];

    /**
     * Grouping condition to be used for the next query.
     *
     * @var string
     */
    protected string $group;
    /**
     * If the current condition is grouped
     *
     * @var boolean
     */
    protected bool $isGrouped = false;
    /**
     * If the current condition has any having condition
     *
     * @var string
     */
    protected string $having;
    /**
     * If the current condition has order.
     *
     * @var array
     */
    protected array $order = [];
    /**
     * Table to be used for the next query.
     *
     * @var string
     */
    protected string $table;
    /**
     * Alias to be used in for the queries.
     *
     * @var string
     */
    protected string $tableAlias = 't';
    /* MySql exclusive */
    /**
     * If the current query has any limit
     *
     * @var [type]
     */
    protected $limit;
    /**
     * If the current query has an offset.
     *
     * @var [type]
     */
    protected $offset;

    /**
     * Function to set the current query.
     *
     * @param [type] $table
     * @return DBQuery
     */
    public function setTable($table): DBQuery {
        $this->table = $table;
        return $this;
    }

    /**
     * Function to set the current column names.
     *
     * @param [type] $columnNames
     * @return DBQuery
     */
    public function setColumnNames($columnNames): DBQuery {
        $this->columnNames = $columnNames;
        return $this;
    }
    /**
     * Function to set the current values.
     *
     * @param [type] $values
     * @return DBQuery
     */
    public function setValues($values): DBQuery {
        $this->values = $values;
        return $this;
    }
    /**
     * Must be implemented by every driver query to define select queries.
     *
     * @return string
     */
    public abstract function getSelectQuery(): string;
    /**
     * Must be implemented by every driver query to define insert queries.
     *
     * @return string
     */
    public abstract function getInsertQuery(): string;
    /**
     * Must be implemented by every driver query to define update queries.
     *
     * @return string
     */
    public abstract function getUpdateQuery(): string;
    /**
     * Must be defined by every driver query to define delete queries.
     *
     * @return string
     */
    public abstract function getDeleteQuery(): string;
    /**
     * Must be implemented by every driver query to resolve the conditions.
     *
     * @return string
     */
    public abstract function resolveConditions(): string;
    /**
     * Must be implemented by every driver query to resolve a single condition.
     *
     * @param [type] $condition
     * @return string
     */
    public abstract function resolveCondition($condition): string;
    /**
     * Must be implemented by every driver query to resolve IN conditions.
     *
     * @param array $condition
     * @param boolean $not
     * @return string
     */
    public abstract function resolveInCondition(array $condition, $not = false): string;
    /**
     * Must be implemented by every driver query to resolve LIKE conditions.
     *
     * @param array $condition
     * @param boolean $not
     * @return string
     */
    public abstract function resolveLikeCondition(array $condition, bool $not = false): string;

    public abstract function resolveOrdering(): string|null;

    public abstract function resolveLimit(): string|null;

    public abstract function resolveJoins():string|null;

    protected function addConnector() {
        if(count($this->conditions) > 0) {
            $this->conditions[] = ['type' => 'connector', 'connector' => $this->connector];
        }
    }

    /**
     * Add conditions to the conditions stack.
     *
     * @param array $config
     * @return DBQuery
     */
    protected function addCondition(array $config): DBQuery {
        $this->addConnector();
        $this->conditions[] = array_merge([
            'operator' => null,
            'field' => null,
            'compare' => null,
            'options' => null,
            'values' => null,
            'from' => null,
            'to' => null,
        ], $config);
        return $this;
    }
    /**
     * Add a equals condition to the stack.
     *
     * @param string $field
     * @param mixed $compare
     * @return DBQuery
     */
    public function equals(string $field, mixed $compare): DBQuery {
        return $this->addCondition([
            'type' => 'condition', 
            'field' => $field, 
            'compare' => $compare,
            'operator' => self::COND_EQUALITY
        ]);
    }

    /**
     * Add a not equals condition to the stack.
     *
     * @param string $field
     * @param mixed $compare
     * @return DBQuery
     */
    public function notEquals(string $field, mixed $compare): DBQuery {
        return $this->addCondition([
            'type' => 'condition', 
            'field' => $field, 
            'compare' => $compare,
            'operator' => self::COND_NON_EQUALITY
        ]);
    }

    public function addJoin(array $config = [], $type = '', $alias = ''): DBQuery {
        [
            'table' => $table,
            'field' => $field,
            'table_pk' => $pk,
            'goesTo' => $goesTo,
            'goesFrom' => $goesFrom,
            'rel_fields' => $relFields] = $config;

        $this->joins[] = [
            'relTable' => $table,
            'relPk' => $field,
            'tablePk' => $pk,
            'goesTo' => $goesTo,
            'goesFrom' => $goesFrom,
            'type' => $type,
            'alias' => $alias,
            'fields' => $relFields,
        ];
        return $this;
    }

    public function setAliasesMap(array $aliasesMap) {
        $transformed = [];
        foreach ($aliasesMap as $key=>$value) {
            $transformed[$value['table']] = $key;
        }
        $this->aliasesMap = $transformed;
    }

    /**
     * Add like condition to the stack.
     *
     * @param string $field
     * @param string $compare
     * @param [type] $where
     * @return DBQuery
     */
    public function likeCondition(string $field, string $compare, $where = DBQuery::LIKE_ANY_WHERE): DBQuery {
        return $this->addCondition([
            'type' => 'condition',
            'field' => $field,
            'compare' => $compare,
            'operator' => self::COND_LIKE,
            'options' => $where,
        ]);
    }
    /**
     * Add a LIKE condition to the stack.
     *
     * @param string $field
     * @param string $compare
     * @param [type] $where
     * @return DBQuery
     */
    public function notLikeCondition(string $field, string $compare, $where = DBQuery::LIKE_ANY_WHERE): DBQuery {
        return $this->addCondition([
            'type' => 'condition',
            'field' => $field,
            'compare' => $compare,
            'operator' => self::COND_NOT_LIKE,
            'options' => $where,
        ]);
    }

    /**
     * Add an IN condition to the stack.
     *
     * @param string $field
     * @param array $values
     * @return DBQuery
     */
    public function inCondition(string $field, array $values = []): DBQuery {
        return $this->addCondition([
            'type' => 'condition',
            'field' => $field,
            'values' => $values,
            'operator' => self::COND_IN
        ]);
    }
    /**
     * Add an NOT IN Condition to the stack.
     *
     * @param string $field
     * @param array $values
     * @return DBQuery
     */
    public function notInCondition(string $field, array $values = []): DBQuery {
        return $this->addCondition([
            'type' => 'condition',
            'field' => $field,
            'values' => $values,
            'operator' => self::COND_NOT_IN
        ]);
    }
    /**
     * Add a Between between condition to the stack.
     *
     * @param string $field
     * @param string $from
     * @param string $to
     * @return DBQuery
     */
    public function isBetweenCondition(string $field, string $from, string $to): DBQuery {
        return $this->addCondition([
            'type' => 'condition',
            'field' => $field,
            'from' => $from,
            'to' => $to,
            'operator' => self::COND_IS_BETWEEN
        ]);
    }

    /**
     * Add a is null condition to the stack.
     *
     * @param string $field
     * @return DBQuery
     */
    public function isNullCondition(string $field): DBQuery {
        return $this->addCondition([
            'type' => 'condition',
            'field' => $field,
            'operator' => self::COND_IS_NULL
        ]);
    }

    /**
     * Add a NOT IS NULL condition.
     *
     * @param string $field
     * @return DBQuery
     */
    public function isNotNullCondition(string $field): DBQuery {
        return $this->addCondition([
            'type' => 'condition',
            'field' => $field,
            'operator' => self::COND_IS_NOT_NULL
        ]);
    }

    /**
     * Add a greater then condition to the conditions stack.
     *
     * @param string $field
     * @param mixed $compare
     * @return DBQuery
     */
    public function greaterThenCondition(string $field, mixed $compare): DBQuery {
        return $this->addCondition([
            'type' => 'condition',
            'field' => $field,
            'compare' => $compare,
            'operator' => self::COND_GT
        ]);
    }
    /**
     * Add a greater or equals to condition to the conditions stack.
     *
     * @param string $field
     * @param mixed $compare
     * @return DBQuery
     */
    public function greaterEquealsToCondition(string $field, mixed $compare): DBQuery {
        return $this->addCondition([
            'type' => 'condition',
            'field' => $field,
            'compare' => $compare,
            'operator' => self::COND_GET
        ]);
    }
    /**
     * Add a lower then condition to the conditions stack.
     *
     * @param string $field
     * @param mixed $compare
     * @return DBQuery
     */
    public function lowerThenCondition(string $field, mixed $compare): DBQuery {
        return $this->addCondition([
            'type' => 'condition',
            'field' => $field,
            'compare' => $compare,
            'operator' => self::COND_LT
        ]);
    }
    /**
     * Add a lower or equals to condition to the stack.
     *
     * @param string $field
     * @param mixed $compare
     * @return DBQuery
     */
    public function lowerEquealsToCondition(string $field, mixed $compare): DBQuery {
        return $this->addCondition([
            'type' => 'condition',
            'field' => $field,
            'compare' => $compare,
            'operator' => self::COND_LET
        ]);
    }

    /**
     * Add the table used for the current query.
     *
     * @return string
     */
    public function getTable(): string {
        return $this->table;
    }
    /**
     * Returns the column names used for the current query.
     *
     * @return array
     */
    public function getColumnNames(): array {
        return $this->columnNames;
    }
    /**
     * Returns the values used for the current query.
     *
     * @return array
     */
    public function getValues(): array {
        return $this->values;
    }
    /**
     * Returns the current alias used.
     *
     * @return string
     */
    public function getAlias() {
        return $this->tableAlias;
    }
    /**
     * Builds the columns structure to be used in the query (In most databases is the same)
     *
     * @param boolean $alias
     * @return string
     */
    public function buildColumns(bool $alias = false): string {
        $columns = implode(
            ', ', 
            array_map(fn ($column) => ($alias? "{$this->tableAlias}." : "") . "{$column}", 
            $this->columnNames)
        );
        return $columns;
    }

    public function max($max, $alias) {
        $this->max = $max;
        $this->maxAlias = $alias;
    }
    /**
     * Builds the values structure to be used in the current query (In most databases is the same)
     *
     * @return string
     */
    public function buildValues(): string {
        $values = implode(', ', 
            array_map(
                fn ($value) => is_null($value)? "NULL" : "'{$value}'", 
                $this->values
            )
        );
        return $values;
    }
    /**
     * Builds the structure for the values to be used in the current query (In most databases is the same)
     *
     * @return string
     */
    public function buildValuesUpdate(): string {
        $values = implode(
            ', ', 
            array_map(
                fn ($column, $value) => "{$column} = " . (is_null($value)? 'NULL' :"'{$value}'"), 
                $this->columnNames, $this->values
            )
        );
        return $values;
    }
    /**
     * Sets the current connector to the query to AND (in most databases is the same but can be overriden)
     *
     * @return DBQuery
     */
    public function and(): DBQuery {
        $this->connector = "AND";
        return $this;
    }

    /**
     * Sets the current connector the query to OR (In most databases is the same but can be overriden)
     *
     * @return DBQuery
     */
    public function or(): DBQuery {
        $this->connector = "OR";
        return $this;
    }

    /**
     * REturns the current conditions stack.
     *
     * @return array
     */
    public function getConditions(): array {
        return $this->conditions;
    }

    public function order(string $field, bool $asc = true) {
        $this->order[] = [$field, $asc];
    }

    public function limit(int $limit, int $offset = null) {
        $this->limit = $limit;
        $this->offset = $offset;
    }

    /**
     * Clears the query.
     *
     * @return void
     */
    public function clear() {
        $this->table = "";
        $this->joins = [];
        $this->conditions = [];
        $this->isGrouped = false;
        $this->group;
        $this->order = "";
        $this->limit = null;
        $this->offset = null;
        $this->columnNames = [];
        $this->values = [];
    }
}