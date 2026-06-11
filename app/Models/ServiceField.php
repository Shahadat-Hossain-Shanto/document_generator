<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceField extends Model
{
    protected $fillable = [
        'service_id',
        'service_step_id',
        'label',
        'document_key',
        'type',
        'placeholder',
        'required',
        'options',
        'order',
        'column',
    ];

    protected $casts = [
        'required' => 'boolean',
        'options' => 'array',
        'column' => 'integer',
    ];

    public function step()
    {
        return $this->belongsTo(ServiceStep::class, 'service_step_id');
    }

    public function isSelectable()
    {
        return in_array($this->type, ['select', 'radio']);
    }
}
