<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'discount',
        'total_used',
        'max_uses',
        'expires',
        'status'
    ];

    protected static function booted()
    {
        static::updating(function ($promo) {
            if ($promo->total_used >= $promo->max_uses) {
                $promo->status = 0;
            }
        });
    }
}
