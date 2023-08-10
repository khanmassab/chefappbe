<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAccount extends Model
{
    use HasFactory;
    protected $fillable=[
        'user_id',
        'account_no',
        'acc_token',
        'status',
        'person_id',
    ];   

    public function recipe()
    {
        return $this->hasMany(Recipe::class, 'id', 'chef_info_id');
    }
}
