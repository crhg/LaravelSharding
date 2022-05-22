<?php

namespace Tests\Feature;

use Crhg\LaravelSharding\Providers\ShardingServiceProvider;
use Illuminate\Support\Facades\DB;
use Orchestra\Testbench\TestCase;
use PDO;
use Tests\Concerns\WithLoadShardingMigrationsFrom;
use Tests\Models\Foo;

class FooTest extends TestCase
{
    use WithLoadShardingMigrationsFrom;

    protected function getPackageProviders($app): array
    {
        return [
            ShardingServiceProvider::class,
        ];
    }

    protected function defineDatabaseMigrations(): void
    {
        foreach (['a1', 'a2'] as $connection) {
            $this->loadShardingMigrationsFrom($connection, __DIR__ . '/../migrations');
        }
    }


    protected function defineEnvironment($app)
    {
        $app['config']->set(
            'database.sharding_groups',
            [
                'a' => [
                    'tables' => [
                        'foo' => [
                            'key'         => 'id',
                            'connections' => [
                                [
                                    'name' => 'a1',
                                    'from' => 1,
                                    'to'   => 10000,
                                ],
                                [
                                    'name' => 'a2',
                                    'from' => 10001,
                                    'to'   => 20000,
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $app['config']->set(
            'database.connections.a',
            [
                'driver'         => 'sharding',
                'sharding_group' => 'a',
                'connections'    => ['a1', 'a2'],
            ]
        );

        $app['config']->set(
            'database.connections.a1',
            [
                'driver'         => 'mysql',
                'url'            => env('DATABASE_URL'),
                'host'           => env('DB_HOST', '127.0.0.1'),
                'port'           => env('DB_PORT', '3306'),
                'database'       => env('DB_DATABASE_A1', 'forge'),
                'username'       => env('DB_USERNAME', 'forge'),
                'password'       => env('DB_PASSWORD', ''),
                'unix_socket'    => env('DB_SOCKET', ''),
                'charset'        => 'utf8mb4',
                'collation'      => 'utf8mb4_unicode_ci',
                'prefix'         => '',
                'prefix_indexes' => true,
                'strict'         => true,
                'engine'         => null,
                'options'        =>
                    extension_loaded('pdo_mysql') ?
                        array_filter(
                            [
                                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
                            ]
                        ) :
                        [],
            ]
        );

        $app['config']->set(
            'database.connections.a2',
            [
                'driver'         => 'mysql',
                'url'            => env('DATABASE_URL'),
                'host'           => env('DB_HOST', '127.0.0.1'),
                'port'           => env('DB_PORT', '3306'),
                'database'       => env('DB_DATABASE_A2', 'forge'),
                'username'       => env('DB_USERNAME', 'forge'),
                'password'       => env('DB_PASSWORD', ''),
                'unix_socket'    => env('DB_SOCKET', ''),
                'charset'        => 'utf8mb4',
                'collation'      => 'utf8mb4_unicode_ci',
                'prefix'         => '',
                'prefix_indexes' => true,
                'strict'         => true,
                'engine'         => null,
                'options'        =>
                    extension_loaded('pdo_mysql') ?
                        array_filter(
                            [
                                PDO::MYSQL_ATTR_SSL_CA => env(
                                    'MYSQL_ATTR_SSL_CA'
                                ),
                            ]
                        ) :
                        [],
            ]
        );
    }

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

    public function testUpdate(): void
    {
        /** @var Foo $foo */
        $foo = Foo::create(['x' => 'foo']);

        $foo->x = 'bar';
        $foo->save();

        $connection = (collect(config('database.sharding_groups.a.tables.foo.connections'))
            ->firstOrFail(fn($c) => $c['from'] <= $foo->id && $foo->id <= $c['to']))['name'];

        $this->assertDatabaseHas(
            'foo',
            ['id' => $foo->id, 'x' => 'bar'],
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

        $this->assertEqualsCanonicalizing(
            $foos->map(fn($foo) => $foo->x),
            $actual->map(fn($foo) => $foo->x)
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
