<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
// use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\VideoCall;

use App\Models\DeviceToken;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'is_chef',
        'social_token',
        'login_type',
        'interest',
        'profile_picture',
        'one_signal_player_id'
    ];

    // public function getProfilePictureAttribute($value)
    // {
    //     if (!$value) {
    //         return null;
    //     }
        
    //     return basename($value);
    // }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_chef' => 'integer',
    ];

    public function chefInfo()
    {
        return $this->hasOne(ChefInfo::class);
    }

    public function recipes()
    {
        return $this->hasManyThrough(
            Recipe::class,
            ChefInfo::class,
            'user_id', // Foreign key on the ChefInfo table...
            'chef_info_id', // Foreign key on the Recipe table...
            'id', // Local key on the User table...
            'id' // Local key on the ChefInfo table...
        )->with('chefInfo.user');
    }
    
    public function favoriteRecipes()
    {
        return $this->belongsToMany(Recipe::class, 'favorite_recipes')
                    ->with('chefInfo.user', 'chefInfo.cookingStyle', 'cookingStyle')->withTimestamps();
    }
    
    public function favoriteRecipe()
    {
        return $this->belongsToMany(Recipe::class, 'favorite_recipes')
                    ->with('chefInfo')->withTimestamps();
    }
    public function videoCalls() {
        return $this->hasOne(VideoCall::class);
    }
    public function AauthAcessToken(){
        return $this->hasMany('\App\OauthAccessToken');
    }

    public function chefPayment()
    {
        return $this->belongsTo(ChefPayment::class, 'id', 'user_id');
    }
    public function chefAccount()
    {
        return $this->belongsTo(UserAccount::class,'id','user_id');
    }

    public function deviceToken() {
        return $this->hasOne(DeviceToken::class);
    }

}
