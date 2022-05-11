<?php

namespace Tests\Unit;

use App\Models\Foo;
use Crhg\LaravelSharding\Testing\RefreshShardingDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FooTest extends TestCase
{
    use RefreshShardingDatabase;

    public function testCreate(): void
    {
        /** @var Foo $foo */
        $foo = Foo::create(['x' => 'foo']);

        $connection = (collect(config('database.sharding_groups.a.tables.foo.connections'))
            ->firstOrFail(fn($c) => $c['from'] <= $foo->id && $foo->id <= $c['to']))['name'];

        $this->assertDatabaseHas(
            'foo',
            ['id' => $foo->id, 'x' => 'foo'],
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
        Foo::create(
            [
                'id' => $this->getId('a1', 0),
                'x'  => 'a',
            ]
        );
        Foo::create(
            [
                'id' => $this->getId('a1', 1),
                'x'  => 'b',
            ]
        );
        Foo::create(
            [
                'id' => $this->getId('a2', 0),
                'x'  => 'c',
            ]
        );

        $foos = Foo::query()->get();

        $this->assertSame(
            ['a', 'b', 'c'],
            $foos->map(fn($foo) => $foo->x)->sort()->values()->all()
        );
    }

    /**
     * idを指定したときにそのidがある筈のshardにしか問い合わせが行われないことを確認する
     */
    public function testFind(): void
    {
        $foo1 = Foo::create(
            [
                'id' => $this->getId('a1', 1),
                'x'  => 'a',
            ]
        );
        $foo2 = Foo::create(
            [
                'id' => $this->getId('a2', 1),
                'x'  => 'b',
            ]
        );

        DB::connection('a1')->enableQueryLog();
        DB::connection('a2')->enableQueryLog();

        Foo::find($foo1->id);

        $this->assertCount(1, DB::Connection('a1')->getQueryLog());
        $this->assertCount(0, DB::Connection('a2')->getQueryLog());
    }

    private function getId(string $name, int $index): int
    {
        return $this->getFrom($name) + $index;
    }

    public function getFrom(string $name): int
    {
        return $this->getConnectionConfig($name)['from'];
    }

    public function getConnectionConfig(string $name): array
    {
        return (collect(config('database.sharding_groups.a.tables.foo.connections'))
            ->firstOrFail(fn($c) => $c['name'] === $name));
    }
}
