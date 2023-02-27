<?php

namespace Alejodevop\YowlOrm;
use Alejodevop\YowlOrm\Core\DBCache;
use Alejodevop\YowlOrm\Core\DBDriver;
use Alejodevop\YowlOrm\Core\DBQuery;
use Alejodevop\YowlOrm\Core\DBTableCreator;
use Alejodevop\YowlOrm\Core\IDBDriver;
use Alejodevop\YowlOrm\Exceptions\DBTableNotFoundExeption;
use Alejodevop\YowlOrm\Exceptions\InvalidDriverException;

/**
 * DB manager offers interface to communicate directly with the Database manager
 * and connector.
 * @author Alejandro Quiroz <alejandro.devop@gmail.com>
 * @version 1.0.0
 * @since 1.0.0
 */
class DBManager implements IDBDriver {
    const MYSQL_DRIVER = 'MySql';
    private $cacheDir = '';
    /**
     * List of the available drivers
     *
     * @var array
     */
    private $validDrivers = [];
    /**
     * Current driver loaded
     *
     * @var DBDriver
     */
    private DBDriver $driver;

    private DBCache $dbCache;

    private array $schema = [];

    private function __construct(){
        $this->validDrivers = [
            DBManager::MYSQL_DRIVER,
        ];
    }

    /**
     * Function to load the current database driver.
     *
     * @param string $driverName Should be one of the available drivers
     * @param array $config
     * @return DBManager
     */
    public function loadDriver(string $driverName, array $config): DBManager {
        if (!in_array($driverName, $this->validDrivers))  {
            throw new InvalidDriverException("'$driverName' Is not a valid driver ");
        }
        $namespace = "\\Alejodevop\\YowlOrm\\Drivers\\{$driverName}\\{$driverName}Driver";
        ['cache_dir' => $this->cacheDir] = array_merge(['cache_dir' => null], $config);
        $this->driver = new $namespace($config);
        $this->driver->init();
        $this->dbCache = new DBCache($this->cacheDir);
        return $this;
    }

    public function initCache($override = false) {
        if (!$this->dbCache->checkGeneratedCache() || $override) {
            $this->dbCache->generateCache();
        }
        $path = $this->cacheDir . DS . "database.php";
        $this->schema = include_once($path);
    }

    public function updateCache() {
        $this->initCache(true);
    }

    public function getFromSchema($tableName) {
        if (!key_exists($tableName, $this->schema)) {
            throw new DBTableNotFoundExeption("Table {$tableName} not found in schema");
        }
        return $this->schema[$tableName];
    }

    /**
     * Allows to get the unique intance of the Database manager.
     *
     * @return DBManager
     */
    public static function getInstance(): DBManager {
        static $instance = null;
        if ($instance === null) $instance = new DBManager();
        return $instance;
    }

    /**
     * Communicates with the database driver to execute a describe query.
     *
     * @param string $tableName
     * @param bool $asArray
     * @return array
     */
    public function describe(string $tableName, bool $asArray): array {
        return $this->driver->describe($tableName, $asArray);
    }

    /**
     * Communicates with the database driver to execute a query which retrieves data.
     * 
     * @param string $query
     * @param bool $many
     * @return array
     */
    public function query(string $query, bool $many = true): array {
        return $this->driver->query($query, $many);
    }

    /**
     * Communicates with the database driver to execute a query which does not 
     * return data.
     *
     * @param string $query
     * @return boolean
     */
    public function execQuery(string $query): bool {
        return $this->driver->execQuery($query);
    }

    /**
     * Communicates with the database driver to get a query object.
     *
     * @return DBQuery
     */
    public function createQuery(): DBQuery {
        return $this->driver->createQuery();
    }

    /**
     * Communicates with the database driver to set the query to be executed.
     *
     * @param DBQuery $query
     * @return void
     */
    public function setQuery(DBQuery $query) {
        return $this->driver->setQuery($query);
    }

    /**
     * Communicates with the database driver to execute a insert query.
     * @return bool
     */
    public function insert(): bool {
        return $this->driver->insert();
    }

    /**
     * Communicates with the database driver to execute a select query
     * which returns data.
     * @return mixed
     */
    public function select(): mixed {
        return $this->driver->select();
    }

    /**
     * Communicates with the database driver to execute an update query.
     * @return bool
     */
    public function update(): bool {
        return $this->driver->update();
    }

    /**
     * Communicates with the database driver to execute a delete query.
     * @return bool
     */
    public function delete(): bool {
        return $this->driver->delete();
    }

    public function getTables(): mixed {
        return $this->driver->getTables();
    }

    public function getDatabaseName(): string {
        return $this->driver->getDatabaseName();
    }

    public function lastInsertId(): mixed {
        return $this->driver->lastInsertId();
    }

    public function tableExists(string $tableName): bool {
        return $this->driver->
        tableExists($tableName);
    }

    public function getTableCreator(): DBTableCreator {
        return $this->driver->getTableCreator();
    }

    public function createTable(DBTableCreator $tableCreator): mixed {
        return $this->driver->createTable($tableCreator);
    }
}