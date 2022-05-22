<?php

namespace Tests\Concerns;

use InvalidArgumentException;
use Orchestra\Testbench\Database\MigrateProcessor;

trait WithLoadShardingMigrationsFrom
{
    protected function loadShardingMigrationsFrom(string $connection, $paths): void
    {
        $options = \is_array($paths) ? $paths : ['--path' => $paths];

        if (isset($options['--realpath']) && ! \is_bool($options['--realpath'])) {
            throw new InvalidArgumentException('Expect --realpath to be a boolean.');
        }

        $options['--realpath'] = true;

        $this->app['config']->set('database.default', $connection);
        $migrator = new MigrateProcessor($this, $options);
        $migrator->up();

        $this->resetApplicationArtisanCommands($this->app);

        $this->beforeApplicationDestroyed(
            function () use ($connection, $migrator) {
                $this->app['config']->set('database.default', $connection);
                $migrator->rollback();
            }
        );
    }
}
