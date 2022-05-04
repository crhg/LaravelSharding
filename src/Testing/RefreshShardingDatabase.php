<?php

namespace Crhg\Laravel\Sharding\Testing;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;

/**
 * @property Application $app
 */
trait RefreshShardingDatabase
{
    use RefreshDatabase;

    /**
     * Refresh a conventional test database.
     *
     * @return void
     */
    protected function refreshTestDatabase(): void
    {
        if (!RefreshDatabaseState::$migrated) {
            $this->artisan('sharding', ['cmd' => 'migrate:fresh']);
            $this->artisan('migrate:fresh', $this->migrateFreshUsing());

            $this->app[Kernel::class]->setArtisan(null);

            RefreshDatabaseState::$migrated = true;
        }

        $this->beginDatabaseTransaction();
    }

    /**
     * The database connections that should have transactions.
     *
     * @return array
     */
    protected function connectionsToTransact(): array
    {
        return property_exists($this, 'connectionsToTransact')
            ? $this->connectionsToTransact : $this->defaultConnectionsToTransact();
    }

    private function defaultConnectionsToTransact(): array
    {
        return
            [
                null,
                ...collect(config('database.sharding_groups'))
                    ->flatMap(fn($c) => collect($c['connections'])
                        ->map(fn($c) => $c['name']))
                    ->all(),
            ];
    }
}
