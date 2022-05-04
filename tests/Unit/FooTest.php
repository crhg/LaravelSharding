<?php

namespace Tests\Unit;

use App\Models\Foo;
use Crhg\Laravel\Sharding\Testing\RefreshShardingDatabase;
use Tests\TestCase;

class FooTest extends TestCase
{
    use RefreshShardingDatabase;

    public function testCreate(): void
    {
        /** @var Foo $foo */
        $foo = Foo::create(['x' => 'foo']);

        $shardingManager = $foo->getShardingManager();
        $connection = $shardingManager->getConnection($foo->id);
        $this->assertDatabaseHas(
            'foo',
            ['id' => $foo->id, 'x' => 'foo'],
            $connection
        );
    }
}
