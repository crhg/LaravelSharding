<?php

namespace Crhg\Laravel\Sharding\Providers;

use Crhg\Laravel\Sharding\Console\Commands\ShardingCommand;
use Illuminate\Support\ServiceProvider;

class ShardingProvider extends ServiceProvider
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
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands(
                [
                    ShardingCommand::class,
                ]
            );
        }
    }
}
