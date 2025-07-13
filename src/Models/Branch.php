<?php

namespace Kaely\Auth\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kaely\Auth\Traits\HasUserFields;

class Branch extends Model
{
    use HasFactory, SoftDeletes, HasUserFields;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'main_branches';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'description',
        'address',
        'phone',
        'email',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the users that belong to the branch.
     */
    public function users()
    {
        $userModel = config('kaely-auth.models.user');
        return $this->belongsToMany($userModel, 'main_branches_users', 'branch_id', 'user_id');
    }

    /**
     * Scope a query to only include active branches.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include inactive branches.
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
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