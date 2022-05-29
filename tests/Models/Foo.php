<?php

namespace Tests\Models;

use Crhg\LaravelSharding\Database\ShardingModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Tests\Factories\FooFactory;

/**
 * @property int $id
 * @property string $x
 * @mixin Builder
 */
class Foo extends ShardingModel
{
    use HasFactory;

    protected $connection = 'a';
    protected $table = 'foo';
    protected $fillable = ['x'];

    protected static function newFactory()
    {
        return FooFactory::new();
    }
}
