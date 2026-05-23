<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Requirement extends Model
{
    protected $fillable = [
        'project_id',
        'client_id',
        'description',
        'audio_path',
        'audio_mime',
        'voice_path',
        'voice_mime',
        'status',
        'status_updated_at',
        'status_updated_by',
        'status_seen_at',
    ];

    protected $casts = [
        'status_updated_at' => 'datetime',
        'status_seen_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}
