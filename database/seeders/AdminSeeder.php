<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'first_name' =>'Admin',
            'last_name' => 'chefApp',
            'email' => 'admin@gmail.com',
            'is_chef' => '2',
            'password' => Hash::make('12345678'),
        ]);
    }
}
