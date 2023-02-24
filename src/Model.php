<?php

namespace Alejodevop\YowlOrm;
use Alejodevop\YowlOrm\Core\DBQuery;
use Alejodevop\YowlOrm\Core\ModelBean;

class Model extends ModelBean {
    private $protected = [];

    public function all(): array|null {
        return $this->selectAll();
    }

    public function first(): Model|null {
        $this->getCriteria()->limit(1);
        return $this->selectOne();
    }

    public function save() {
        if ($this->isNew) {
            $this->insert();
        } else {
            $this->update();
        }
    }

    public static function get(): Model {
        $className = get_called_class();
        return new $className([], true, ['mountStructure' => true]);
    }

    public static function search(): Model {
        $className = get_called_class();
        return new $className([], true, ['mountStructure' => true]);
    }
	/**
	 * @param string $field
	 * @param mixed $compare
	 * @return Model
	 */
	public function equals(string $field, mixed $compare): Model {
        $this->getCriteria()->equals($field, $compare);
        return $this;
	}
	
	/**
	 *
	 * @param string $field
	 * @param mixed $compare
	 * @return Model
	 */
	public function notEquals(string $field, mixed $compare): Model {
        $this->getCriteria()->notEquals($field, $compare);
        return $this;
	}
	
	/**
	 *
	 * @param string $field
	 * @param mixed $compare
	 * @return Model
	 */
	public function greater(string $field, mixed $compare): Model {
        $this->getCriteria()->greaterThenCondition($field, $compare);
        return $this;
	}
	
	/**
	 *
	 * @param string $field
	 * @param mixed $compare
	 * @return Model
	 */
	public function less(string $field, mixed $compare): Model {
        $this->getCriteria()->lowerThenCondition($field, $compare);
        return $this;
	}
	
	/**
	 *
	 * @param string $field
	 * @param mixed $compare
	 * @return Model
	 */
	public function greaterOrEquals(string $field, mixed $compare): Model {
        $this->getCriteria()->greaterEquealsToCondition($field, $compare);
        return $this;
	}
	
	/**
	 *
	 * @param string $field
	 * @param mixed $compare
	 * @return Model
	 */
	public function lessOrEquals(string $field, mixed $compare): Model {
        $this->getCriteria()->lowerEquealsToCondition($field, $compare);
        return $this;
	}
	
	/**
	 *
	 * @param string $field
	 * @param string $content
	 * @return Model
	 */
	public function startsWith(string $field, string $content): Model {
        $this->getCriteria()->likeCondition($field, $content, DBQuery::LIKE_BEGIN);
        return $this;
	}
	
	/**
	 *
	 * @param string $field
	 * @param string $content
	 * @return Model
	 */
	public function endsWith(string $field, string $content): Model {
        $this->getCriteria()->likeCondition($field, $content, DBQuery::LIKE_END);
        return $this;
	}
	
	/**
	 *
	 * @param string $field
	 * @param string $content
	 * @return Model
	 */
	public function contains(string $field, string $content): Model {
        $this->getCriteria()->likeCondition($field, $content, DBQuery::LIKE_ANY_WHERE);
        return $this;
	}
	
	/**
	 *
	 * @param string $field
	 * @param string $content
	 * @return Model
	 */
	public function notContains(string $field, string $content): Model {
        $this->getCriteria()->notLikeCondition($field, $content, DBQuery::LIKE_ANY_WHERE);
        return $this;
	}
	
	/**
	 *
	 * @param string $field
	 * @param array $values
	 * @return Model
	 */
	public function in(string $field, array $values): Model {
        $this->getCriteria()->inCondition($field, $values);
        return $this;
	}
	
	/**
	 *
	 * @param string $field
	 * @param array $values
	 * @return Model
	 */
	public function notIn(string $field, array $values): Model {
        $this->getCriteria()->notInCondition($field, $values);
        return $this;
	}
	
	/**
	 *
	 * @param string $field
	 * @param mixed $from
	 * @param mixed $to
	 * @return Model
	 */
	public function between(string $field, mixed $from, mixed $to): Model {
        $this->getCriteria()->isBetweenCondition($field, $from, $to);
        return $this;
	}
	
	/**
	 *
	 * @param string $field
	 * @return Model
	 */
	public function isEmpty(string $field): Model {
        $this->getCriteria()->isNullCondition($field);
        return $this;
	}
	
	/**
	 *
	 * @param string $field
	 * @return Model
	 */
	public function isNotEmpty(string $field): Model {
        $this->getCriteria()->isNotNullCondition($field);
        return $this;
	}

    public function orderAsc(string $field): Model {
        return $this->order($field, true);
    }

    public function orderDesc(string $field): Model {
        return $this->order($field, false);
    }
	
	/**
	 *
	 * @param string $field
	 * @return Model
	 */
	public function group(string $field): Model {
        return $this;
	}
	
	/**
	 *
	 * @param string $field
	 * @return Model
	 */
	public function having(string $field): Model {
        return $this;
	}
	
	/**
	 *
	 * @param int $limit
	 * @param int $offset
	 * @return Model
	 */
	public function limit(int $limit = 1, int $offset = 0): Model {
        return $this;
	}
	
	/**
	 * @return Model
	 */
	public function or(): Model {
        $this->getCriteria()->or();
        return $this;
	}
	
	/**
	 * @return Model
	 */
	public function and(): Model {
        $this->getCriteria()->and();
        return $this;
	}
	
	/**
	 * @return Model
	 */
	public function getCount(): Model {
        return $this;
	}
	
	/**
	 * @return Model
	 */
	public function list(): Model {
        return $this;
	}
	
	/**
	 * @return Model
	 */
	public function byPk(mixed $id): Model|null {
        $this->getCriteria()->equals($this->pk, $id);
        return $this->first();;
	}
	
	/**
	 * @return int
	 */
	public function total(): int {
        return 0;
	}
	/**
	 * @param string $field
	 * @param mixed $asc
	 * @return Model
	 */
	public function order(string $field, $asc = true): Model {
        $this->getCriteria()->order($field, $asc);
        return $this;
	}
}