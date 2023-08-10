<?php

namespace Database\Seeders;

use App\Models\CookingStyle;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CookingStylesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $cookingStyles = [
            'chinese',
            'japanese',
            'korean',
            'continental',
            'pakistani',
            'baking',
            'gourmet',
        ];

        foreach ($cookingStyles as $style) {
            CookingStyle::create(['cooking_style' => $style]);
        }
    }
}
