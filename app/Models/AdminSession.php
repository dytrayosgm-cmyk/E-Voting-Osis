<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminSession extends Model
{
    protected $table = 'admin_sessions';

    protected $fillable = [
        'admin_id',
        'session_id',
        'ip_address',
        'user_agent',
        'logged_in_at',
        'last_seen_at',
        'revoked_at',
    ];

    protected $casts = [
        'logged_in_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
