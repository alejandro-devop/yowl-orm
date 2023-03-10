<?php

namespace Alejodevop\YowlOrm\Core;
use Alejodevop\YowlOrm\DBManager;
use Alejodevop\YowlOrm\Model;
use Ramsey\Uuid\Uuid;

abstract class ModelBean implements ISelectable {
    protected bool $isNew = true;
    protected bool $autoGeneratedPk = true;
    protected bool $uuid = false;
    protected mixed $generatedId;
    protected bool $usingCriteria = false;

    protected string $table;
    protected mixed $pk;

    protected mixed $pkValue;

    protected $propertiesInfo = [];
    protected $aliasMap = [];
    protected $attributes = [];
    protected $relations = [];
    protected $fieldsColumnsMap = [];
    protected DBQuery|null $criteria = null;
    protected int $aliasCount = 1;

    protected $counted = 0;

    public function __construct(array $fielsFromDB = [], bool $isNew = true, array $options = []) {
        [
            'mountStructure' => $mountStructure,
            'relations' => $relations,
            'aliasMap' => $aliasMap
        ] = array_merge(['relations' => [], 'mountStructure' => false, 'aliasMap' => []], $options);
        $this->relations = $relations;
        $this->init($mountStructure);
        $this->aliasMap = ['t' => ['table' => $this->table]];
        $this->populateAttributesFromDB($fielsFromDB, $aliasMap);
        $this->isNew = $isNew;
        $this->initCriteria();
    }

    protected function dashesToCamelCase($string, $capitalizeFirst = false) {
        $str = str_replace(' ', '', 
            ucwords(
                str_replace('_', ' ', $string)
            )
        );
        return $capitalizeFirst? ucfirst($str) : lcfirst($str);
    }

    protected function init($mountStructure) {
        $this->buildAttributes();
        if ($mountStructure) {
            $this->relations = $this->relations();
        } else {
            $this->relations = $this->getEmptyRels($this->relations());
        }
    }

    public function hasOne(mixed $relatedModel, string $field, $options = []) {
        [
            'recursive' => $recursive,
        ] = array_merge(['recursive' => false], $options);

        $relModel = new $relatedModel([], true, []);
        if (!$relModel instanceof Model) {
            throw new \Exception("Invalid related model $relatedModel");
        }
        $pk = $relModel->getPK();
        $table = $relModel->getTable();
        $relFields = array_keys($relModel->getFieldsColumnsMap());

        $rels = [];

        if ($recursive) {
            $rels = $relModel->relations();
        }
        return [
            'hasOne' => true,
            'table' => $table,
            'goesTo' => $relModel->getTable(),
            'goesFrom' => $this->getTable(),
            'field' => $field,
            'table_pk' => $pk,
            'rel_fields' => $relFields,
            'model' => $relatedModel,
            'rels' => $rels,
        ];
    }
    public static function hasMany(mixed $relatedModel) {
        return [];
    }

    public function relations() {
        return [];
    }

    public function getCriteria(): DBQuery {
        $this->usingCriteria = true;
        if (is_null($this->criteria)) {
            $this->initCriteria();
        } 
        return $this->criteria;
    }

    protected function buildAttributes() {
        $attrs = DBManager::getInstance()->getFromSchema($this->table);
        foreach($attrs as $key=>$attr) {
            [
                'isPrimary' => $isPrimary,
                'default' => $default
            ] = $attr;
            if ($isPrimary === true) {
                $this->pk = $key;
            }
            $attributeName = $this->dashesToCamelCase($key);
            $this->propertiesInfo[$attributeName] = $attr;
            $this->attributes[$attributeName] = $default === ''? null : $default;
            $this->fieldsColumnsMap[$key] = $attributeName;
        }
    }

    protected function initCriteria() {
        $this->criteria = DBManager::getInstance()->createQuery();
        $this->criteria->setTable($this->table);
        $this->criteria->setColumnNames(array_keys($this->fieldsColumnsMap));
    }

