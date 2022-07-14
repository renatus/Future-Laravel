<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notebook>
 */
class NotebookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => Str::orderedUuid()->toString(),
            'creator_uuid' => User::factory(),
            'name' => fake()->name(),
            'company' => fake()->company(),
            'phone' => fake()->e164PhoneNumber(),
            'email' => fake()->unique()->freeEmail,
            'birthday' => fake()->date($format = 'Y-m-d', $max = 'now'),
            'picture' => null,
        ];
    }
}
