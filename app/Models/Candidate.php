<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Candidate extends Model
{
    protected $fillable = [
        'number_order',
        'type',
        'chairman_name',
        'vice_chairman_name',
        'chairman_photo',
        'vice_chairman_photo',
        'slogan',
        'vision',
        'mission',
        'is_active',
    ];

    protected $attributes = [
        'type' => 'paslon',
        'vote_count' => 0,
        'is_active' => true,
    ];

    // vote_count TIDAK fillable — hanya boleh berubah lewat increment()
    // di dalam transaksi vote, tidak pernah lewat form admin.

    protected function casts(): array
    {
        return [
            'mission' => 'array',
            'is_active' => 'boolean',
            'vote_count' => 'integer',
        ];
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * Nama tampilan kandidat (nama gabungan paslon atau nama tunggal kandidat individu).
     */
    public function getPairNameAttribute(): string
    {
        if ($this->isKandidat() || empty($this->vice_chairman_name)) {
            return (string) $this->chairman_name;
        }

        return "{$this->chairman_name} & {$this->vice_chairman_name}";
    }

    public function isKandidat(): bool
    {
        return ($this->type ?? 'paslon') === 'kandidat';
    }

    public function isPaslon(): bool
    {
        return ! $this->isKandidat();
    }

    /**
     * Persentase perolehan suara relatif terhadap total suara sah.
     */
    public function percentage(int $totalVotes): float
    {
        if ($totalVotes <= 0) {
            return 0.0;
        }

        return round(($this->vote_count / $totalVotes) * 100, 1);
    }
}
