<?php

namespace Crhg\Laravel\Sharding\Database;

use Closure;
use Illuminate\Database\Eloquent\Model;

abstract class ShardingModel extends Model
{
    abstract public function getShardingManager(): ShardingManager;

    public function getShardConnection(string|Closure $default = null): string
    {
        return optional(
                $this->getAttribute($this->getKeyName()),
                fn($id) => $this->getShardingManager()->getConnection($id)
            )
            ?? value($default)
            ?? throw new \RuntimeException("shardが決定できません");
    }

    /**
     * Save the model to the database.
     *
     * saveする前にconnectionを設定します
     *
     * @param array $options
     *
     * @return bool
     */
    public function save(array $options = []): bool
    {
        $this->setConnection(
            $this->getShardConnection(fn() => $this->getShardingManager()->getConnectionWithoutId())
        );

        return parent::save($options);
    }
}
