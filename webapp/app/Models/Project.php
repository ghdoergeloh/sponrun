<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Project extends Model
{
    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = ['id', 'name', 'scope'];

    public function projectlists(): BelongsToMany
    {
        return $this->belongsToMany(Projectlist::class, 'project_projectlist')
            ->withTimestamps();
    }

    public function getNameWithScopeAttribute(): string
    {
        return match ($this->scope) {
            'project' => $this->name.' (Projekt)',
            'person' => $this->name.' (Person)',
            default => $this->name,
        };
    }
}
