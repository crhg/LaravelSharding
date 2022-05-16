<?php

namespace App\Models;

use Crhg\LaravelSharding\Database\ShardingEloquentBuilder;
use Crhg\LaravelSharding\Database\ShardingModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property string $x
 * @mixin ShardingEloquentBuilder
 */
class Foo extends ShardingModel
{
    use HasFactory;

    protected $connection = 'a';
    protected $table = 'foo';
    protected $fillable = ['x'];
}
