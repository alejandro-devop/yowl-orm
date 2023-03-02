<?php

namespace Alejodevop\YowlOrm\Drivers\Mysql;
use Alejodevop\YowlOrm\Core\DBDriver;
use Alejodevop\YowlOrm\Core\DBField;
use Alejodevop\YowlOrm\Core\DBQuery;
use Alejodevop\YowlOrm\Core\DBTableCreator;

/**
 * Mysql Driver, translates instructions and converts Query objects to mysql valid queries.
 * @author Alejandro Quiroz <alejandro.devop@gmail.com>
 * @version 1.0.0
 * @since 1.0.0
 */
class MysqlDriver extends DBDriver {
    public function init() {
        $this->connector = new MysqlConnector($this->configuration);
        $this->connector->connect();
    }

    /**
     * Implementation of a describe query in mysql.
     *
     * @param string $tableName
     * @param bool $asArray
     * @return array
     */
    public function describe(string $tableName, bool $asArray = false): array {
        $query = "DESCRIBE {$tableName}";
        $result = $this->query($query);
        $fields = [];
        foreach($result AS $fieldInfo){
            $fields[] = $this->createField($fieldInfo, $asArray);
        }
        return $fields;
    }

    public function tableExists($tableName): bool {
        $dbName = $this->connector->getDatabaseName();
        $sql = "SELECT COUNT(*) as matches FROM information_schema.tables " 
        ."WHERE table_schema = '{$dbName}' AND table_name = '{$tableName}'";
        $result = $this->query($sql);
        if (count($result) > 0) {
            $found = $result[0] ?? [];
            return ($found['matches'] ?? 0) > 0;
        } else {
            return false;
        }
        
    }

    public function getTables(): mixed {
        $dbName = $this->connector->getDatabaseName();
        $query = "SELECT table_name AS `table_name` FROM information_schema.tables t WHERE t.table_schema = '{$dbName}'";
        $result = $this->query($query);
        return $result;
    }

    /**
     * Executes a given raw query which returns data.
     *
     * @param string $query
     * @param boolean $many
     * @return mixed
     */
    public function query(string $query, $many = true): mixed {
        $result = [];
        if ($this->execQuery($query)) {
            $result = $this->connector->fetch($many);
        }
        return $result;
    }

	/**
     * Executes a raw query which doesn't return data.
	 * @param string $query
	 * @param bool $many
	 * @return bool
	 */
	public function execQuery(string $query): bool {
        return $this->connector->execQuery($query);
	}

    /**
     * Creates a field used for dedescribe function
     *
     * @param array $fieldInfo
     * @param bool $asArray
     * @return DBField|array
     */
    public function createField(array $fieldInfo, bool $asArray = false): DBField|array {
        preg_match('#\((.*?)\)#', $fieldInfo['Type'], $match);
        $length = $match[1] ?? 0;
        $type = isset($match[0]) ? str_replace($match[0], '', $fieldInfo['Type']) : $fieldInfo['Type'];

        $field = (new DBField())
        ->setName($fieldInfo['Field'])
        ->setType($type)
        ->setLength($length)
        ->isNullable($fieldInfo['Null'] === 'YES')
        ->isPrimary($fieldInfo['Key'] === 'PRI')
        ->setDefault($fieldInfo['Default'])
        ->setExtra($fieldInfo['Extra']);

        if ($asArray) {
            return $field->asArray();
        }
        return $field;
    }

    /**
     * Creates a mysql query object.
     *
     * @return DBQuery
     */
    public function createQuery(): DBQuery {
        return new MySqlQuery();
    }
	/**
     * Allows to set the driver the current query to be executed.
	 * @param DBQuery $query
	 * @return mixed
	 */
	public function setQuery(DBQuery $query) {
        $this->currentQuery = $query;
	}

	/**
     * Executes an insert query.
	 * @return bool
	 */
	public function insert(): bool {
        $this->beforeQuery();
        $query = $this->currentQuery->getInsertQuery();
        return $this->execQuery($query);
	}
	
	/**
     * Executes a select query.
	 * @return mixed
	 */
	public function select(): mixed {
        $this->beforeQuery();
        $queryToBeExecuted = $this->currentQuery->getSelectQuery();
        // echo "query: " . $queryToBeExecuted . "<br>";
        // exit();
        $result = $this->query($queryToBeExecuted);
        return $result;
	}
	
	/**
     * Executes an update query.
	 * @return bool
	 */
	public function update(): bool {
        $this->beforeQuery();
        $queryToExecute = $this->currentQuery->getUpdateQuery();
        return $this->execQuery($queryToExecute);
	}
	
	/**
     * Executes a delete query.
	 * @return bool
	 */
	public function delete(): bool {
        $queryToExecute = $this->currentQuery->getDeleteQuery();
        return $this->execQuery($queryToExecute);
	}

    public function lastInsertId(): mixed {
        return $this->connector->lastInsertId();
    }

    public function getTableCreator(): DBTableCreator {
        return new MySqlTableCreator();
    }

    public function createTable(DBTableCreator $dBTableCreator) : mixed {
        $sql = $dBTableCreator->getTableCreationQuery();
        echo $sql . "\n";
        $result = $this->execQuery($sql);
        return $result;
    }
}