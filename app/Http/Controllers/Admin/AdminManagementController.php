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
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminManagementController extends Controller
{
    public function index(): View
    {
        $admin = Auth::guard('admin')->user();
        if ($admin && $admin->role !== 'superadmin') {
            abort(403, 'Hanya superadmin yang dapat melihat daftar admin.');
        }

        $admins = Admin::query()->orderBy('name')->get();

        return view('admin.management.index', compact('admins'));
    }

    public function create(): View
    {
        $admin = Auth::guard('admin')->user();
        if ($admin && $admin->role !== 'superadmin') {
            abort(403, 'Hanya superadmin yang dapat menambah admin.');
        }

        return view('admin.management.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $actor = Auth::guard('admin')->user();
        if (! $actor || $actor->role !== 'superadmin') {
            abort(403, 'Hanya superadmin yang dapat menambah admin.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'unique:admins,username'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:admin,superadmin'],
        ], [
            'username.unique' => 'Username admin sudah digunakan.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $admin = Admin::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        SystemLog::record(SystemLog::TYPE_ADMIN_ACTION, "Admin '{$admin->username}' ditambahkan oleh '{$actor->username}'.");

        return redirect()->route('admin.management.index')->with('success', 'Admin baru berhasil dibuat.');
    }

    public function passwordForm(): View
    {
        return view('admin.management.password');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $admin = Auth::guard('admin')->user();

        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ], [
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.different' => 'Password baru harus berbeda dari password lama.',
        ]);

        if (! Hash::check($request->current_password, $admin->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Password lama tidak valid.',
            ]);
        }

        $admin->password = Hash::make($request->password);
        $admin->save();

        AdminSession::query()->where('admin_id', $admin->id)->whereNull('revoked_at')->update(['revoked_at' => now()]);
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        SystemLog::record(SystemLog::TYPE_ADMIN_ACTION, "Admin '{$admin->username}' mengganti password.");

        return redirect()->route('admin.login')->with('status', 'Password berhasil diganti. Silakan login kembali.');
    }
}
