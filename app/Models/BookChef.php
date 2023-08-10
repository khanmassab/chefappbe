<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookChef extends Model
{
    use HasFactory;
    protected $table = 'book_chefs';
    protected $fillable = ['user_id', 'chef_id', 'time_slot_id', 'status','reminder_time','reminder_sent'];

    public function ChefAccount()
    {
        return $this->belongsTo(UserAccount::class,'chef_id', 'user_id');
    }
    public function chefInfo()
    {
        return $this->belongsTo(ChefInfo::class, 'chef_id' ,'user_id');
    }

    public function timeSlot()
    {
        return $this->belongsTo(TimeSlots::class,'time_slot_id','id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    public function chef(){
        return $this->belongsTo(User::class, 'chef_id', 'id');
    }
}
