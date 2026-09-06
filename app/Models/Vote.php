<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vote extends Model
{
    // Vote HANYA pernah dibuat lewat VotingBooth::confirmVote() di dalam
    // DB::transaction(). Fillable dibatasi ketat agar tidak ada jalan
    // pintas membuat record vote dari controller/route lain.
    protected $fillable = [
        'student_id',
        'candidate_id',
        'ip_address',
        'user_agent',
        'voted_at',
    ];

    protected function casts(): array
    {
        return [
            'voted_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }
}
