<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $casts = [
        'emails' => 'array',
        'personal_emails' => 'array',
        'phones' => 'array',
        'marvin_searches' => 'array',
        'skills' => 'array',
        'languages' => 'array',
        'schools' => 'array',
        'external_searches' => 'array',
    ];
}