<?php

namespace Crhg\LaravelSharding\Database;

use Crhg\LaravelSharding\Exceptions\UnambiguousShardException;
use Illuminate\Database\Query\Builder;

class ShardingQueryBuilder extends Builder
{
    protected function runSelect(): array
    {
        if ($this->connection instanceof ShardingConnection) {
            if (!empty($this->orders)) {
                throw new UnambiguousShardException();
            }

            $sql = $this->toSql();
            $bindings = $this->getBindings();
            return $this->connection
                ->getConnectionsByWheres($this->wheres, $this->from)
                ->flatMap(fn($c) => $c->select($sql, $bindings, !$this->useWritePdo))
                ->all();
        }

        return parent::runSelect();
    }
}
