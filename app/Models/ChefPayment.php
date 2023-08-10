<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChefPayment extends Model
{
    use HasFactory;

    protected $fillable=[
        'user_id', //chef id
        'amount',
        'status',
        'due',
        'charge_id',
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
