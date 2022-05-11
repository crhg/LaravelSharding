<?php

namespace Crhg\LaravelSharding\Database;

use Closure;
use Illuminate\Database\Eloquent\Model;

abstract class ShardingModel extends Model
{
//    abstract public function getShardingManager(): ShardingManager;

    public function getShardConnection(): string
    {
        $connection = $this->getConnection();
        assert($connection instanceof ShardingConnection);

        return $connection->getConnectionNameByModel($this);
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
        if ($this->getConnection() instanceof ShardingConnection) {
            $this->setConnection($this->getShardConnection());
        }

        return parent::save($options);
    }
}
