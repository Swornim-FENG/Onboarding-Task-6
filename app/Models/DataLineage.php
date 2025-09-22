<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataLineage extends Model
{
    protected $fillable = [
        'data_element',
        'action',
        'source',
        'transformation',
        'destination',
        'metadata',
        'occurred_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'occurred_at' => 'datetime',
    ];
}
