<?php

namespace App\Services;

use App\Models\DistributionHistory;
use Illuminate\Support\Str;

class PublicLinkService
{
    public function getOrCreateToken($slip, string $slipType, string $channel = 'whatsapp'): string
    {
        $slipClass = $slipType === 'tetap' ? \App\Models\SalarySlipTetap::class : \App\Models\SalarySlipPartime::class;

        $distribution = DistributionHistory::firstOrNew([
            'slip_id' => $slip->id,
            'slip_type' => $slipClass,
            'channel' => $channel,
        ]);

        if (!$distribution->public_token) {
            $distribution->public_token = Str::random(48);
            $distribution->status = $distribution->status ?? 'pending';
            $distribution->save();
        }

        return $distribution->public_token;
    }
}