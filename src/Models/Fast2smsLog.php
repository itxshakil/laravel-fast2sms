<?php

declare(strict_types=1);

namespace Shakil\Fast2sms\Models;

use Illuminate\Database\Eloquent\Model;

class Fast2smsLog extends Model
{
    protected $guarded = [];

    protected $casts = [
        'payload' => 'array',
        'response' => 'array',
        'is_success' => 'boolean',
    ];
}
