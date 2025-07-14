<?php

namespace Kaely\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Person extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'address',
        'birth_date',
        'gender',
        'nationality',
        'document_type',
        'document_number',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    /**
     * Get the user that owns the person.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 