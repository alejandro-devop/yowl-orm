<?php

namespace Alejodevop\YowlOrm\Core;
use Alejodevop\YowlOrm\Model;

interface ISelectable {
    public function selectCount() : int;
    public function selectAll():array|null;

    public function getList(string $key, string $value): array;
    public function selectOne(): Model|null;
    public function insert(): bool;
    public function insertRecord(): bool;
    public function update(): bool;
    public function delete(): bool;
    public function equals(string $field, mixed $compare): Model;
    public function notEquals(string $field, mixed $compare): Model;
    public function greater(string $field, mixed $compare): Model;
    public function less(string $field, mixed $compare): Model;
    public function greaterOrEquals(string $field, mixed $compare): Model;
    public function lessOrEquals(string $field, mixed $compare): Model;
    public function startsWith(string $field, string $content): Model;
    public function endsWith(string $field, string $content): Model;
    public function contains(string $field, string $content): Model;
    public function notContains(string $field, string $content): Model;
    public function in(string $field, array $values): Model;
    public function notIn(string $field, array $values): Model;
    public function between(string $field, mixed $from, mixed $to): Model;
    public function isEmpty(string $field): Model;
    public function isNotEmpty(string $field): Model;
    public function order(string $field, $asc = true): Model;
    public function group(string $field): Model;
    public function having(string $field): Model;
    public function limit(int $limit = 1, int $offset = 0): Model;
    public function or(): Model;
    public function and(): Model;
    public function first(): Model|null;
    public function getCount(): Model;
    public function list(): Model;
    public function byPk(mixed $id): Model|null;
    public function total(): int;
}