@extends('layouts.admin')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-2xs p-6 sm:p-8 space-y-5">
        <div class="pb-3 border-b border-slate-100">
            <h1 class="text-xl font-black text-slate-900 tracking-tight">Ubah Password Admin</h1>
            <p class="mt-0.5 text-xs text-slate-500">Perbarui kata sandi akun admin Anda demi keamanan.</p>
        </div>

        @if (session('status'))
            <div class="rounded-2xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-xs font-semibold text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-2xl bg-red-50 border border-red-200 p-3.5 text-xs text-red-700">
                <ul class="list-disc pl-4 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.password.update') }}" class="space-y-4 text-xs">
            @csrf
            <div>
                <label for="current_password" class="block font-bold text-slate-700 mb-1">Password Saat Ini</label>
                <input id="current_password" type="password" name="current_password" required class="ui-input w-full font-semibold" placeholder="Masukkan password saat ini">
            </div>
            <div>
                <label for="password" class="block font-bold text-slate-700 mb-1">Password Baru</label>
                <input id="password" type="password" name="password" required class="ui-input w-full font-semibold" placeholder="Minimal 8 karakter">
            </div>
            <div>
                <label for="password_confirmation" class="block font-bold text-slate-700 mb-1">Konfirmasi Password Baru</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required class="ui-input w-full font-semibold" placeholder="Ulangi password baru">
            </div>

            <div class="flex gap-2.5 pt-3 border-t border-slate-100">
                <a href="{{ route('admin.dashboard') }}" class="ui-button bg-slate-100 text-slate-700 hover:bg-slate-200 text-xs px-4 py-2">
                    Batal
                </a>
                <button type="submit" class="ui-button bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs px-5 py-2 shadow-xs">
                    Simpan Password Baru
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
