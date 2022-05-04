<?php

namespace Crhg\Laravel\Sharding\Database;

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
}
