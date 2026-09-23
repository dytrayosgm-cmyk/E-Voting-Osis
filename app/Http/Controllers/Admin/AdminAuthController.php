<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminSession;
use App\Models\SystemLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AdminAuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = 'admin-login:'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'username' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.",
            ]);
        }

        $admin = Admin::where('username', $credentials['username'])->first();
        if (! $admin || ! Hash::check($credentials['password'], $admin->password)) {
            RateLimiter::hit($throttleKey, 60);
            throw ValidationException::withMessages([
                'username' => 'Username atau password salah.',
            ]);
        }

        $existing = AdminSession::query()
            ->where('admin_id', $admin->id)
            ->whereNull('revoked_at')
            ->first();

        if ($existing && $existing->session_id !== $request->session()->getId()) {
            RateLimiter::hit($throttleKey, 60);
            throw ValidationException::withMessages([
                'username' => 'Akun admin ini sedang aktif di sesi lain. Logout sesi yang aktif terlebih dahulu.',
            ]);
        }

        if (! Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, 60);
            throw ValidationException::withMessages([
                'username' => 'Username atau password salah.',
            ]);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        $admin->last_login_at = now();
        $admin->last_login_ip = $request->ip();
        $admin->save();

        $sessionId = $request->session()->getId();
        AdminSession::query()->updateOrCreate(
            ['admin_id' => $admin->id, 'session_id' => $sessionId],
            [
                'admin_id' => $admin->id,
                'session_id' => $sessionId,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'logged_in_at' => now(),
                'last_seen_at' => now(),
                'revoked_at' => null,
            ],
        );

        AdminSession::query()->where('admin_id', $admin->id)
            ->where('session_id', '!=', $sessionId)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);

        SystemLog::record(SystemLog::TYPE_ADMIN_ACTION, "Admin '{$admin->username}' login ke Control Center.");

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $admin = Auth::guard('admin')->user();
        if ($admin) {
            SystemLog::record(SystemLog::TYPE_ADMIN_ACTION, "Admin '{$admin->username}' logout.");
            AdminSession::query()
                ->where('admin_id', $admin->id)
                ->where('session_id', $request->session()->getId())
                ->update(['revoked_at' => now(), 'last_seen_at' => now()]);
        }

        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
