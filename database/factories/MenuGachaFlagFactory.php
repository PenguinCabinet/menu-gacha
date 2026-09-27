<?php

namespace Database\Factories;

use App\Models\MenuGachaFlag;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MenuGachaFlag>
 */
class MenuGachaFlagFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'menu_gacha_id' => fn (): int => User::factory()->create()->menuGachas()->create(['name' => 'サンプルメニュー'])->getKey(),
            'name' => fake()->unique()->word(),
        ];
    }
}
