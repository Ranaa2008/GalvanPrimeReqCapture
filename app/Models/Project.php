<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'created_by',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'project_user')
            ->withPivot(['assignment_role', 'assigned_by'])
            ->withTimestamps();
    }

    public function clients()
    {
        return $this->members()->wherePivot('assignment_role', 'client');
    }

    public function developers()
    {
        return $this->members()->wherePivot('assignment_role', 'developer');
    }

    public function requirements()
    {
        return $this->hasMany(Requirement::class);
    }
}
