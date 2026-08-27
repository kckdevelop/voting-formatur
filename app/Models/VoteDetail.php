<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoteDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'vote_id',
        'candidate_id',
    ];

    public function vote()
    {
        return $this->belongsTo(Vote::class);
    }

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }
}
