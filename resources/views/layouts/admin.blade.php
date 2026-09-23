<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Control Center' }} — E-Voting OSIS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-50 text-gray-900 antialiased min-h-screen">
    <div class="flex min-h-screen">
        <aside class="hidden lg:flex lg:flex-col lg:w-64 shrink-0 bg-white border-r border-gray-200">
            <div class="h-20 flex items-center px-6 border-b border-gray-200">
                <span class="w-10 h-10 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-bold text-base">EV</span>
                <span class="ml-3 text-base font-bold text-gray-900">E-Voting OSIS</span>
            </div>
            <nav class="flex-1 px-3 py-5 space-y-2">
                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center gap-3 px-3 py-3 rounded-lg text-base font-semibold
                          {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50' }}">
                    <span class="w-2 h-2 rounded-full bg-current"></span> Dashboard
                </a>
                @if (auth('admin')->user()?->role === 'superadmin')
                <a href="{{ route('admin.management.index') }}"
                   class="flex items-center gap-3 px-3 py-3 rounded-lg text-base font-semibold
                          {{ request()->routeIs('admin.management.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50' }}">
                    <span class="w-2 h-2 rounded-full bg-current"></span> Kelola Admin
                </a>
                @endif
                <a href="{{ route('admin.password.form') }}"
                   class="flex items-center gap-3 px-3 py-3 rounded-lg text-base font-semibold
                          {{ request()->routeIs('admin.password.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50' }}">
                    <span class="w-2 h-2 rounded-full bg-current"></span> Ubah Password
                </a>
                <a href="{{ route('admin.reports.export') }}"
                   class="flex items-center gap-3 px-3 py-3 rounded-lg text-base font-semibold text-gray-600 hover:bg-gray-50">
                    <span class="w-2 h-2 rounded-full bg-current"></span> Export Laporan (CSV)
                </a>
            </nav>
            <div class="p-3 border-t border-gray-200">
                <div class="px-3 py-3 text-sm text-gray-500">
                    Masuk sebagai <span class="font-medium text-gray-700">{{ auth('admin')->user()?->name }}</span>
                </div>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-3 py-3 rounded-lg text-base font-semibold text-gray-600 hover:bg-gray-50">
                        Keluar
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex-1 min-w-0">
            <header class="lg:hidden h-16 flex items-center px-4 bg-white border-b border-gray-200">
                <span class="w-9 h-9 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-bold text-sm">EV</span>
                <span class="ml-3 font-bold text-base">E-Voting OSIS</span>
            </header>
            <main class="p-4 sm:p-6 lg:p-8 xl:p-10">
                @yield('content')
                {{ $slot ?? '' }}
            </main>
        </div>
    </div>
    @livewireScripts
</body>
</html>
