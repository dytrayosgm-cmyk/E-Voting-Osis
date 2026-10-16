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
        'unique_code',
    ];

    protected $attributes = [
        'has_voted' => false,
    ];

    protected static function booted(): void
    {
        static::creating(function (Student $student) {
            if (empty($student->unique_code)) {
                $student->unique_code = static::generateUniqueCode();
            } else {
                $student->unique_code = strtoupper(trim((string) $student->unique_code));
            }
        });
    }

    /**
     * Hasilkan kode unik 4 karakter acak (huruf kapital dan angka)
     * yang tidak ambigu (menghindari 0, O, 1, I).
     */
    public static function generateUniqueCode(): string
    {
        $characters = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        do {
            $code = '';
            for ($i = 0; $i < 4; $i++) {
                $code .= $characters[random_int(0, strlen($characters) - 1)];
            }
        } while (static::query()->where('unique_code', $code)->exists());

        return $code;
    }

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
