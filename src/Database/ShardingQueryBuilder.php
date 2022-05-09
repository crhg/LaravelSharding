<?php

namespace Crhg\LaravelSharding\Database;

use Illuminate\Database\Query\Builder;

class ShardingQueryBuilder extends Builder
{
//    /**
//     * @noinspection MagicMethodsValidityInspection
//     * @noinspection PhpMissingParentConstructorInspection
//     */
//    public function __construct(
//        Grammar $grammar,
//        Processor $processor
//    ) {
//    }

    protected function runSelect()
    {
        if ($this->connection instanceof ShardingConnection) {
            return $this->connection->getConnections()
                ->flatMap(
                    fn($connection) => $connection->select($this->toSql(), $this->getBindings(), !$this->useWritePdo)
                )
                ->all();
        }

        return parent::runSelect();
    }
}
