@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Manajemen Admin</h1>
            <p class="mt-1 text-sm text-gray-500">Daftar admin dan akses kontrol center.</p>
        </div>
        <a href="{{ route('admin.management.create') }}" class="ui-button bg-indigo-600 text-white hover:bg-indigo-500">
            Tambah Admin
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Nama</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Username</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Role</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Login Terakhir</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @forelse ($admins as $admin)
                    <tr>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $admin->name }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $admin->username }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $admin->role === 'superadmin' ? 'bg-indigo-50 text-indigo-700' : 'bg-gray-100 text-gray-700' }}">
                                {{ $admin->role }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            {{ $admin->last_login_at ? $admin->last_login_at->format('d M Y, H:i') : 'Belum pernah login' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">
                            Belum ada admin.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
