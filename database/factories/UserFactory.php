<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $faker = \Faker\Factory::create();

        // $profilePicture = UploadedFile::fake()->image('profile_picture.jpg', 500, 500);
        // $profilePicturePath = $profilePicture->store('profile_pictures');


        return [
            'first_name' => $faker->name,
            'last_name' => $faker->name,
            'email' => $faker->unique()->safeEmail,
            'email_verified_at' => now(),
            'password' => bcrypt('password'), // Default password is 'password'
            'remember_token' => Str::random(10),
            'is_chef' => $faker->randomElement([1 , 2]), 
            // 'profile_picture' => $profilePicturePath
        ];
    }

    

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
