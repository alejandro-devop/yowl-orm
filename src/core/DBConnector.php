<?php

namespace Alejodevop\YowlOrm\Core;

/**
 * Base class for all database connector
 * @author Alejandro Quiroz <alejandro.devop@gmail.com>
 * @version 1.0.0
 * @since 1.0.0
 */
abstract class DBConnector {
    /**
     * Statement executed
     *
     * @var \PDOStatement
     */
    protected \PDOStatement $statement;
    /**
     * Row count by the latest query
     *
     * @var integer
     */
    protected int $rowCount = 0;
    /**
     * Current database host.
     *
     * @var string
     */
    protected string $_host;
    /**
     * Current database user.
     *
     * @var string
     */
    protected string $_user;
    /**
     * Current database password.
     *
     * @var string
     */
    protected string $_password;
    /**
     * Current database name
     *
     * @var string
     */
    protected string $_database;
    /**
     * Current database port.
     *
     * @var string
     */
    protected string $_port;
    /**
     * Current database connection.
     *
     * @var \PDO|null
     */
    protected \PDO|null $_connection;

    public function __construct(array $config) {
        [
            'host' => $this->_host,
            'user' => $this->_user,
            'password' => $this->_password,
            'database' => $this->_database,
            'port' => $this->_port
        ] = $config;
        if (is_null($this->_port)) {
            $this->_port === '3306';
        }
    }

    /**
     * Must be implemented to handle the database connection.
     *
     * @return void
     */
    public abstract function connect();
    /**
     * Must be implemented to execute a query which returns data
     *
     * @param string $query
     * @return mixed
     */
    public abstract function execQuery(string $query);
    /**
     * Must be implemented to fetch data from the statements
     *
     * @param boolean $all
     * @return array
     */
    public abstract function fetch(bool $all = true): array;
    /**
     * Must be implemented to disconnect from the database server.
     *
     * @return mixed
     */
    public abstract function disconnect();
    /**
     * Must be implemented to return the database error.
     */
    public abstract function getError();
    /**
     * Must be implemented to return the database error.
     *
     * @return mixed
     */
    public abstract function lastInsertId();
    /**
     * Must be implemented to return the affected rows count.
     *
     * @return mixed
     */
    public function affectedRows() {
        return $this->rowCount;
    }
}