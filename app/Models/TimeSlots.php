<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TimeSlots extends Model
{
    use HasFactory;
    
    protected $table = 'time_slots_available';

    protected $fillable = [
        'chef_info_id',
        'to_time',
        'from_time',
        'date',
    ];

    // protected $casts = [
    //     'to_time' => 'integer',
    //     'from_time' => 'integer',
    // ];
    
//     public function getStatusAttribute()
// {
//     var_dump($this->date);
//     var_dump($this->from_time);

//     $fromDateTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $this->date . ' ' . $this->from_time);
//     $nowDateTime =  \Carbon\Carbon::now();

//     if ($fromDateTime->lte($nowDateTime)) {
//         return 'past';
//     }
// }


    
    public function chefInfo()
    {
        // return $this->belongsTo(ChefInfo::class, 'chef_info_id');
        return $this->belongsTo(ChefInfo::class);
    }
    public function getToTimeAttribute($value)
    {
        return Carbon::parse($value)->format('H:i');  
        // return Carbon::createFromTime('H:i',)->format('H:i');
    }
    public function getFromTimeAttribute($value)
    {
        return Carbon::parse($value)->format('H:i');  
        // return Carbon::createFromTime('H:i',)->format('H:i');
    }   
    public function getDateAttribute($value)
    {
        return Carbon::createFromFormat('Y-m-d', $value)->format('l dS M Y');
    }
//     public function setDateAttribute($value)
//     {
//         // dd($value);
//   return Carbon::createFromFormat('Y-m-d', $value)->format('l dS M Y');
    
//     }
}
