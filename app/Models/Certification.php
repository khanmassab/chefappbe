<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certification extends Model
{
    use HasFactory;
    protected $table = 'certifications';
    protected $fillable = ['chef_id', 'certification_proof'];
    
    public function chefInfo()
    {
        return $this->belongsTo(ChefInfo::class);
    }
}
