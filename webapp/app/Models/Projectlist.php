<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Projectlist extends Model
{
    protected $fillable = ['name'];

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_projectlist')
            ->withTimestamps();
    }

    public function sponsoredRuns(): BelongsToMany
    {
        return $this->belongsToMany(SponsoredRun::class, 'projectlist_sponsored_run')
            ->withTimestamps();
    }

    public function getProjectSelection(): array
    {
        $selection = [];
        foreach ($this->projects as $project) {
            $selection[$project->id] = $project->name_with_scope;
        }

        return $selection;
    }
}
