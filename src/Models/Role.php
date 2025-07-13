<?php

namespace Kaely\Auth\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kaely\Auth\Traits\HasUserFields;

class Role extends Model
{
    use HasFactory, SoftDeletes, HasUserFields;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'main_roles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'role_category_id',
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
     * Get the users that belong to the role.
     */
    public function users()
    {
        $userModel = config('kaely-auth.models.user');
        return $this->belongsToMany($userModel, 'main_user_role', 'role_id', 'user_id');
    }

    /**
     * Get the permissions that belong to the role.
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'main_role_permission', 'role_id', 'permission_id');
    }

    /**
     * Get the role category that belongs to the role.
     */
    public function category()
    {
        return $this->belongsTo(RoleCategory::class, 'role_category_id');
    }

    /**
     * Scope a query to only include active roles.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include inactive roles.
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Check if role is active.
     */
    public function isActive()
    {
        return $this->is_active;
    }

    /**
     * Activate the role.
     */
    public function activate()
    {
        $this->update(['is_active' => true]);
    }

    /**
     * Deactivate the role.
     */
    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Assign permissions to the role.
     */
    public function assignPermissions($permissions)
    {
        if (is_array($permissions)) {
            $this->permissions()->attach($permissions);
        } else {
            $this->permissions()->attach($permissions);
        }
    }

    /**
     * Remove permissions from the role.
     */
    public function removePermissions($permissions)
    {
        if (is_array($permissions)) {
            $this->permissions()->detach($permissions);
        } else {
            $this->permissions()->detach($permissions);
        }
    }

    /**
     * Sync permissions for the role.
     */
    public function syncPermissions($permissions)
    {
        $this->permissions()->sync($permissions);
    }
} 