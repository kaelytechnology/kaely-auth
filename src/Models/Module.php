<?php

namespace Kaely\Auth\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kaely\Auth\Traits\HasUserFields;

class Module extends Model
{
    use HasFactory, SoftDeletes, HasUserFields;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'main_modules';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'route',
        'parent_id',
        'order',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Get the permissions that belong to the module.
     */
    public function permissions()
    {
        return $this->hasMany(Permission::class, 'module_id');
    }

    /**
     * Get the parent module.
     */
    public function parent()
    {
        return $this->belongsTo(Module::class, 'parent_id');
    }

    /**
     * Get the child modules.
     */
    public function children()
    {
        return $this->hasMany(Module::class, 'parent_id');
    }

    /**
     * Scope a query to only include active modules.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include inactive modules.
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope a query to only include root modules (no parent).
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope a query to order by order field.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }

    /**
     * Check if module is active.
     */
    public function isActive()
    {
        return $this->is_active;
    }

    /**
     * Activate the module.
     */
    public function activate()
    {
        $this->update(['is_active' => true]);
    }

    /**
     * Deactivate the module.
     */
    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Check if module has children.
     */
    public function hasChildren()
    {
        return $this->children()->exists();
    }

    /**
     * Check if module is a child.
     */
    public function isChild()
    {
        return !is_null($this->parent_id);
    }

    /**
     * Get the full path of the module.
     */
    public function getFullPathAttribute()
    {
        $path = [$this->name];
        $parent = $this->parent;

        while ($parent) {
            array_unshift($path, $parent->name);
            $parent = $parent->parent;
        }

        return implode(' > ', $path);
    }
} 