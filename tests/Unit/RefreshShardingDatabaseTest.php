<?php

namespace Tests\Unit;

use Crhg\LaravelSharding\Testing\RefreshShardingDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RefreshShardingDatabaseTest extends \Orchestra\Testbench\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->target = new class {
            use RefreshShardingDatabase;

            public function defaultConnectionsToTransactForTest()
            {
                return $this->defaultConnectionsToTransact();
            }

            public function connectionsToTransactForTest()
            {
                return $this->connectionsToTransact();
            }
        };
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set(
            'database.sharding_groups',
            [
                'a' => [
                    'tables' => [
                        'foo' => [
                            'key' => 'id',
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
    }

    public function testDefaultConnectionsToTransact(): void
    {
        $connections = $this->target->defaultConnectionsToTransactForTest();
        $this->assertSame([null, 'a1', 'a2'], $connections);
    }

    public function testConnectionsToTransact(): void
    {
        $connections = $this->target->connectionsToTransactForTest();
        $this->assertSame([null, 'a1', 'a2'], $connections);
    }
}
