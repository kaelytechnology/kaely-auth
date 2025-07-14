<?php

namespace Kaely\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the users that belong to the department.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_departments', 'department_id', 'user_id');
    }

    /**
     * Check if department is active.
     */
    public function isActive()
    {
        return $this->is_active;
    }

    /**
     * Activate the department.
     */
    public function activate()
    {
        $this->update(['is_active' => true]);
    }

    /**
     * Deactivate the department.
     */
    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }
} 