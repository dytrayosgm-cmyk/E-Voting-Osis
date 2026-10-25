<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'session_name',
        'election_period',
        'is_voting_open',
        'opened_at',
        'closed_at',
        'is_finalized',
        'finalized_at',
    ];

    protected function casts(): array
    {
        return [
            'is_voting_open' => 'boolean',
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'is_finalized' => 'boolean',
            'finalized_at' => 'datetime',
        ];
    }

    public function getElectionPeriodAttribute($value): string
    {
        return $value ?: 'Periode 2025/2026';
    }

    /**
     * Selalu mengambil (atau membuat) baris settings tunggal id=1.
     * Dipakai di seluruh aplikasi supaya tidak ada query berulang
     * yang rawan salah row.
     */
    public static function current(): self
    {
        return self::firstOrCreate(['id' => 1]);
    }
}
