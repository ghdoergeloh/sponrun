<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sponsor extends Model
{
    protected $fillable = [
        'firstname', 'lastname',
        'street', 'housenumber', 'postcode', 'city',
        'phone', 'email',
        'donation_per_lap', 'donation_static_max',
        'wants_newsletter', 'ext_personnel_no',
    ];

    protected function casts(): array
    {
        return [
            'donation_per_lap'   => 'decimal:2',
            'donation_static_max'=> 'decimal:2',
            'wants_newsletter'   => 'boolean',
        ];
    }

    public function setDonationPerLapAttribute(mixed $value): void
    {
        $this->attributes['donation_per_lap'] = $value === null ? 0 : str_replace(',', '.', $value);
    }

    public function setDonationStaticMaxAttribute(mixed $value): void
    {
        $this->attributes['donation_static_max'] = $value === null ? 0 : str_replace(',', '.', $value);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function runParticipation(): BelongsTo
    {
        return $this->belongsTo(RunParticipation::class);
    }

    public function calculateDonationSum(int $laps): float
    {
        $perLap    = (float) $this->donation_per_lap;
        $staticMax = (float) $this->donation_static_max;
        $donation  = $perLap * $laps;

        if ($staticMax == 0) {
            return $donation;
        }
        if ($perLap == 0) {
            return $staticMax;
        }
        return min($donation, $staticMax);
    }
}
