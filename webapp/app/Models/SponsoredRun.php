<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SponsoredRun extends Model
{
    protected $fillable = [
        'name', 'begin', 'end', 'closed', 'with_tshirt',
        'street', 'housenumber', 'postcode', 'city', 'description',
    ];

    protected function casts(): array
    {
        return [
            'begin'      => 'datetime',
            'end'        => 'datetime',
            'closed'     => 'boolean',
            'with_tshirt'=> 'boolean',
        ];
    }

    public function isElapsed(): bool
    {
        return $this->closed;
    }

    public function runParticipations(): HasMany
    {
        return $this->hasMany(RunParticipation::class);
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'run_participations');
    }

    public function projectlists(): BelongsToMany
    {
        return $this->belongsToMany(Projectlist::class, 'projectlist_sponsored_run')
            ->withTimestamps();
    }

    public function getProjectSelection(): array
    {
        $this->load('projectlists.projects');
        $selection = [null => 'Bitte auswählen'];
        foreach ($this->projectlists as $projectlist) {
            $selection = $projectlist->getProjectSelection() + $selection;
        }
        asort($selection);
        return $selection;
    }

    public function totalLaps(): int
    {
        return $this->runParticipations->sum('laps');
    }

    public function totalDonationSum(): float
    {
        return $this->runParticipations->sum(fn ($rp) => $rp->calculateDonationSum());
    }

    public function participantionsMostLaps(): Collection
    {
        $max = $this->runParticipations->max('laps') ?? 0;
        return $this->runParticipations->where('laps', $max)->values();
    }

    public function participantionsMostSponsors(): Collection
    {
        $loaded = $this->runParticipations->loadMissing('sponsors');
        $max = $loaded->max(fn ($rp) => $rp->sponsors->count()) ?? 0;
        return $loaded->filter(fn ($rp) => $rp->sponsors->count() === $max)->values();
    }

    public function participantionsHighestDonation(): Collection
    {
        $max = $this->runParticipations->max(fn ($rp) => $rp->calculateDonationSum()) ?? 0.0;
        return $this->runParticipations->filter(fn ($rp) => $rp->calculateDonationSum() == $max)->values();
    }

    public function oldestParticipants(): Collection
    {
        $this->participants->loadMissing([]);
        $min = $this->participants->min(fn ($u) => $u->birthday?->timestamp);
        return $this->participants->filter(fn ($u) => $u->birthday?->timestamp === $min)->values();
    }

    public function youngestParticipants(): Collection
    {
        $max = $this->participants->max(fn ($u) => $u->birthday?->timestamp);
        return $this->participants->filter(fn ($u) => $u->birthday?->timestamp === $max)->values();
    }
}
