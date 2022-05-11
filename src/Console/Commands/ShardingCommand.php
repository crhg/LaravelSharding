<?php

namespace Crhg\LaravelSharding\Console\Commands;

use Crhg\LaravelSharding\Database\ShardingGroup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ShardingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sharding {cmd} {args?*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sharding';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        foreach (config('database.sharding_groups') as $group_name => $g) {
            $shardingGroup = new ShardingGroup($g);
            $command = $this->makeCommand($group_name);

            foreach ($shardingGroup->getAllConnectionNames() as $name) {
                $this->line('group=' . $group_name . ', connection=' . $name);
                $this->withConfigs(
                    [
                        'database.default' => $name,
//                        'database.sharding.connection' => $c,
                    ],
                    fn() => Artisan::call($command, outputBuffer: $this->getOutput())
                );
            }
        }
    }

    private function makeCommand(string $groupName): string
    {
        $cmd = $this->argument('cmd');
        $args = $this->argument('args') ?? [];
        $arguments = collect($args)
            ->map(fn($s) => escapeshellarg($s))
            ->all();

        return implode(
            ' ',
            [
                $cmd,
                '--path=' . database_path('migrations_' . $groupName),
                '--realpath',
                ...$arguments,
            ]
        );
    }

    private function withConfigs(array $configs, callable $action): void
    {
        $keys = array_keys($configs);
        $oldValues = config()->getMany($keys);
        config($configs);
        try {
            $action();
        } finally {
            config(array_combine($keys, $oldValues));
        }
    }

    private function withConfig(string $key, mixed $value, callable $action): void
    {
        $oldValue = config($key);
        config([$key => $value]);
        try {
            $action();
        } finally {
            config([$key => $oldValue]);
        }
    }
}
