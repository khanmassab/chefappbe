<?php

namespace Database\Seeders;

use App\Models\ChefInfo;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ChefInfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        ChefInfo::factory()->count(200)->create();
    }
}
