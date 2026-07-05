<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DistributionHistory extends Model
{
    protected $fillable = [
        'slip_id', 'slip_type', 'channel', 'status', 'note',
        'public_token', 'sent_at', 'sent_by',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function slip(): MorphTo
    {
        return $this->morphTo();
    }

    public function sentBy()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}