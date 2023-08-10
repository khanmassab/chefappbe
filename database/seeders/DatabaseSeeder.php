<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use App\Models\Recipe;
use App\Models\ChefInfo;
use App\Models\Certification;
use App\Models\Notification;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {   
        // User::factory()->count(10)->create();
        // Notification::factory()->count(100)->create();
        // ChefInfo::factory()->count(200)->create();
        // Certification::factory()->count(200)->create();
        Recipe::factory()->count(10)->create();
        // \App\Models\User::factory(10)->create();
        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
