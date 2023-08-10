<?php

namespace App\Models;

use App\Models\BookChef;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserPayment extends Model
{
    use HasFactory;
    protected $fillable=[
        'token_id',
        'object',
        'email',
        'name',
        'user_id',
        'booking_id',
        'amount',
        'recived_to_chef',
        'created_at',
        'updated_at',
    ];

    public function bookingChef()
    {
        return $this->belongsTo(BookChef::class,'booking_id','id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
}
