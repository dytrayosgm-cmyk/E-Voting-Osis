@extends('layouts.admin')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm p-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Ubah Password Admin</h1>
            <p class="mt-1 text-sm text-gray-500">Ganti password akun admin yang sedang login.</p>
        </div>

        @if (session('status'))
            <div class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.password.update') }}" class="space-y-4">
            @csrf
            <div>
                <label for="current_password" class="block text-sm font-semibold text-gray-700">Password Saat Ini</label>
                <input id="current_password" type="password" name="current_password" required class="mt-1.5 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label for="password" class="block text-sm font-semibold text-gray-700">Password Baru</label>
                <input id="password" type="password" name="password" required class="mt-1.5 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-semibold text-gray-700">Konfirmasi Password Baru</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required class="mt-1.5 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div class="flex gap-3 pt-4">
                <a href="{{ route('admin.dashboard') }}" class="ui-button border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">
                    Batal
                </a>
                <button type="submit" class="ui-button bg-indigo-600 text-white hover:bg-indigo-500">
                    Simpan Password Baru
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
