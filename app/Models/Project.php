<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'name',
        'description',
        'status',
        'created_by',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function developers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_developers');
    }

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_clients');
    }

    public function requirements(): HasMany
    {
        return $this->hasMany(Requirement::class);
    }
}
