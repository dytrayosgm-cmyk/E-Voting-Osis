<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    protected $fillable = [
        'nis',
        'name',
        'class',
    ];

    /**
     * `has_voted` dan `voted_at` SENGAJA tidak dimasukkan ke $fillable.
     * Kedua kolom ini hanya boleh diubah lewat alur terkontrol di dalam
     * DB::transaction() pada VotingBooth::confirmVote(), bukan lewat
     * mass-assignment dari request biasa. Ini mencegah manipulasi status
     * "sudah memilih" dari luar alur voting resmi.
     */
    protected function casts(): array
    {
        return [
            'has_voted' => 'boolean',
            'voted_at' => 'datetime',
        ];
    }

    public function vote(): HasOne
    {
        return $this->hasOne(Vote::class);
    }
}
