<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\BookChef;
use App\Models\User;

class BookChefFactory extends Factory
{
    protected $model = BookChef::class;

    public function definition()
    {
        $faker = \Faker\Factory::create();

        $user = optional(User::where('is_chef', '0')->inRandomOrder()->first());
        $chef = optional(User::where('is_chef', '1')->inRandomOrder()->first());

        // dd($user);
        return [
            'user_id' => $user->id,
            'chef_id' => $chef->id,
            'time_slot_id' => $faker->numberBetween(1, 2),
            'status' => $faker->randomElement(['pending', 'paid', 'cancel']),
            'reminder_sent' => $faker->boolean,
            'reminder_time' => $faker->dateTimeBetween('now', '+1 week'),
            'created_at' => $faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
