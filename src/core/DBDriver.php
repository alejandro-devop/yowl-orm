<?php

namespace Alejodevop\YowlOrm\Core;
use Alejodevop\YowlOrm\Exceptions\DBNoQueryToBeExecuted;

/**
 * Base class for all Database drivers
 * @author Alejandro Quiroz <alejandro.devop@gmail.com>
 * @version 1.0.0
 * @since 1.0.0
 */
abstract class DBDriver implements IDBDriver {
    protected $configuration = [];
    /**
     * Current query to be executed by the database driver.
     *
     * @var DBQuery|null
     */
    protected ?DBQuery $currentQuery;
    /**
     * The current driver connector
     */
    protected DBConnector $connector;

    public function __construct($config) {
        $this->configuration = $config;
    }
    /**
     * Must be implemented by every database driver to initialize data
     *
     * @return void
     */
    public abstract function init();
    /**
     * Must be implemented to create a query
     *
     * @param array $fieldInfo
     * @return DBField
     */
    public abstract function createField(array $fieldInfo): DBField;
    /**
     * Validate if there is any query set.
     *
     * @return void
     */
    protected function beforeQuery() {
        if (!isset($this->currentQuery)) {
            throw new DBNoQueryToBeExecuted("There is no query to be executed try 'DBManager::setQuery(\$query)'");
        }
    }
    /**
     * Function to clear the current query.
     *
     * @return void
     */
    protected function afterQuery() {
        $this->currentQuery = null;
    }
}