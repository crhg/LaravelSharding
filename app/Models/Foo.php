<?php

namespace App\Models;

use Crhg\Laravel\Sharding\Database\ShardingManager;
use Crhg\Laravel\Sharding\Database\ShardingModel;

class Foo extends ShardingModel
{
    protected $table = 'foo';
    protected $fillable = ['x'];

    public function getShardingManager(): ShardingManager
    {
        return new ShardingManager(config('database.sharding_groups.a'));
    }
}
