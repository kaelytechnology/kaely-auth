<?php

namespace Kaely\Auth\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasUserFields
{
    /**
     * Boot the trait.
     */
    protected static function bootHasUserFields()
    {
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->user_add = auth()->id();
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->user_edit = auth()->id();
            }
        });

        static::deleting(function ($model) {
            if (auth()->check()) {
                $model->user_deleted = auth()->id();
            }
        });
    }

    /**
     * Get the user who created the record.
     */
    public function createdBy()
    {
        $userModel = config('kaely-auth.models.user');
        return $this->belongsTo($userModel, 'user_add');
    }

    /**
     * Get the user who last updated the record.
     */
    public function updatedBy()
    {
        $userModel = config('kaely-auth.models.user');
        return $this->belongsTo($userModel, 'user_edit');
    }

    /**
     * Get the user who deleted the record.
     */
    public function deletedBy()
    {
        $userModel = config('kaely-auth.models.user');
        return $this->belongsTo($userModel, 'user_deleted');
    }

    /**
     * Scope a query to only include records created by a specific user.
     */
    public function scopeCreatedBy(Builder $query, $userId)
    {
        return $query->where('user_add', $userId);
    }

    /**
     * Scope a query to only include records updated by a specific user.
     */
    public function scopeUpdatedBy(Builder $query, $userId)
    {
        return $query->where('user_edit', $userId);
    }

    /**
     * Scope a query to only include records deleted by a specific user.
     */
    public function scopeDeletedBy(Builder $query, $userId)
    {
        return $query->where('user_deleted', $userId);
    }

    /**
     * Check if the record was created by a specific user.
     */
    public function isCreatedBy($userId)
    {
        return $this->user_add == $userId;
    }

    /**
     * Check if the record was updated by a specific user.
     */
    public function isUpdatedBy($userId)
    {
        return $this->user_edit == $userId;
    }

    /**
     * Check if the record was deleted by a specific user.
     */
    public function isDeletedBy($userId)
    {
        return $this->user_deleted == $userId;
    }
} 