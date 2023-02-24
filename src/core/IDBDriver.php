<?php

namespace Alejodevop\YowlOrm\Core;

/**
 * This interface defines the methods which every Database Driver should implement.
 * @author Alejandro Quiroz <alejandro.devop@gmail.com>
 * @version 1.0.0
 * @since 1.0.0
 */
interface IDBDriver {
    /**
     * Must be implemented to describe a table
     *
     * @param string $tableName
     * @param bool $asArray
     * @return array
     */
    public function describe(string $tableName, bool $asArray): array;
    /**
     * Must be implemented to executed queries which returns data.
     *
     * @param string $query
     * @param boolean $many
     * @return mixed
     */
    public function query(string $query, bool $many = true): mixed;
    /**
     * Must be implemented to execute a query which does not returns data.
     *
     * @param string $query
     * @return boolean
     */
    public function execQuery(string $query): bool;
    /**
     * Must be implemented to create query objects.
     *
     * @return DBQuery
     */
    public function createQuery(): DBQuery;

    /**
     * Must be implemented to set the query to be executed.
     *
     * @param DBQuery $query
     * @return void
     */
    public function setQuery(DBQuery $query);

    /**
     * Must be implemented to create and execute insert queries
     *
     * @return boolean
     */
    public function insert(): bool;
    /**
     * Must be implemented to create and execute select queries.
     *
     * @return mixed
     */
    public function select(): mixed;
    /**
     * Must be implemented to create and execute update queries.
     *
     * @return boolean
     */
    public function update(): bool;
    /**
     * Must be implemented to create and execute delete queries.
     *
     * @return boolean
     */
    public function delete(): bool;

    public function getTables(): mixed;

    public function lastInsertId(): mixed;
}