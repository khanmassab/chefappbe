<?php

namespace Database\Factories;
use App\Models\User;
use App\Models\Recipe;
use Faker\Factory as FakerFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\Factory;
use GuzzleHttp\Client;
use Samwilson\PhpFlickr\PhpFlickr;


class RecipeFactory extends Factory
{
    protected $model = Recipe::class;
    
    public function definition()
    {
        $flickr = new PhpFlickr('34ee65680fcecbb92d1c4e6d6020a03f', '16dc83a62c52296d');

        $results = $flickr->photos_search([
            'tags' => 'recipe',
            'tag_mode' => 'all',
            'safe_search' => 1,
            'per_page' => 100,
        ]);
        $photos = $results['photo'];
        $photo = $photos[array_rand($photos)];

        $imageUrl = $flickr->buildPhotoURL($photo, 'large');


        Storage::makeDirectory('public/recipe_videos');

        $faker = \Faker\Factory::create();

        $user = User::inRandomOrder()->first();

        $user = User::whereHas('chefInfo')->inRandomOrder()->first();


        $recipeData = [
            'recipe_name' => $faker->words(3, true),
            'recipe_requirements' => $faker->paragraphs(3, true),
            'chef_info_id' => $user->chefInfo->id,
            'image' => $imageUrl,
        ];

        return $recipeData;
    }
}