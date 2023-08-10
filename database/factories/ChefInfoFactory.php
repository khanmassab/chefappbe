<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\ChefInfo;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChefInfoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ChefInfo::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $faker = \Faker\Factory::create();

        return [
            'user_id' => User::where('is_chef', 1)->inRandomOrder()->first()->id,
            'nationality' => $faker->country,
            'cooking_style_id' => $faker->numberBetween(1, 7),
            'about' => $faker->paragraph,
        ];
    }
}