    public function __set(string $property, mixed $value) {
        if ($property === 'attributes') {
            $this->setAttributes($value);
        } else if (key_exists($property, $this->attributes)) {
            $this->attributes[$property] = $value;
        }
    }

    public function __get(string $property) {
        if (key_exists($property, $this->attributes)) {
            return $this->attributes[$property];
        } else if (key_exists($property, $this->relations)) {
            return $this->relations[$property];
        }
        return null;
    }

    protected function setAttributes(array $attributes): ModelBean {
        foreach($attributes as $name => $value) {
            if (key_exists($name, $this->attributes)) {
                $this->attributes[$name] = $value;
            }
        }
        return $this;
    }

    public function relExists($rel) {
        return key_exists($rel, $this->relations);
    }

    public function setRel($rel, ModelBean $value) {
        $this->relations[$rel] = $value;
    }

    public function setRelations(array $rels) {
        $this->relations = $rels;
    }

    protected function populateAttributesFromDB(array $attributes, array $aliasMap = []) {
        $relsToAssign = [];
        foreach($attributes as $name => $value) {
            # Checking the relations
            if (!isset($this->fieldsColumnsMap[$name])) {
                if (strpos($name, "__") !== false) {
                    [$alias, $field] = explode('__', $name);
                    $table = $this->dashesToCamelCase($aliasMap[$alias]['table']);
                    $relsToAssign[$table]['model'] = $aliasMap[$alias]['model'];
                    $relsToAssign[$table]['fields'][$field] = $value;
                }
                continue;
            };
            $field = $this->fieldsColumnsMap[$name];
            if (key_exists($field, $this->attributes)) {
                $this->attributes[$field] = $value;
                if ($field === $this->pk) {
                    $this->pkValue = $value;
                }
            }
        }

        if (count($relsToAssign) > 0) {
            $previousRel = null;
            foreach($relsToAssign as $key=>$rel) {
                ['model' => $modelName, 'fields' => $fields] = $rel;
                if ($this->relExists($key)) {
                    $model = new $modelName($fields, false, []);
                    $this->relations[$key] = $model;
                    $previousRel = $key;
                } else if (
                    !is_null($previousRel) 
                    && isset($this->relations[$previousRel]) 
                    && $this->relations[$previousRel]->relExists($key)
                    ){
                    $model = new $modelName($fields, false, []);
                    $this->relations[$previousRel]->setRel($key, $model);
                }
            }
        }
    }

    public function getPK(): string {
        return $this->pk;
    }

    public function getTable(): string {
        return $this->table;
    }

    public function getPkVal() {
        return key_exists($this->pk, $this->attributes)? $this->attributes[$this->pk] : null;
    }

    // To Abstract

    public function insert(): bool {
        $inserted = $this->insertRecord();
        if ($inserted) {
            if ($this->uuid && is_null($this->attributes[$this->pk])) {
                $this->attributes[$this->pk] = $this->generatedId;
            } else if ($this->autoGeneratedPk) {
                $this->attributes[$this->pk] = DBManager::getInstance()->lastInsertId();
            }
            $this->isNew = false;
        }
        return $inserted;
    }

    public function insertRecord(): bool {
        $attributes = $this->attributes;
        $dbMap = $this->fieldsColumnsMap;
        if ($this->uuid && is_null($this->attributes[$this->pk])) {
            $uuid = Uuid::uuid4();            
            $this->generatedId = $uuid->toString();
            $attributes[$this->pk] = $this->generatedId;
        } else if ($this->autoGeneratedPk === true && !$this->uuid) {
            unset($attributes[$this->pk], $dbMap[$this->pk]);
        }
        $cols = array_keys($dbMap);
        $vals = [];
        foreach($cols as $col) {
            $field = $dbMap[$col];
            $value = $attributes[$field];
            if (is_null($value)) $value = '';
            $vals[] = $value;
        }
        $query = DBManager::getInstance()->createQuery();
        $query->setTable($this->table)
            ->setColumnNames($cols)
            ->setValues($vals);
        DBManager::getInstance()->setQuery($query);
        return DBManager::getInstance()->insert();
    }

