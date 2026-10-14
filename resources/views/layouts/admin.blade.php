<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dashboard Admin' }} — E-Voting OSIS SMK An-Nurmaniyah</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo-smk.jpg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-[#f8fafc] text-slate-900 antialiased min-h-screen flex flex-col font-sans">

    {{-- Top Navbar matching Design --}}
    <header class="sticky top-0 z-40 bg-white border-b border-slate-200/80 shadow-2xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex items-center justify-between gap-4">
            {{-- Left: Brand & School Info --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 rounded-2xl bg-white p-1 border border-slate-200/90 shadow-2xs flex items-center justify-center shrink-0 group-hover:scale-105 transition">
                        <img src="{{ asset('images/logo-smk.jpg') }}" alt="Logo SMK An-Nurmaniyah" class="w-full h-full object-contain">
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-black tracking-wider uppercase bg-[#eef0ff] text-[#4f46e5]">
                                SISTEM E-VOTING DIGITAL
                            </span>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-[#edfdf7] text-[#059669] border border-[#a7f3d0]">
                                v2.4-stable
                            </span>
                        </div>
                        <h1 class="text-base sm:text-lg font-black text-slate-900 leading-tight mt-0.5 tracking-tight">
                            SMK An-Nurmaniyah
                        </h1>
                    </div>
                </a>
            </div>

            {{-- Right: Server Status, Quick Actions & Admin Profile --}}
            <div class="flex items-center gap-2.5 sm:gap-3">
                {{-- Server Live Badge --}}
                <div class="hidden lg:inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold bg-slate-50 border border-slate-200/80 text-slate-700 shadow-2xs">
                    <span class="w-3.5 h-3.5 rounded-full bg-emerald-100 flex items-center justify-center">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    </span>
                    <span>Server Live <span class="text-slate-400 font-normal">(0.1ms • WS Aktif)</span></span>
                </div>

                {{-- Quick Action: Buat Akun Admin (Superadmin Only) --}}
                @if (auth('admin')->user()?->role === 'superadmin')
                <a href="{{ route('admin.management.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200/80 transition shadow-2xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    <span class="hidden sm:inline">+ Buat Akun Admin</span>
                    <span class="sm:hidden">+ Admin</span>
                </a>
                @endif

                {{-- Quick Action: Direct Logout Button --}}
                <form method="POST" action="{{ route('admin.logout') }}" class="inline">
                    @csrf
                    <button type="submit" title="Keluar dari akun admin" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-red-600 bg-red-50 hover:bg-red-100 border border-red-200/80 transition shadow-2xs cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                        <span class="hidden sm:inline">Logout</span>
                    </button>
                </form>

                {{-- User Profile & Dropdown --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" @click.outside="open = false" type="button" class="flex items-center gap-2.5 p-1 rounded-xl hover:bg-slate-50 transition focus:outline-hidden">
                        @php
                            $adminUser = auth('admin')->user();
                            $name = $adminUser?->name ?? 'Administrator Utama';
                            $initials = collect(explode(' ', $name))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->implode('');
                        @endphp
                        <span class="w-9 h-9 rounded-full bg-[#1b2234] text-white font-black text-xs flex items-center justify-center shrink-0 shadow-2xs ring-2 ring-slate-100">
                            {{ $initials ?: 'AD' }}
                        </span>
                        <div class="text-left hidden md:block">
                            <p class="text-xs sm:text-sm font-extrabold text-slate-900 leading-tight">{{ $name }}</p>
                            <p class="text-[10px] text-slate-400 font-medium leading-tight mt-0.5">Panitia Pemilihan OSIS</p>
                        </div>
                    </button>

                    {{-- Dropdown Menu --}}
                    <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute right-0 mt-2 w-56 rounded-2xl bg-white shadow-xl border border-slate-200/90 py-2 z-50 text-xs font-semibold text-slate-700 divide-y divide-slate-100">
                        <div class="px-4 py-2.5">
                            <p class="text-slate-400 text-[10px] uppercase tracking-wider font-bold">Masuk Sebagai</p>
                            <p class="font-bold text-slate-900 text-sm truncate mt-0.5">{{ $name }}</p>
                            <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $adminUser?->role === 'superadmin' ? 'bg-indigo-50 text-indigo-700 border border-indigo-100' : 'bg-slate-100 text-slate-600' }}">
                                {{ $adminUser?->role === 'superadmin' ? 'Superadmin' : 'Admin' }}
                            </span>
                        </div>

                        <div class="py-1.5">
                            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-4 py-2 hover:bg-slate-50 hover:text-blue-600 transition {{ request()->routeIs('admin.dashboard') ? 'text-blue-600 bg-blue-50/50' : '' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                                Dashboard Utama
                            </a>
                            @if (auth('admin')->user()?->role === 'superadmin')
                            <a href="{{ route('admin.management.index') }}" class="flex items-center gap-2.5 px-4 py-2 hover:bg-slate-50 hover:text-blue-600 transition {{ request()->routeIs('admin.management.*') ? 'text-blue-600 bg-blue-50/50' : '' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                Kelola Admin
                            </a>
                            @endif
                            <a href="{{ route('admin.password.form') }}" class="flex items-center gap-2.5 px-4 py-2 hover:bg-slate-50 hover:text-blue-600 transition {{ request()->routeIs('admin.password.*') ? 'text-blue-600 bg-blue-50/50' : '' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" /></svg>
                                Ubah Password
                            </a>
                            <a href="{{ route('admin.reports.export') }}" class="flex items-center gap-2.5 px-4 py-2 hover:bg-slate-50 hover:text-blue-600 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                Export Laporan CSV
                            </a>
                        </div>

                        <div class="py-1.5">
                            <form method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2 text-red-600 hover:bg-red-50 hover:text-red-700 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                                    Keluar (Logout)
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    {{-- Main Content Area --}}
    <main class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @yield('content')
        {{ $slot ?? '' }}
    </main>

    {{-- Footer matching Design --}}
    <footer class="mt-auto py-6 border-t border-slate-200/60 bg-white/50 text-center text-xs text-slate-400">
        <p>© 2026 E-Voting OSIS SMK An-Nurmaniyah • Panitia Pemilihan Siswa Terpadu.</p>
        <p class="mt-1 text-[11px] text-slate-400/80">Dibangun dengan keamanan terenkripsi tinggi, Database Transaction Ledger, dan Audit Trail SHA-256.</p>
    </footer>

    {{-- Auto-logout on tab/browser close via Beacon API --}}
    <script>
        window.__isInternalNavigation = false;
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (link && link.href && link.href.startsWith(window.location.origin)) {
                window.__isInternalNavigation = true;
            }
        });
        document.addEventListener('submit', function() {
            window.__isInternalNavigation = true;
        });

        window.addEventListener('pagehide', function() {
            if (!window.__isInternalNavigation) {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const data = new FormData();
                if (token) data.append('_token', token);
                navigator.sendBeacon('{{ route('admin.beacon-logout') }}', data);
            }
        });
    </script>

    @livewireScripts
</body>
</html>
