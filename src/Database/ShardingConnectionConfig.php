<?php

namespace Crhg\LaravelSharding\Database;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;

class ShardingConnectionConfig
{
    private string $name;
    private int $from;
    private int $to;

    public function __construct(array $config)
    {
        $this->name = $config['name'];
        $this->from = $config['from'];
        $this->to = $config['to'];
    }

    public function contains(int $id): bool
    {
        return $this->from >= $id && $id <= $this->to;
    }

    public function getConnectionName(): string
    {
        return $this->name;
    }

    public function getConnection(): ConnectionInterface
    {
        return DB::connection($this->getConnectionName());
    }

    public function getFrom(): int
    {
        return $this->from;
    }

    public function getTo(): int
    {
        return $this->to;
    }
}
