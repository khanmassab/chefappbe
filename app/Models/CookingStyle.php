<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CookingStyle extends Model
{
    use HasFactory;

    public function chefInfo()
    {
        return $this->belongsTo(ChefInfo::class);
    }
}
