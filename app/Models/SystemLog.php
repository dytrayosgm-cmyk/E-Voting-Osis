<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemLog extends Model
{
    public const TYPE_VOTE_SUCCESS = 'vote_success';

    public const TYPE_DUPLICATE_ATTEMPT = 'duplicate_attempt';

    public const TYPE_RACE_CONDITION_BLOCKED = 'race_condition_blocked';

    public const TYPE_NIS_NOT_FOUND = 'nis_not_found';

    public const TYPE_ADMIN_ACTION = 'admin_action';

    public const TYPE_SESSION_TOGGLED = 'session_toggled';

    public const TYPE_SESSION_OPENED = 'session_opened';

    public const TYPE_SESSION_CLOSED = 'session_closed';

    public const TYPE_DATA_RESET = 'data_reset';

    protected $fillable = [
        'type',
        'message',
        'student_id',
        'ip_address',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Helper cepat untuk membuat log dari mana saja tanpa mengulang
     * ip_address() setiap kali.
     */
    public static function record(string $type, string $message, ?int $studentId = null, array $meta = []): self
    {
        return self::create([
            'type' => $type,
            'message' => $message,
            'student_id' => $studentId,
            'ip_address' => request()->ip(),
            'meta' => $meta,
        ]);
    }
}
