<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'request_amount',
        'fee_amount',
        'net_amount',
        'status',
        'mp_transfer_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
