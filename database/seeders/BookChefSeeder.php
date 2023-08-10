<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BookChef;

class BookChefSeeder extends Seeder
{
    public function run()
    {
        BookChef::factory()->count(20)->create();
    }
}
