@extends('layouts.admin')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm p-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Tambah Admin</h1>
            <p class="mt-1 text-sm text-gray-500">Buat akun admin baru untuk Control Center.</p>
        </div>

        @if ($errors->any())
            <div class="rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.management.store') }}" class="space-y-4">
            @csrf
            <div>
                <label for="name" class="block text-sm font-semibold text-gray-700">Nama Lengkap</label>
                <input id="name" name="name" value="{{ old('name') }}" required class="mt-1.5 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label for="username" class="block text-sm font-semibold text-gray-700">Username</label>
                <input id="username" name="username" value="{{ old('username') }}" required class="mt-1.5 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700">Password</label>
                    <input id="password" type="password" name="password" required class="mt-1.5 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-700">Konfirmasi Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required class="mt-1.5 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>
            <div>
                <label for="role" class="block text-sm font-semibold text-gray-700">Role</label>
                <select id="role" name="role" required class="mt-1.5 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="admin">Admin</option>
                    <option value="superadmin">Super Admin</option>
                </select>
            </div>

            <div class="flex gap-3 pt-4">
                <a href="{{ route('admin.management.index') }}" class="ui-button border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">
                    Batal
                </a>
                <button type="submit" class="ui-button bg-indigo-600 text-white hover:bg-indigo-500">
                    Simpan Admin
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
