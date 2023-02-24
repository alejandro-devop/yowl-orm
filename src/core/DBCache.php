<?php

namespace Alejodevop\YowlOrm\Core;
use Alejodevop\YowlOrm\DBManager;

class DBCache {
    private $cacheDir = '';
    private $doesCacheExists = false;
    private $tableNames = [];
    private $tablesInfo = [];

    function __construct(string $cacheDir) {
        if (!defined('DS')) { 
            define('DS', DIRECTORY_SEPARATOR);
        }
        $this->cacheDir = $cacheDir;
    }

    public function checkGeneratedCache() {
        $path = "{$this->cacheDir}" . DS . 'database.php';
        return file_exists($path);
    }

    public function generateCache() {
        $this->getTablesInfo();
        $this->getTablesDescription();        
        $cachedInfo = $this->varexport($this->tablesInfo, true);
        $cacheFile = $this->cacheDir . DS . "database.php";
        $file = fopen($cacheFile, 'w');
        fwrite($file, "<?php\n");
        fwrite($file, "return $cachedInfo;");
        fclose($file);
        echo "Cache generated...";
        exit();
    }

    function varexport($expression, $return=true) {
        $export = var_export($expression, true);
        $patterns = [
            "/array \(/" => '[',
            "/^([ ]*)\)(,?)$/m" => '$1]$2',
            "/=>[ ]?\n[ ]+\[/" => '=> [',
            "/([ ]*)(\'[^\']+\') => ([\[\'])/" => '$1$2 => $3',
        ];
        $export = preg_replace(array_keys($patterns), array_values($patterns), $export);
        if ((bool)$return) return $export; else echo $export;
    }

    private function getTablesDescription() {
        foreach($this->tableNames as $tableName) {
            $tableInfo = DBManager::getInstance()->describe($tableName, true);
            $this->tablesInfo[$tableName] = [];
            foreach ($tableInfo as $value) {
                $key = $value['name'];
                unset($value['name']);
                $this->tablesInfo[$tableName][$key] = $value;
            }
        }
    }

    private function getTablesInfo() {
        $tables = DBManager::getInstance()->getTables();
        foreach($tables as $table) {
            $this->tableNames[] = $table['table_name'];
        }
    }


}