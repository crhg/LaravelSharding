<?php

namespace App\Models;

use Crhg\LaravelSharding\Database\ShardingEloquentBuilder;
use Crhg\LaravelSharding\Database\ShardingModel;

/**
 * @property int $id
 * @property string $x
 * @mixin ShardingEloquentBuilder
 */
class Foo extends ShardingModel
{
    protected $connection = 'a';
    protected $table = 'foo';
    protected $fillable = ['x'];
}
