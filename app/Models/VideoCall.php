<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoCall extends Model
{
    use HasFactory;
    protected $fillable=[
        'session_id',
        'vonage_token',
        'user_id'
    ];
}
