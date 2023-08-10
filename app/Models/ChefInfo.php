<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChefInfo extends Model
{
    use HasFactory;
    protected $table = 'chef_infos';
    protected $fillable = ['user_id', 'nationality', 'cooking_style_id', 'about',
    'city',
    'number_of_years_experience'
    ];

    // public function getCookingStyleAttribute()
    // {
    //     $cooking_style =  CookingStyle::find($this->cooking_style_id);
    //     return $cooking_style;
    // }

    protected $hidden = ['id'];

 

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function chefAccount()
    {
        return $this->hasOne(UserAccount::class, 'id', 'user_id');
    }

    public function certifications()
    {
        return $this->hasMany(Certification::class, 'chef_info_id','user_id');
    }
    
    public function recipes()
    {
        return $this->hasMany(Recipe::class, 'chef_info_id', 'user_id');
    } 

    public function ChefRecipes()
    {
        return $this->hasMany(Recipe::class, 'chef_info_id', 'user_id');
    }

    public function cookingStyle()
    {
        return $this->hasOne(CookingStyle::class, 'id', 'cooking_style_id');
    }
    public function timeSlots()
    {
        return $this->hasMany(TimeSlots::class,'chef_info_id', 'user_id');
    }
    
}
