<?php

namespace Tests\Unit;

use Crhg\LaravelSharding\Testing\RefreshShardingDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RefreshShardingDatabaseTest extends TestCase
{
    use RefreshShardingDatabase;

    /**
     * @dataProvider fooDataProvider
     */
    public function testFoo(string $dummy): void
    {
        DB::connection('a1')->table('foo')->insert([[]]);
        DB::connection('a2')->table('foo')->insert([[], []]);
        $this->assertSame(1, DB::connection('a1')->table('foo')->count());
        $this->assertSame(2, DB::connection('a2')->table('foo')->count());
    }

    public function fooDataProvider(): array
    {
        // XXX: パラメタなしでもテストは行われるが、なぜかデータセット名がうまく反映されなくなるので
        //      ダミーの文字列パラメタを一つ渡すようにしている
        return [
            '1回目' => ['dummy'],
            '2回目' => ['dummy'],
        ];
    }

    public function testDefaultConnectionsToTransact(): void
    {
        $connections = $this->defaultConnectionsToTransact();
        $this->assertSame([null, 'a1', 'a2'], $connections);
    }

    public function testConnectionsToTransact(): void
    {
        $connections = $this->connectionsToTransact();
        $this->assertSame([null, 'a1', 'a2'], $connections);
    }
}
