<?php

namespace Kaely\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'address',
        'phone',
        'email',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the users that belong to the branch.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_branches', 'branch_id', 'user_id');
    }

    /**
     * Check if branch is active.
     */
    public function isActive()
    {
        return $this->is_active;
    }

    /**
     * Activate the branch.
     */
    public function activate()
    {
        $this->update(['is_active' => true]);
    }

    /**
     * Deactivate the branch.
     */
    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }
} 