<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceSubmission extends Model
{
    protected $fillable = [
        'service_id',
        'document',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function getDocumentUrlAttribute()
    {
        return $this->document
            ? asset('storage/' . $this->document)
            : null;
    }

    public function getValue($key)
    {
        return $this->data[$key] ?? null;
    }
}
