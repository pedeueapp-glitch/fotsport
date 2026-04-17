<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;
    
    protected $guarded = [];

    public function photo()
    {
        return $this->belongsTo(Photo::class);
    }
    
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
