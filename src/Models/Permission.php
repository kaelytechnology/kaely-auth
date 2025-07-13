<?php

namespace Kaely\Auth\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kaely\Auth\Traits\HasUserFields;

class Permission extends Model
{
    use HasFactory, SoftDeletes, HasUserFields;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'main_permissions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'module_id',
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
     * Get the roles that belong to the permission.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'main_role_permission', 'permission_id', 'role_id');
    }

    /**
     * Get the module that belongs to the permission.
     */
    public function module()
    {
        return $this->belongsTo(Module::class, 'module_id');
    }

    /**
     * Scope a query to only include active permissions.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include inactive permissions.
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope a query to only include permissions by module.
     */
    public function scopeByModule($query, $moduleId)
    {
        return $query->where('module_id', $moduleId);
    }

    /**
     * Check if permission is active.
     */
    public function isActive()
    {
        return $this->is_active;
    }

    /**
     * Activate the permission.
     */
    public function activate()
    {
        $this->update(['is_active' => true]);
    }

    /**
     * Deactivate the permission.
     */
    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }
} 