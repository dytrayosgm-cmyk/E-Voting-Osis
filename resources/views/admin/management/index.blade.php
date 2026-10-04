@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Manajemen Admin</h1>
            <p class="mt-0.5 text-xs text-slate-500">Daftar akun panitia dan pengelola Control Center.</p>
        </div>
        <a href="{{ route('admin.management.create') }}" class="ui-button bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs px-4 py-2.5 rounded-xl shadow-xs self-start sm:self-auto flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah Admin
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-2xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-xs font-semibold text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left text-slate-600">
                <thead class="bg-slate-50/80 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                <tr>
                    <th class="px-5 py-4 font-bold">Nama</th>
                    <th class="px-5 py-4 font-bold">Username</th>
                    <th class="px-5 py-4 font-bold">Role</th>
                    <th class="px-5 py-4 font-bold">Login Terakhir</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @forelse ($admins as $admin)
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="px-5 py-4 font-bold text-slate-900">{{ $admin->name }}</td>
                        <td class="px-5 py-4 font-medium text-slate-600">{{ $admin->username }}</td>
                        <td class="px-5 py-4">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $admin->role === 'superadmin' ? 'bg-blue-50 text-blue-700 border border-blue-100' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                                {{ $admin->role }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-slate-500">
                            {{ $admin->last_login_at ? $admin->last_login_at->format('d M Y, H:i') : 'Belum pernah login' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-8 text-center text-slate-400">
                            Belum ada admin terdaftar.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
