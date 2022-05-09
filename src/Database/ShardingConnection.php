<?php

namespace Crhg\LaravelSharding\Database;

use Closure;
use Crhg\LaravelSharding\Exceptions\UnambiguousShardException;
use Illuminate\Database\Connection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ShardingConnection extends Connection
{
    /**
     * @noinspection MagicMethodsValidityInspection
     * @noinspection PhpMissingParentConstructorInspection
     */
    public function __construct(
        array $config
    ) {
        $this->config = $config;
        $this->useDefaultQueryGrammar();
        $this->useDefaultPostProcessor();
    }

    protected function getDefaultQueryGrammar()
    {
        $connection = DB::connection($this->config['connections'][0]);
        return (fn () => $this->getDefaultQueryGrammar())->call($connection);
    }

    public function query(): ShardingQueryBuilder
    {
        return new ShardingQueryBuilder(
            $this,
            $this->getQueryGrammar(),
            $this->getPostProcessor()
        );
    }

    public function getConnections(): Collection
    {
        return collect($this->config['connections'])
            ->map(fn ($name) => DB::connection($name));
    }

    public function table($table, $as = null)
    {
        throw new UnambiguousShardException();
    }

    public function raw($value)
    {
        throw new UnambiguousShardException();
    }

    public function selectOne($query, $bindings = [], $useReadPdo = true)
    {
        throw new UnambiguousShardException();
    }

    public function select($query, $bindings = [], $useReadPdo = true)
    {
        throw new UnambiguousShardException();
    }

    public function cursor($query, $bindings = [], $useReadPdo = true)
    {
        throw new UnambiguousShardException();
    }

    public function insert($query, $bindings = [])
    {
        throw new UnambiguousShardException();
    }

    public function update($query, $bindings = [])
    {
        throw new UnambiguousShardException();
    }

    public function delete($query, $bindings = [])
    {
        throw new UnambiguousShardException();
    }

    public function statement($query, $bindings = [])
    {
        throw new UnambiguousShardException();
    }

    public function affectingStatement($query, $bindings = [])
    {
        throw new UnambiguousShardException();
    }

    public function unprepared($query)
    {
        throw new UnambiguousShardException();
    }

    public function prepareBindings(array $bindings)
    {
        throw new UnambiguousShardException();
    }

    public function transaction(Closure $callback, $attempts = 1)
    {
        throw new UnambiguousShardException();
    }

    public function beginTransaction()
    {
        throw new UnambiguousShardException();
    }

    public function commit()
    {
        throw new UnambiguousShardException();
    }

    public function rollBack($toLevel = null)
    {
        throw new UnambiguousShardException();
    }

    public function transactionLevel()
    {
        throw new UnambiguousShardException();
    }

    public function pretend(Closure $callback)
    {
        throw new UnambiguousShardException();
    }

    public function getDatabaseName()
    {
        throw new UnambiguousShardException();
    }


}
