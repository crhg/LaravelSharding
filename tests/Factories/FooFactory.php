<?php

namespace Tests\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Tests\Models\Foo;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Tests\Models\Foo>
 */
class FooFactory extends Factory
{
    protected $model = Foo::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'x' => $this->faker->name,
        ];
    }
}
