<?php

namespace Database\Seeders;

use App\Models\Certification;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ChefInfoCertificationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Certification::factory()->count(200)->create();
    }
}