    	/**
	 * @return bool
	 */
	public function delete(): bool {
        $query = DBManager::getInstance()->createQuery();
        $query->setTable($this->table)
            ->setColumnNames([$this->pk])
            ->setValues([$this->pkValue]);
        DBManager::getInstance()->setQuery($query);
        return DBManager::getInstance()->delete();
	}

    /**
	 * @return bool
	 */
	public function update(): bool {
        $attributes = $this->attributes;
        $dbMap = $this->fieldsColumnsMap;
        unset($attributes[$this->pk], $dbMap[$this->pk]);
        $cols = array_keys($dbMap);
        $vals = [];
        foreach($cols as $col) {
            $field = $dbMap[$col];
            $value = $this->attributes[$field];
            if (is_null($value)) $value = '';
            $vals[] = $value;
        }
        $query = DBManager::getInstance()
            ->createQuery()
            ->setTable($this->table)
            ->setColumnNames($cols)
            ->setValues($vals)
            ->equals($this->pk, $this->pkValue);

        DBManager::getInstance()
            ->setQuery($query);

        return DBManager::getInstance()->update();
	}

    public function getEmptyRels(array $rels) {
        $relKeys = array_keys($rels);
        return array_combine($relKeys, array_fill(0, count($relKeys), null));
    }

    public function selectAll(): array {
        
        if (!$this->usingCriteria) {
            $query = DBManager::getInstance()->createQuery();
            
        } else {
            $query = $this->criteria;
        }
        $cols = array_keys($this->fieldsColumnsMap);
        $query->setTable($this->table)->setColumnNames($cols);
        $this->resolveRelations($query);
        $query->setAliasesMap($this->aliasMap);
        DBManager::getInstance()->setQuery($query);
        $response = DBManager::getInstance()->select();
        $invokedClass = get_called_class();
        $instances = array_map(fn($data) => new $invokedClass($data, false, [
            'aliasMap' => $this->aliasMap,
            'relations' => $this->getEmptyRels($this->relations)
        ]), $response);

        return $instances;
    }

    protected function resolveRelations(DBQuery &$query) {
        $oneToManies = array_filter(
            $this->relations, 
            fn ($item) => isset($item['hasOne']) && $item['hasOne'] === true
        );

        $relsToProcess = [];
        foreach($oneToManies as $singleRel) {
            if (isset($singleRel['rels']) && count($singleRel['rels']) > 0) {
                $relsToProcess = array_merge($relsToProcess, $singleRel['rels']);
                unset($singleRel['rels']);
            }
            $relsToProcess[] = $singleRel;
        }
        $relsToProcess = array_reverse($relsToProcess);

        if (count($relsToProcess) > 0) {
            foreach($relsToProcess as $relInfo) {
                $alias = "t" . $this->aliasCount;
                $this->aliasMap[$alias] = ['table' => $relInfo['table'], 'model' => $relInfo['model']];
                $query->addJoin($relInfo, 'left', $alias);
                $this->aliasCount ++;
            }
        }
    }

    public function getAliasesMap() {
        return $this->aliasMap;
    }

    /**
	 * @return Model|null
	 */
	public function selectOne(): Model|null {
        $response = $this->selectAll();
        return isset($response[0])? $response[0] : null;
	}

	/**
	 * @return int
	 */
	public function selectCount(): int {
        return null;
	}
	
	/**
	 *
	 * @param string $key
	 * @param string $value
	 * @return array
	 */
	public function getList(string $key, string $value): array {
        return null;
	}

    public function __debugInfo() {
        return [
            'isNew' => $this->isNew,
            'attributes' => $this->attributes,
            'relations' => $this->relations,
        ];
    }

    public function getFieldsColumnsMap() {
        return $this->fieldsColumnsMap;
    }
}