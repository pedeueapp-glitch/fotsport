<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Customer extends Authenticatable
{
    use HasFactory;

    protected $guarded = [];

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
}