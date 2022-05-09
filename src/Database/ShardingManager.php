<?php

namespace Crhg\LaravelSharding\Database;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Support\Facades\DB;

class ShardingManager
{

    public function __construct(
        private array $config
    ) {
    }

    public function getConnection(int $id): string
    {
        return (
        collect($this->config['connections'])
            ->firstOrFail(fn($c) => $c['from'] <= $id && $id <= $c['to'])
        )['name'];
    }

    public function getConnectionWithoutId(): string
    {
        return (collect($this->config['connections'])
            ->random())['name'];
    }

    /**
     * 代表コネクション
     *
     * 最初のものを代表として返す。
     * query grammerやpost processorの取得に使います
     *
     * @return string
     */
    private function getRepresentativeConnection(): string
    {
        return $this->config['connections'][0]['name'];
    }

    public function getQueryGrammar(): Grammar
    {
        $connection = DB::connection($this->getRepresentativeConnection());
        assert($connection instanceof Connection);

        return $connection->getQueryGrammar();
    }

    public function getPostProcessor(): Processor
    {
        $connection = DB::connection($this->getRepresentativeConnection());
        assert($connection instanceof Connection);

        return $connection->getPostProcessor();
    }
}
