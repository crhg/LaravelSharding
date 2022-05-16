<?php

namespace Tests\Unit;

use App\Models\Foo;
use App\Models\User;
use Crhg\LaravelSharding\Testing\RefreshShardingDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FooTest extends TestCase
{
    use RefreshShardingDatabase;

    public function testCreate(): void
    {
        /** @var Foo $foo */
        $foo = Foo::factory()->create();

        $connection = (collect(config('database.sharding_groups.a.tables.foo.connections'))
            ->firstOrFail(fn($c) => $c['from'] <= $foo->id && $foo->id <= $c['to']))['name'];

        $this->assertDatabaseHas(
            'foo',
            ['id' => $foo->id, 'x' => $foo->x],
            $connection
        );
    }

    /**
     * テーブルが空の時の全件取得
     */
    public function testGetNone(): void
    {
        $foos = Foo::query()->get();
        $this->assertEmpty($foos);
    }

    public function testGet2(): void
    {
        $foos = collect(['a1', 'a1', 'a2'])
            ->map(fn($connectionName) => Foo::factory()->connection($connectionName)->create());

        $actual = Foo::query()->get();

        $this->assertSame(
            $foos->map(fn($foo) => $foo->x)->sort()->values()->all(),
            $actual->map(fn($foo) => $foo->x)->sort()->values()->all()
        );
    }

    /**
     * idを指定したときにそのidがある筈のshardにしか問い合わせが行われないことを確認する
     */
    public function testFind(): void
    {
        $foo1 = Foo::factory()->connection('a1')->create();
        Foo::factory()->connection('a2')->create();

        DB::connection('a1')->enableQueryLog();
        DB::connection('a2')->enableQueryLog();

        Foo::find($foo1->id);

        $this->assertCount(1, DB::Connection('a1')->getQueryLog());
        $this->assertCount(0, DB::Connection('a2')->getQueryLog());
    }
}
