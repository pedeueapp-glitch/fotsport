<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeroItem extends Model
{
    /** @use HasFactory<\Database\Factories\HeroItemFactory> */
    use HasFactory;

    protected $fillable = ['image_path', 'title', 'subtitle', 'is_active', 'order'];
}
