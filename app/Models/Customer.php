<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'email',
        'state',
        'join_date',
        'total_orders',
        'total_spent',
        'last_activity',
    ];

    protected $casts = [
        'join_date' => 'date',
        'last_activity' => 'datetime',
        'total_spent' => 'decimal:2',
    ];
}
