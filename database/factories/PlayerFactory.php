<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Player>
 */
class PlayerFactory extends Factory
{
	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 */
	public function definition(): array
	{
		$gender = fake()->randomElement(['male', 'female']);

		return [
			'name' => fake()->name($gender),
			'gender' => $gender,
            'skill' => fake()->numberBetween(1, 100),
		];
	}
}
