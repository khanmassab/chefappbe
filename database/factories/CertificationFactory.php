<?php

namespace Database\Factories;

use App\Models\ChefInfo;
use App\Models\Certification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Certification>
 */
class CertificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $faker = \Faker\Factory::create();
        return [
            'certification_proof' => $faker->imageUrl(),
            'chef_info_id' => ChefInfo::inRandomOrder()->first()->id,
        ];
    }
}
