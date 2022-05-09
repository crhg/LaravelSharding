<?php

namespace Crhg\LaravelSharding\Providers;

use Crhg\LaravelSharding\Console\Commands\ShardingCommand;
use Crhg\LaravelSharding\Database\ShardingConnection;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\ServiceProvider;

class ShardingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(DatabaseManager $databaseManager): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands(
                [
                    ShardingCommand::class,
                ]
            );
        }

        $databaseManager->extend('sharding',
            function (array $config, string $name): ShardingConnection {
                return new ShardingConnection($config);
            }
        );
    }
}
