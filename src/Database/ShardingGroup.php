<?php

namespace Crhg\LaravelSharding\Database;

use Illuminate\Support\Collection;

class ShardingGroup
{
    /**
     * @var Collection<ShardingTableConfig> $tableConfigs ;
     */
    private Collection $tableConfigs;

    public function __construct(array $config)
    {
        $this->tableConfigs = collect($config['tables'])
            ->map(fn($tableConfig, $tableName) => new ShardingTableConfig($tableName, $tableConfig));
    }

    public function getAllConnectionNames(): Collection
    {
        return $this->tableConfigs
            ->flatMap(fn(ShardingTableConfig $tc) => $tc->getAllConnectionNames());
    }

    public function getTableConfig(string $tableName): ShardingTableConfig
    {
        return $this->tableConfigs
            ->get(
                $tableName,
                fn() => throw new \RuntimeException('config not found: table=' . $tableName)
            );
    }

    public static function createByName(string $name): self
    {
        return new self(config('database.sharding_groups.' . $name));
    }
}
