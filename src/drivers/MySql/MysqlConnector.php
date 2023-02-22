<?php

namespace Alejodevop\YowlOrm\Drivers\Mysql;
use Alejodevop\YowlOrm\Core\DBConnector;
use Alejodevop\YowlOrm\Exceptions\DBConnectionException;

/**
 * Class to handle the mysql connection
 */
class MysqlConnector extends DBConnector {
    
	/**
     * Connects to the mysql server.
	 * @return mixed
	 */
	public function connect() {
        try {
            $connString = "mysql:host={$this->_host};dbname={$this->_database}";
            $this->_connection = new \PDO($connString, $this->_user, $this->_password);
            $this->_connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);            
        } catch (\Exception $e) {
            throw new DBConnectionException($e->getMessage());
        }
	}
	
	/**
	 * Executes a raw query.
	 * @param string $query
	 * @return mixed
	 */
	public function execQuery(string $query) {
        $this->statement = $this->_connection->prepare($query);
        $result = $this->statement->execute();
        $this->rowCount = $this->statement->rowCount();
        return $result;
	}
	
	/**
	 * Fetch the returned data returned by the mysql database.
	 * @param bool $all
	 * @return array
	 */
	public function fetch(bool $all = true): array {
        $type = \PDO::FETCH_ASSOC;
        return $all? $this->statement->fetchAll($type) : $this->statement->fetch($type);
	}
	
	/**
     * Disconnects from the msyql server
	 * @return mixed
	 */
	public function disconnect() {
        $this->_connection = null;
	}
	
	/**
     * Returns the mysql server error.
	 * @return mixed
	 */
	public function getError() {
        return $this->_connection->errorInfo();
	}

    /**
     * Returns the last inserted id to the mysql database.
     *
     * @return mixed
     */
    public function lastInsertId() {
        return $this->_connection->lastInsertId();
    }
}