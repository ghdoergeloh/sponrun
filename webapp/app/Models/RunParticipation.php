<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class RunParticipation extends Model
{
    protected $fillable = ['laps', 'tshirt_size', 'project_id'];

    protected function casts(): array
    {
        return [
            'laps' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (RunParticipation $rp) {
            if (empty($rp->hash)) {
                do {
                    $hash = Str::random(32);
                } while (static::where('hash', $hash)->exists());
                $rp->hash = $hash;
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sponsoredRun(): BelongsTo
    {
        return $this->belongsTo(SponsoredRun::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function sponsors(): HasMany
    {
        return $this->hasMany(Sponsor::class);
    }

    public function getShareLinkAttribute(): string
    {
        return url('run') . '/' . $this->hash;
    }

    public function calculateDonationSum(?int $laps = null): float
    {
        $laps ??= $this->laps;
        return $this->sponsors->sum(fn (Sponsor $s) => $s->calculateDonationSum($laps));
    }
}
