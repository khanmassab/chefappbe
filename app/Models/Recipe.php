<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    use HasFactory;
    protected $fillable = [
        'recipe_name',
        'recipe_requirements',
        'recipe_video',
        'chef_info_id',
        'cooking_style_id',
        'is_draft',
        'image',
    ];

    public function chefInfo()
    {
        return $this->belongsTo(ChefInfo::class, 'chef_info_id', 'user_id');
    }

    public function chef()
    {
        return $this->belongsTo(User::class, 'chef_info_id', 'id');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function favourite()
    {
        auth()->user()->favoriteRecipes()->attach($this->id);
    }
    
    public function defavourite()
    {
        auth()->user()->favoriteRecipes()->detach($this->id);
    }

    public function cookingStyle()
    {
        return $this->hasOne(CookingStyle::class, 'id', 'cooking_style_id');
    }

    public function timeSlots(){
        return $this->hasMany(TimeSlots::class);
    }

    public function is_favorited(){
       return  $this->belongsTo(FavoriteRecipe::class,'id','recipe_id')->where('user_id',auth('api')->id());
    }

    public function chefAccount()
    {
        return $this->belongsTo(UserAccount::class,'chef_info_id','user_id');
    }

}
