<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $shardingGroup = new \Crhg\LaravelSharding\Database\ShardingGroup(config('database.sharding_groups.a'));
        $tableConfig = $shardingGroup->getTableConfig('foo');
        $connectionConfig = $tableConfig->getConnectionConfig(config('database.default'));

        Schema::create('foo', function (Blueprint $table) use ($connectionConfig) {
            $table->id()->from($connectionConfig->getFrom());
            $table->string('x', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('foo');
    }
};
