<?php

namespace Kaely\Auth\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kaely\Auth\Traits\HasUserFields;

class Person extends Model
{
    use HasFactory, SoftDeletes, HasUserFields;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'main_people';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'birth_date',
        'gender',
        'address',
        'document_type',
        'document_number',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'birth_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user associated with the person.
     */
    public function user()
    {
        $userModel = config('kaely-auth.models.user');
        return $this->hasOne($userModel, 'person_id');
    }

    /**
     * Scope a query to only include active people.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include inactive people.
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Check if person is active.
     */
    public function isActive()
    {
        return $this->is_active;
    }

    /**
     * Activate the person.
     */
    public function activate()
    {
        $this->update(['is_active' => true]);
    }

    /**
     * Deactivate the person.
     */
    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Get the person's full name.
     */
    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Get the person's initials.
     */
    public function getInitialsAttribute()
    {
        $first = substr($this->first_name, 0, 1);
        $last = substr($this->last_name, 0, 1);
        return strtoupper($first . $last);
    }
} 