<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReferralCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'code',
        'type',
        'amount',
        'used_count',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'amount' => 'decimal:2',
        'used_count' => 'integer',
    ];

    /**
     * Check if the code is active.
     */
    public function isValid()
    {
        return $this->is_active;
    }

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
