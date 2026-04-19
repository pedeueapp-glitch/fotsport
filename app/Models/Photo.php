<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'user_id',
        'original_path',
        'watermarked_path',
        'price',
        'face_descriptors',
        'face_indexed',
    ];

    protected $casts = [
        'face_descriptors' => 'array',
        'face_indexed' => 'boolean',
    ];


    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
