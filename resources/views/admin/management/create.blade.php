@extends('layouts.admin')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-2xs p-6 sm:p-8 space-y-5">
        <div class="pb-3 border-b border-slate-100">
            <h1 class="text-xl font-black text-slate-900 tracking-tight">Tambah Admin Baru</h1>
            <p class="mt-0.5 text-xs text-slate-500">Buat akun pengelola baru untuk Control Center E-Voting.</p>
        </div>

        @if ($errors->any())
            <div class="rounded-2xl bg-red-50 border border-red-200 p-3.5 text-xs text-red-700">
                <ul class="list-disc pl-4 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.management.store') }}" class="space-y-4 text-xs">
            @csrf
            <div>
                <label for="name" class="block font-bold text-slate-700 mb-1">Nama Lengkap</label>
                <input id="name" name="name" value="{{ old('name') }}" required class="ui-input w-full font-semibold text-slate-900" placeholder="Contoh: Budi Prasetyo">
            </div>
            <div>
                <label for="username" class="block font-bold text-slate-700 mb-1">Username</label>
                <input id="username" name="username" value="{{ old('username') }}" required class="ui-input w-full font-semibold text-slate-900" placeholder="Contoh: budi_admin">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label for="password" class="block font-bold text-slate-700 mb-1">Password</label>
                    <input id="password" type="password" name="password" required class="ui-input w-full" placeholder="••••••••">
                </div>
                <div>
                    <label for="password_confirmation" class="block font-bold text-slate-700 mb-1">Konfirmasi Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required class="ui-input w-full" placeholder="••••••••">
                </div>
            </div>
            <div>
                <label for="role" class="block font-bold text-slate-700 mb-1">Role Akun</label>
                <select id="role" name="role" required class="ui-input w-full font-semibold text-slate-900">
                    <option value="admin">Admin Standar (Monitoring &amp; Sesi)</option>
                    <option value="superadmin">Super Admin (Akses Penuh + Kelola Akun)</option>
                </select>
            </div>

            <div class="flex gap-2.5 pt-3 border-t border-slate-100">
                <a href="{{ route('admin.management.index') }}" class="ui-button bg-slate-100 text-slate-700 hover:bg-slate-200 text-xs px-4 py-2">
                    Batal
                </a>
                <button type="submit" class="ui-button bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs px-5 py-2 shadow-xs">
                    Simpan Admin
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
