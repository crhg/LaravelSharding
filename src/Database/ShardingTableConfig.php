<?php

namespace Crhg\LaravelSharding\Database;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Collection;

class ShardingTableConfig
{
    private string $key;
    /**
     * @var Collection<ShardingConnectionConfig>
     */
    private Collection $connectionConfigs;

    public function __construct(private string $tableName, array $config)
    {
        $this->key = $config['key'];
        $this->connectionConfigs = collect($config['connections'])
            ->map(fn($connectionConfig) => new ShardingConnectionConfig($connectionConfig));
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getAllConnectionNames(): Collection
    {
        return $this->connectionConfigs->map(fn(ShardingConnectionConfig $c) => $c->getConnectionName());
    }

    public function getConnectionConfig(string $connectionName): ShardingConnectionConfig
    {
        return $this->connectionConfigs->first(
            fn(ShardingConnectionConfig $config) => $config->getConnectionName() === $connectionName,
            fn() => new \RuntimeException($connectionName . 'not found')
        );
    }

    public function getConnectionNameById(int $id): string
    {
        return $this->connectionConfigs
            ->firstOrFail(fn(ShardingConnectionConfig $c) => $c->contains($id))
            ->getConnectionName();
    }

    public function getConnectionNameWithoutId(): string
    {
        $connectionConfig = $this->connectionConfigs->random();
        assert($connectionConfig instanceof ShardingConnectionConfig);
        return $connectionConfig->getConnectionName();
    }

    /**
     * @param Collection<int> $ids
     *
     * @return Collection<ConnectionInterface>
     */
    public function getConnectionsByIds(Collection $ids): Collection
    {
        return $this->connectionConfigs
            ->filter(
                fn(ShardingConnectionConfig $connectionConfig) => $ids->contains(
                    fn($id) => $connectionConfig->contains($id)
                )
            )
            ->map(fn($connectionConfig) => $connectionConfig->getConnection());
    }

    /**
     * @param array $wheres
     *
     * @return Collection<ConnectionInterface>
     */
    public function getConnectionsByWheres(array $wheres): Collection
    {
        $ids = $this->getIdsFromWheres(collect($wheres));

        // idの条件がなければ絞り込めないので全接続を返却
        if ($ids->isEmpty()) {
            return $this->connectionConfigs->map(fn($cc) => $cc->getConnection());
        }

        return $this->getConnectionsByIds($ids);
    }


    /**
     * @param Collection<array> $wheres where条件のコレクション
     *
     * @return Collection<int> where条件から抽出した$this->keyカラムと比較するidのコレクション
     */
    private function getIdsFromWheres(Collection $wheres): Collection
    {
        if ($wheres->contains(fn($where) => $where['boolean'] !== 'and')) {
            return collect([]);
        }

        $column = $this->tableName . '.' . $this->key;
        return $wheres->flatMap(function ($where) use ($column) {
            if ($where['type'] === 'Basic' && $where['column'] === $column && $where['operator'] === '=') {
                return [$where['value']];
            }

            if ($where['type'] === 'In' && $where['column'] === $column) {
                return $where['values'];
            }

            return [];
        });
    }
}
