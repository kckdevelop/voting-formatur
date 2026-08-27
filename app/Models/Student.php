<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Student extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'nis',
        'nama',
        'kelas',
        'token',
        'plain_token',
        'status',
        'has_voted',
        'voted_at',
    ];

    protected $hidden = [
        'token',
    ];

    protected function casts(): array
    {
        return [
            'has_voted' => 'boolean',
            'voted_at' => 'datetime',
        ];
    }

    /**
     * Get the password for the authenticatable user (token column).
     */
    public function getAuthPassword(): string
    {
        return $this->token;
    }

    /**
     * Relationship to vote record.
     */
    public function vote()
    {
        return $this->hasOne(Vote::class);
    }
}
