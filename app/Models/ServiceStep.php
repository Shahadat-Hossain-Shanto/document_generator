<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceStep extends Model
{
    protected $fillable = [
        'service_id',
        'title',
        'order',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function fields()
    {
        return $this->hasMany(ServiceField::class)->orderBy('order');
    }
}
