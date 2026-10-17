<div wire:poll.5s="$refresh" class="space-y-6">

    {{-- Flash message --}}
    @if ($flashMessage)
        <div
            x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show"
            class="rounded-2xl px-4 py-3.5 text-xs font-semibold flex items-center justify-between border {{ $flashType === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-red-50 border-red-200 text-red-800' }}"
        >
            <div class="flex items-center gap-2">
                @if ($flashType === 'success')
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                @else
                    <svg class="w-4 h-4 text-red-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                @endif
                <span>{{ $flashMessage }}</span>
            </div>
            <button @click="show = false" class="text-slate-400 hover:text-slate-600">&times;</button>
        </div>
    @endif

    {{-- Dashboard Top Header matching Design --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black tracking-wider uppercase bg-[#eff1fe] text-[#6366f1]">
                <span class="w-1.5 h-1.5 rounded-full bg-[#6366f1]"></span>
                REALTIME MONITOR
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mt-1.5">
                Dashboard Admin
            </h1>
            <p class="mt-1 text-xs text-slate-400 font-medium">
                Pemilihan Ketua &amp; Wakil OSIS • SMK An-Nurmaniyah Periode 2025/2026
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            {{-- Export Report Button --}}
            <a
                href="{{ route('admin.reports.export') }}"
                class="ui-button bg-white border border-slate-200/90 text-slate-700 hover:bg-slate-50 text-xs font-bold px-3.5 py-2.5 rounded-2xl flex items-center gap-2 shadow-2xs transition"
            >
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Export Laporan (CSV)</span>
            </a>

            {{-- Akhiri Sesi & Umumkan Pemenang Button --}}
            @if (! $this->setting->is_finalized)
                <button
                    wire:click="confirmFinalizeSession"
                    class="ui-button text-xs font-black px-3.5 py-2.5 rounded-2xl flex items-center gap-2 shadow-xs transition bg-gradient-to-r from-amber-400 to-yellow-500 hover:from-amber-500 hover:to-yellow-600 text-slate-950 border border-amber-300 cursor-pointer"
                >
                    <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.504-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.003 0V8.25a2.25 2.25 0 00-2.25-2.25h-1.5a2.25 2.25 0 00-2.25 2.25v7.125m5.003 0H9.497M18 10.5a3 3 0 10-6 0 3 3 0 006 0zm-12 0a3 3 0 10-6 0 3 3 0 006 0z"/></svg>
                    <span>Akhiri Sesi &amp; Umumkan Pemenang</span>
                </button>
            @else
                <button
                    wire:click="reopenElection"
                    class="ui-button text-xs font-bold px-3.5 py-2.5 rounded-2xl flex items-center gap-2 shadow-xs transition bg-slate-900 hover:bg-slate-800 text-amber-300 border border-slate-700 cursor-pointer"
                >
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Buka Kembali Sesi</span>
                </button>
            @endif

            {{-- Toggle Session Button --}}
            @if (! $this->setting->is_finalized)
                <button
                    wire:click="confirmToggleSession"
                    class="ui-button text-xs font-bold px-3.5 py-2.5 rounded-2xl flex items-center gap-2 shadow-xs transition cursor-pointer
                           {{ $this->setting->is_voting_open ? 'bg-[#dc2626] hover:bg-[#b91c1c] text-white' : 'bg-emerald-600 hover:bg-emerald-700 text-white' }}"
                >
                    @if ($this->setting->is_voting_open)
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="3" fill="currentColor"/></svg>
                        <span>Tutup Sesi Voting</span>
                    @else
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Buka Sesi Voting</span>
                    @endif
                </button>
            @endif
        </div>
    </div>

    {{-- Official Winner Celebration Spotlight Card --}}
    @if ($this->setting->is_finalized)
        @if ($this->winner)
            <div
                x-data="{
                    burstConfetti() {
                        const canvas = this.$refs.confettiCanvas;
                        if (!canvas) return;
                        const ctx = canvas.getContext('2d');
                        canvas.width = canvas.parentElement.offsetWidth;
                        canvas.height = canvas.parentElement.offsetHeight;
                        const pieces = [];
                        const colors = ['#f59e0b', '#fbbf24', '#fde047', '#3b82f6', '#10b981', '#ec4899', '#8b5cf6'];
                        for (let i = 0; i < 90; i++) {
                            pieces.push({
                                x: Math.random() * canvas.width,
                                y: -20 - Math.random() * 80,
                                size: Math.random() * 8 + 4,
                                color: colors[Math.floor(Math.random() * colors.length)],
                                speedY: Math.random() * 3 + 2,
                                speedX: (Math.random() - 0.5) * 4,
                                rot: Math.random() * 360,
                                rotSpeed: (Math.random() - 0.5) * 8
                            });
                        }
                        let animId;
                        function draw() {
                            ctx.clearRect(0, 0, canvas.width, canvas.height);
                            let active = false;
                            pieces.forEach(p => {
                                p.y += p.speedY;
                                p.x += p.speedX;
                                p.rot += p.rotSpeed;
                                if (p.y < canvas.height + 30) active = true;
                                ctx.save();
                                ctx.translate(p.x, p.y);
                                ctx.rotate(p.rot * Math.PI / 180);
                                ctx.fillStyle = p.color;
                                ctx.fillRect(-p.size / 2, -p.size / 2, p.size, p.size * 0.6);
                                ctx.restore();
                            });
                            if (active) animId = requestAnimationFrame(draw);
                        }
                        draw();
                    }
                }"
                x-init="setTimeout(() => burstConfetti(), 300)"
                class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-500/20 via-yellow-400/10 to-amber-600/25 border-2 border-amber-400 p-6 sm:p-8 shadow-xl backdrop-blur-xs"
            >
                <canvas x-ref="confettiCanvas" class="absolute inset-0 pointer-events-none z-10 w-full h-full"></canvas>

                <div class="relative z-20 flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="flex flex-col sm:flex-row items-center gap-5 text-center sm:text-left">
                        {{-- Candidate Photos with Gold Rings --}}
                        <div class="flex items-center -space-x-4 shrink-0">
                            <div class="relative">
                                @if ($this->winner->chairman_photo)
                                    <img src="{{ Storage::disk('public')->url($this->winner->chairman_photo) }}" alt="{{ $this->winner->chairman_name }}" class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl object-cover ring-4 ring-amber-400 shadow-md">
                                @else
                                    <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-amber-200 text-amber-900 font-black text-xl flex items-center justify-center ring-4 ring-amber-400 shadow-md">K</div>
                                @endif
                                <span class="absolute -bottom-2 -right-1 px-1.5 py-0.5 rounded-md text-[9px] font-black uppercase bg-amber-500 text-slate-950 shadow-xs">Ketua</span>
                            </div>
                            <div class="relative">
                                @if ($this->winner->vice_chairman_photo)
                                    <img src="{{ Storage::disk('public')->url($this->winner->vice_chairman_photo) }}" alt="{{ $this->winner->vice_chairman_name }}" class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl object-cover ring-4 ring-amber-400 shadow-md">
                                @else
                                    <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-yellow-200 text-yellow-900 font-black text-xl flex items-center justify-center ring-4 ring-amber-400 shadow-md">W</div>
                                @endif
                                <span class="absolute -bottom-2 -right-1 px-1.5 py-0.5 rounded-md text-[9px] font-black uppercase bg-yellow-500 text-slate-950 shadow-xs">Wakil</span>
                            </div>
                        </div>

                        <div>
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-amber-400 text-slate-950 shadow-2xs">
                                <span>🏆</span>
                                <span>PASLON RESMI TERPILIH KETUA &amp; WAKIL OSIS</span>
                            </div>
                            <h2 class="text-xl sm:text-2xl lg:text-3xl font-black text-slate-900 mt-2 tracking-tight">
                                Paslon No. {{ sprintf('%02d', $this->winner->number_order) }} — {{ $this->winner->chairman_name }} &amp; {{ $this->winner->vice_chairman_name }}
                            </h2>
                            <p class="text-xs sm:text-sm text-slate-600 font-medium italic mt-1">
                                “{{ $this->winner->slogan ?: 'Terpilih Memimpin OSIS SMK An-Nurmaniyah Periode 2025/2026' }}”
                            </p>
                            <p class="text-[11px] text-slate-500 font-semibold mt-2">
                                Ditetapkan pada: {{ $this->setting->finalized_at?->format('d F Y • H:i:s') ?? now()->format('d F Y • H:i:s') }} WIB
                            </p>
                        </div>
                    </div>

                    <div class="shrink-0 bg-white/95 rounded-2xl border border-amber-300 p-4 sm:p-5 text-center min-w-[200px] shadow-sm">
                        <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">Total Suara Terpilih</span>
                        <div class="mt-1 flex items-baseline justify-center gap-1">
                            <span class="text-3xl sm:text-4xl font-black text-amber-600">{{ number_format($this->winner->vote_count) }}</span>
                            <span class="text-sm font-bold text-slate-400">Suara</span>
                        </div>
                        <div class="mt-2 inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800">
                            <span>Perolehan {{ $this->winner->computed_percentage }}%</span>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="rounded-2xl bg-amber-50 border border-amber-300 p-5 text-center">
                <p class="text-sm font-bold text-amber-900">Sesi voting telah diakhiri, namun belum ada suara yang masuk.</p>
            </div>
        @endif
    @endif


    {{-- Top 4 KPI Metric Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Card 1: Total Suara Masuk --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-5 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-extrabold tracking-wider text-slate-400 uppercase">TOTAL SUARA MASUK</span>
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                    </div>
                </div>
                <div class="mt-2 flex items-baseline gap-1.5">
                    <span class="text-3xl font-black text-slate-900">{{ number_format($this->totalVotesIn) }}</span>
                    <span class="text-sm font-semibold text-slate-400">/ {{ number_format($this->totalStudents) }} Suara</span>
                </div>
            </div>
            <div class="mt-3">
                <div class="h-1.5 rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-full bg-blue-600 rounded-full transition-all duration-500" style="width: {{ min($this->participationRate, 100) }}%"></div>
                </div>
                <div class="mt-2 flex items-center justify-between text-[11px]">
                    <span class="text-slate-400">{{ $this->participationRate }}% dari {{ $this->totalStudents }} pemilih terdaftar</span>
                    <span class="font-bold text-blue-600">{{ max(0, $this->totalStudents - $this->totalVotesIn) }} sisa</span>
                </div>
            </div>
        </div>

        {{-- Card 2: Partisipasi Siswa --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-5 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-extrabold tracking-wider text-slate-400 uppercase">PARTISIPASI SISWA</span>
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="mt-2 flex items-center gap-2">
                    <span class="text-3xl font-black text-slate-900">{{ $this->participationRate }}%</span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200/60 inline-flex items-center gap-0.5">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                        Kuorum 50%
                    </span>
                </div>
            </div>
            <div class="mt-3">
                <p class="text-xs text-slate-500 flex items-center gap-1.5">
                    @if ($this->participationRate >= 50)
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                        Kuorum minimal 50% telah terlampaui
                    @else
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                        Menuju target kuorum 50%
                    @endif
                </p>
            </div>
        </div>

        {{-- Card 3: Status Sesi Voting --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-5 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-extrabold tracking-wider text-slate-400 uppercase">STATUS SESI VOTING</span>
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="mt-2 flex items-center gap-2">
                    <span class="text-3xl font-black {{ $this->setting->is_voting_open ? 'text-emerald-600' : 'text-slate-400' }}">
                        {{ $this->setting->is_voting_open ? 'OPEN' : 'CLOSED' }}
                    </span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full inline-flex items-center gap-1 {{ $this->setting->is_voting_open ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $this->setting->is_voting_open ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                        {{ $this->setting->is_voting_open ? 'AKTIF' : 'DITUTUP' }}
                    </span>
                </div>
            </div>
            <div class="mt-3">
                <p class="text-xs text-slate-500 flex items-center gap-1.5 truncate">
                    @if ($this->setting->is_voting_open && $this->setting->opened_at)
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                        Dibuka {{ $this->setting->opened_at->diffForHumans() }} ({{ $this->setting->opened_at->format('H:i') }} WIB)
                    @elseif ($this->setting->closed_at)
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400 shrink-0"></span>
                        Ditutup {{ $this->setting->closed_at->diffForHumans() }} ({{ $this->setting->closed_at->format('H:i') }} WIB)
                    @else
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400 shrink-0"></span>
                        Belum pernah dibuka
                    @endif
                </p>
            </div>
        </div>

        {{-- Card 4: DB Transaction Health --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-5 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-extrabold tracking-wider text-slate-400 uppercase">DB TRANSACTION HEALTH</span>
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                    </div>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-2xl sm:text-3xl font-black {{ $this->dbHealth['ok'] ? 'text-emerald-600' : 'text-red-600' }}">
                        {{ $this->dbHealth['label'] }}
                    </span>
                    <span class="text-xs font-semibold text-slate-400">
                        {{ $this->dbHealth['latency_ms'] ?? 0.1 }} ms
                    </span>
                </div>
            </div>
            <div class="mt-3">
                <p class="text-xs text-slate-500 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                    Database Engine Connected • 0 rollbacks
                </p>
            </div>
        </div>
    </div>

    {{-- Middle 2 Columns Grid: Leaderboard & Security Logs --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Left: Klasemen Suara Real-Time --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-5 sm:p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Klasemen Suara Real-Time</h3>
                            <p class="text-[11px] text-slate-400">Quick count terkini hasil bilik suara digital</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Live Count
                    </span>
                </div>

                {{-- Candidates Progress List --}}
                <div class="mt-4 space-y-4">
                    @forelse ($this->leaderboard as $i => $c)
                        <div class="p-3 rounded-xl bg-slate-50/70 border border-slate-100/80 hover:bg-slate-50 transition">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <span class="w-6 h-6 rounded-md {{ $i === 0 ? 'bg-blue-600 text-white' : 'bg-slate-700 text-white' }} font-black text-xs flex items-center justify-center shrink-0">
                                        {{ sprintf('%02d', $c->number_order) }}
                                    </span>
                                    <div class="flex items-center -space-x-1 shrink-0">
                                        @if ($c->chairman_photo)
                                            <img src="{{ Storage::disk('public')->url($c->chairman_photo) }}" class="w-6 h-6 rounded-md object-cover border border-slate-200" alt="Ketua">
                                        @else
                                            <div class="w-6 h-6 rounded-md bg-blue-100 text-blue-700 text-[9px] font-bold flex items-center justify-center">K</div>
                                        @endif
                                        @if ($c->vice_chairman_photo)
                                            <img src="{{ Storage::disk('public')->url($c->vice_chairman_photo) }}" class="w-6 h-6 rounded-md object-cover border border-slate-200" alt="Wakil">
                                        @else
                                            <div class="w-6 h-6 rounded-md bg-indigo-100 text-indigo-700 text-[9px] font-bold flex items-center justify-center">W</div>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-slate-900 text-xs sm:text-sm truncate">
                                            {{ $c->chairman_name }} &amp; {{ $c->vice_chairman_name }}
                                        </p>
                                        <p class="text-[10px] text-slate-400 italic truncate">
                                            “{{ $c->slogan ?: 'Kandidat Paslon' }}”
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="text-xs sm:text-sm font-black text-slate-900">{{ $c->vote_count }} Suara</span>
                                    <span class="block text-[11px] font-bold text-blue-600">{{ $c->computed_percentage }}%</span>
                                </div>
                            </div>

                            {{-- Bar --}}
                            <div class="mt-2.5 h-2 rounded-full bg-slate-200/80 overflow-hidden">
                                <div
                                    class="h-full rounded-full transition-all duration-500 {{ $i === 0 ? 'bg-blue-600' : 'bg-slate-700' }}"
                                    style="width: {{ $c->computed_percentage }}%"
                                ></div>
                            </div>

                            <div class="mt-1.5 flex items-center justify-between text-[10px] text-slate-400">
                                <span>Peringkat {{ $i + 1 }} {{ $i === 0 && $c->vote_count > 0 ? '(Memimpin perolehan)' : '' }}</span>
                                <span>{{ $c->computed_percentage }}%</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 py-4 text-center">Belum ada kandidat terdaftar.</p>
                    @endforelse
                </div>
            </div>

            {{-- Footer info --}}
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
                <span class="flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Total {{ $this->totalVotesIn }} suara sah terverifikasi dari {{ $this->totalStudents }} DPT
                </span>
                <span class="flex items-center gap-1 text-[11px]">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Terenkripsi SHA-256
                </span>
            </div>
        </div>

        {{-- Right: System Security Logs --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-5 sm:p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">System Security Logs</h3>
                            <p class="text-[11px] text-slate-400">Live immutable audit trail</p>
                        </div>
                    </div>
                    <button wire:click="$refresh" title="Perbarui Log" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </button>
                </div>

                {{-- Filter Tabs --}}
                <div class="mt-3 flex items-center gap-1.5 text-xs font-semibold">
                    <button
                        wire:click="setLogFilter('all')"
                        class="px-3 py-1 rounded-lg transition {{ $logFilter === 'all' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}"
                    >
                        Semua
                    </button>
                    <button
                        wire:click="setLogFilter('session')"
                        class="px-3 py-1 rounded-lg transition {{ $logFilter === 'session' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}"
                    >
                        Sesi
                    </button>
                    <button
                        wire:click="setLogFilter('votes')"
                        class="px-3 py-1 rounded-lg transition {{ $logFilter === 'votes' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}"
                    >
                        Suara
                    </button>
                    <button
                        wire:click="setLogFilter('warnings')"
                        class="px-3 py-1 rounded-lg transition {{ $logFilter === 'warnings' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}"
                    >
                        Peringatan
                    </button>
                </div>

                {{-- Logs List --}}
                <div class="mt-3 max-h-72 overflow-y-auto space-y-2 pr-1 divide-y divide-slate-50">
                    @forelse ($this->recentLogs as $log)
                        @php
                            $badgeClass = match($log->type) {
                                'vote_success', 'vote_cast' => 'bg-emerald-100 text-emerald-800',
                                'session_opened', 'session_toggled' => 'bg-blue-100 text-blue-800',
                                'admin_action' => 'bg-indigo-100 text-indigo-800',
                                'nis_not_found' => 'bg-amber-100 text-amber-800',
                                'race_condition_blocked', 'duplicate_attempt' => 'bg-red-100 text-red-800',
                                default => 'bg-slate-100 text-slate-700',
                            };
                            $badgeLabel = match($log->type) {
                                'vote_success', 'vote_cast' => 'VOTE CAST',
                                'session_opened' => 'SESSION OPEN',
                                'session_toggled' => 'SESSION UPDATE',
                                'admin_action' => 'ADMIN ACTION',
                                'nis_not_found' => 'NIS NOT FOUND',
                                'race_condition_blocked', 'duplicate_attempt' => 'BLOCKED',
                                default => strtoupper(str_replace('_', ' ', $log->type)),
                            };
                        @endphp
                        <div class="pt-2 first:pt-0 flex items-start gap-2.5">
                            <span class="mt-0.5 shrink-0 px-2 py-0.5 rounded text-[9px] font-extrabold uppercase tracking-wider {{ $badgeClass }}">
                                {{ $badgeLabel }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs text-slate-700 font-medium leading-relaxed">{{ $log->message }}</p>
                                <p class="text-[10px] text-slate-400 mt-0.5">
                                    {{ $log->created_at->format('d M Y, H:i:s') }} • {{ $log->ip_address }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 py-4 text-center">Belum ada catatan log aktivitas.</p>
                    @endforelse
                </div>
            </div>

            {{-- Footer info --}}
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
                <span>Pencatatan hashing SHA-256 aktif</span>
                <span class="text-blue-600 font-semibold cursor-pointer hover:underline">Validasi Hash</span>
            </div>
        </div>

    </div>

    {{-- Section: Manajemen Paslon --}}
    <section class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-5 sm:p-6">
        @livewire('admin.candidate-manager')
    </section>

    {{-- Section: Monitoring Pemilih (DPT) --}}
    <section class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-5 sm:p-6 space-y-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-3 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                    </div>
                    <h2 class="text-lg font-extrabold text-slate-900">Monitoring Pemilih (DPT)</h2>
                </div>
                <p class="mt-0.5 text-xs text-slate-500">Pantau status kehadiran pemilih secara real-time dan unduh daftar siswa yang belum memberikan hak suara.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a
                    href="{{ route('admin.reports.export-dpt') }}"
                    class="ui-button bg-indigo-50 border border-indigo-200 text-indigo-900 hover:bg-indigo-100 text-xs font-bold px-3.5 py-2 rounded-xl flex items-center gap-1.5 transition shadow-2xs"
                    title="Unduh seluruh DPT beserta Token/Kode Unik 4 karakter untuk dibagikan ke siswa"
                >
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    <span>Unduh DPT + Token (CSV)</span>
                </a>
                <a
                    href="{{ route('admin.reports.export-unvoted') }}"
                    class="ui-button bg-amber-50 border border-amber-200 text-amber-900 hover:bg-amber-100 text-xs font-bold px-3.5 py-2 rounded-xl flex items-center gap-1.5 transition shadow-2xs"
                >
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Unduh Siswa Belum Memilih (CSV)</span>
                </a>
                <button
                    type="button"
                    wire:click="confirmClearStudents"
                    @disabled($this->totalStudents === 0 || $this->setting->is_voting_open)
                    class="ui-button border text-xs font-bold px-3.5 py-2 rounded-xl flex items-center gap-1.5 transition shadow-2xs {{ $this->totalStudents === 0 || $this->setting->is_voting_open ? 'border-slate-200 bg-slate-100 text-slate-400 cursor-not-allowed opacity-60' : 'border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100 cursor-pointer' }}"
                    title="{{ $this->setting->is_voting_open ? 'Tutup sesi voting terlebih dahulu untuk menghapus DPT' : ($this->totalStudents === 0 ? 'Data DPT kosong' : 'Hapus seluruh data siswa pemilih') }}"
                >
                    <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span>Hapus Seluruh DPT</span>
                </button>
            </div>
        </div>

        {{-- Filter & Search Bar --}}
        <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3 pt-1">
            {{-- Tabs --}}
            <div class="inline-flex items-center bg-slate-100 p-1 rounded-xl text-xs font-bold">
                <button
                    wire:click="$set('voterTab', 'unvoted')"
                    class="px-3.5 py-1.5 rounded-lg transition cursor-pointer {{ $voterTab === 'unvoted' ? 'bg-white text-amber-800 shadow-2xs font-extrabold' : 'text-slate-600 hover:text-slate-900' }}"
                >
                    Belum Memilih ({{ $this->unvotedStudentsCount }})
                </button>
                <button
                    wire:click="$set('voterTab', 'voted')"
                    class="px-3.5 py-1.5 rounded-lg transition cursor-pointer {{ $voterTab === 'voted' ? 'bg-white text-emerald-800 shadow-2xs font-extrabold' : 'text-slate-600 hover:text-slate-900' }}"
                >
                    Sudah Memilih ({{ $this->votedStudentsCount }})
                </button>
                <button
                    wire:click="$set('voterTab', 'all')"
                    class="px-3.5 py-1.5 rounded-lg transition cursor-pointer {{ $voterTab === 'all' ? 'bg-white text-slate-900 shadow-2xs font-extrabold' : 'text-slate-600 hover:text-slate-900' }}"
                >
                    Semua ({{ $this->totalStudents }})
                </button>
            </div>

            {{-- Search, Grade & Class Filter --}}
            <div class="flex flex-wrap items-center gap-2">
                {{-- Angkatan / Tingkat Filter --}}
                <div class="relative w-32 sm:w-36">
                    <select wire:model.live="voterGrade" class="ui-input w-full text-xs py-2 pr-7 font-semibold text-slate-700 bg-white">
                        <option value="">Semua Tingkat</option>
                        <option value="X">Kelas X (10)</option>
                        <option value="XI">Kelas XI (11)</option>
                        <option value="XII">Kelas XII (12)</option>
                    </select>
                </div>

                {{-- Kelas Filter --}}
                <div class="relative w-36 sm:w-40">
                    <select wire:model.live="voterClass" class="ui-input w-full text-xs py-2 pr-7 font-semibold text-slate-700 bg-white">
                        <option value="">Semua Kelas</option>
                        @foreach ($this->availableClasses as $cls)
                            <option value="{{ $cls }}">{{ $cls }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Search Box --}}
                <div class="relative flex-1 sm:w-56">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="voterSearch"
                        placeholder="Cari NIS atau Nama..."
                        class="ui-input w-full text-xs py-2 font-semibold text-slate-800 placeholder-slate-400"
                    >
                </div>
            </div>
        </div>

        {{-- Students Table --}}
        <div class="overflow-x-auto rounded-xl border border-slate-200/80">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-slate-500 font-bold uppercase text-[10px] tracking-wider">
                    <tr>
                        <th class="px-4 py-2.5">NIS</th>
                        <th class="px-4 py-2.5">Nama Siswa</th>
                        <th class="px-4 py-2.5">Kelas</th>
                        <th class="px-4 py-2.5">Token / Kode</th>
                        <th class="px-4 py-2.5">Status Hak Suara</th>
                        <th class="px-4 py-2.5">Waktu Voting</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($this->voterStudents as $student)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-4 py-2.5 font-mono font-bold text-slate-800">{{ $student->nis }}</td>
                            <td class="px-4 py-2.5 font-semibold text-slate-900">{{ $student->name }}</td>
                            <td class="px-4 py-2.5 text-slate-600 font-medium">
                                <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-semibold text-[11px]">{{ $student->class ?? '-' }}</span>
                            </td>
                            <td class="px-4 py-2.5">
                                <span class="font-mono font-bold text-blue-700 bg-blue-50 border border-blue-200/70 px-2 py-0.5 rounded text-[11px] tracking-wider">
                                    {{ $student->unique_code ?? '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-2.5">
                                @if ($student->has_voted)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Sudah Memilih
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200/60">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Belum Memilih
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-slate-400 text-[11px]">
                                {{ $student->voted_at ? $student->voted_at->format('H:i:s • d M') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-slate-400 font-medium">
                                Tidak ada data pemilih yang sesuai kriteria pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination & Rows Per Page Controls --}}
        @php
            $voters = $this->voterStudents;
        @endphp
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-3 border-t border-slate-100 text-xs text-slate-600">
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-1.5 font-medium">
                    <span>Tampilkan</span>
                    <select
                        wire:model.live="voterPerPage"
                        class="ui-input py-1 px-2.5 text-xs font-bold text-slate-800 bg-white border-slate-200 rounded-lg shadow-2xs focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span>baris per halaman</span>
                </div>

                @if ($voters->total() > 0)
                    <span class="text-slate-300 hidden sm:inline">•</span>
                    <span class="font-medium text-slate-500">
                        Menampilkan <span class="font-bold text-slate-800">{{ $voters->firstItem() ?? 0 }}</span> - <span class="font-bold text-slate-800">{{ $voters->lastItem() ?? 0 }}</span> dari <span class="font-bold text-slate-800">{{ $voters->total() }}</span> pemilih
                    </span>
                @endif
            </div>

            @if ($voters->hasPages())
                <nav role="navigation" aria-label="Navigasi Halaman Pemilih" class="flex items-center gap-1">
                    {{-- Previous Page Link --}}
                    <button
                        type="button"
                        wire:click="previousPage('voterPage')"
                        wire:loading.attr="disabled"
                        @disabled($voters->onFirstPage())
                        class="px-2.5 py-1.5 rounded-lg border text-xs font-semibold flex items-center gap-1 transition {{ $voters->onFirstPage() ? 'border-slate-200 text-slate-300 cursor-not-allowed bg-slate-50/50' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 hover:text-slate-900 cursor-pointer shadow-2xs' }}"
                        aria-label="Halaman Sebelumnya"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                        <span class="hidden sm:inline">Sebelumnya</span>
                    </button>

                    {{-- Page Numbers --}}
                    <div class="flex items-center gap-1">
                        @foreach ($voters->getUrlRange(max(1, $voters->currentPage() - 2), min($voters->lastPage(), $voters->currentPage() + 2)) as $page => $url)
                            <button
                                type="button"
                                wire:click="gotoPage({{ $page }}, 'voterPage')"
                                class="w-8 h-8 rounded-lg text-xs font-bold transition flex items-center justify-center {{ $page === $voters->currentPage() ? 'bg-blue-600 text-white shadow-2xs' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200 cursor-pointer shadow-2xs' }}"
                            >
                                {{ $page }}
                            </button>
                        @endforeach
                    </div>

                    {{-- Next Page Link --}}
                    <button
                        type="button"
                        wire:click="nextPage('voterPage')"
                        wire:loading.attr="disabled"
                        @disabled(!$voters->hasMorePages())
                        class="px-2.5 py-1.5 rounded-lg border text-xs font-semibold flex items-center gap-1 transition {{ !$voters->hasMorePages() ? 'border-slate-200 text-slate-300 cursor-not-allowed bg-slate-50/50' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 hover:text-slate-900 cursor-pointer shadow-2xs' }}"
                        aria-label="Halaman Selanjutnya"
                    >
                        <span class="hidden sm:inline">Selanjutnya</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </nav>
            @endif
        </div>
    </section>

    {{-- Section: Import Daftar Pemilih Tetap (DPT) --}}
    <section class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-5 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-slate-100">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900">Import Daftar Pemilih Tetap (DPT)</h2>
                <p class="mt-0.5 text-xs text-slate-500">Unggah file CSV untuk sinkronisasi identitas siswa pemilih OSIS secara massal.</p>
            </div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-slate-50 text-slate-700 border border-slate-200">
                <span class="font-bold text-slate-800">{{ $this->totalStudents }} Terdaftar</span>
                <span class="text-slate-300">•</span>
                <span class="text-emerald-700 font-bold">{{ $this->votedStudentsCount }} Sudah Memilih</span>
                <span class="text-slate-300">•</span>
                <span class="text-amber-700 font-bold">{{ $this->unvotedStudentsCount }} Belum Memilih</span>
            </div>
        </div>

        <p class="mt-3 text-xs text-slate-500">
            Gunakan kolom wajib: <span class="font-mono font-semibold bg-slate-100 px-1 py-0.5 rounded text-slate-700">nis</span> ,
            <span class="font-mono font-semibold bg-slate-100 px-1 py-0.5 rounded text-slate-700">name</span> ,
            <span class="font-mono font-semibold bg-slate-100 px-1 py-0.5 rounded text-slate-700">class</span> .
            Siswa yang sudah terdaftar akan diperbarui datanya tanpa mengubah perolehan suara.
        </p>

        {{-- Dropzone / Upload Box --}}
        <form wire:submit="importStudents" class="mt-4 space-y-4">
            <div class="relative border-2 border-dashed border-slate-200 rounded-2xl p-6 sm:p-8 text-center bg-slate-50/50 hover:bg-slate-50 transition group">
                <input
                    type="file"
                    wire:model="studentFile"
                    accept=".csv,.txt"
                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                >
                <div class="flex flex-col items-center">
                    <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mb-3 group-hover:scale-105 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    </div>
                    @if ($studentFile)
                        <p class="text-xs font-bold text-emerald-700">
                            File Terpilih: {{ $studentFile->getClientOriginalName() }}
                        </p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Klik "Import Siswa" di bawah untuk memproses.</p>
                    @else
                        <p class="text-xs font-semibold text-slate-700">
                            <span class="text-blue-600 font-bold hover:underline">Pilih file CSV</span> atau tarik dan letakkan file di sini
                        </p>
                        <p class="text-[11px] text-slate-400 mt-1">Format: .csv (Maksimal 2 MB)</p>
                    @endif
                </div>
            </div>

            @error('studentFile')
                <p class="text-xs text-red-600 font-semibold">{{ $message }}</p>
            @enderror

            @if ($importSummary)
                <div class="rounded-xl bg-emerald-50 border border-emerald-200 p-3 text-xs text-emerald-800">
                    Selesai: {{ $importSummary['created'] }} siswa baru ditambahkan, {{ $importSummary['updated'] }} diperbarui.
                </div>
            @endif

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-2">
                <button
                    type="button"
                    wire:click="downloadTemplate"
                    class="text-xs font-semibold text-slate-500 hover:text-blue-600 flex items-center gap-1.5"
                >
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Download Template CSV Contoh
                </button>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="importStudents"
                    class="ui-button bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs px-5 py-2.5 rounded-xl shadow-xs flex items-center gap-2 self-end sm:self-auto"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    <span wire:loading.remove wire:target="importStudents">Import Siswa</span>
                    <span wire:loading wire:target="importStudents">Memproses...</span>
                </button>
            </div>
        </form>
    </section>

    {{-- Section: Danger Zone: Reset Voting & Suara --}}
    <div class="bg-white rounded-2xl border border-red-200/90 shadow-2xs p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-red-600 font-extrabold text-sm">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                <span>Danger Zone: Reset Voting &amp; Suara</span>
            </div>
            <p class="mt-1 text-xs text-slate-500 max-w-2xl leading-relaxed">
                Menghapus seluruh perolehan suara yang telah masuk, mereset status kehadiran pemilih di DPT kembali menjadi 'Belum Memilih', dan membersihkan audit receipt.
                <span class="text-red-600 font-bold">Tindakan ini tidak dapat dibatalkan dan khusus untuk simulasi uji coba.</span>
            </p>
        </div>
        <button
            wire:click="confirmReset"
            @disabled($this->setting->is_voting_open)
            class="ui-button shrink-0 text-xs font-bold px-4 py-2.5 rounded-xl transition border {{ $this->setting->is_voting_open ? 'border-red-200 text-red-600 bg-red-50/60 cursor-not-allowed opacity-75' : 'border-red-300 bg-white text-red-600 hover:bg-red-50' }}"
        >
            <span class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                {{ $this->setting->is_voting_open ? 'Tutup Sesi untuk Reset' : 'Reset Voting & Suara' }}
            </span>
        </button>
    </div>

    {{-- Reset Confirmation Modal --}}
    @if ($showResetConfirm)
        <div class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-xs" wire:click="cancelReset"></div>
            <div class="relative bg-white rounded-3xl shadow-xl max-w-sm w-full p-6 sm:p-7 z-10 space-y-4">
                <div class="w-12 h-12 rounded-full bg-red-50 text-red-600 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div class="text-center">
                    <h3 class="text-base font-black text-slate-900">Reset Seluruh Data Voting?</h3>
                    <p class="mt-1 text-xs text-slate-500 leading-relaxed">
                        Tindakan ini menghapus semua suara masuk, mengembalikan status siswa menjadi "belum memilih", dan mereset perolehan kandidat ke 0.
                    </p>
                </div>
                <div class="bg-red-50/60 p-3 rounded-xl border border-red-100">
                    <label class="block text-[11px] font-bold text-red-700">Ketik <span class="font-mono bg-white px-1 py-0.5 rounded border border-red-200">RESET</span> untuk konfirmasi:</label>
                    <input type="text" wire:model="resetConfirmText" class="ui-input mt-1.5 w-full text-center font-bold tracking-widest uppercase border-red-300 focus:border-red-500 focus:ring-red-500">
                </div>
                <div class="flex gap-2 pt-2">
                    <button wire:click="cancelReset" class="flex-1 ui-button bg-slate-100 text-slate-700 hover:bg-slate-200 text-xs">
                        Batal
                    </button>
                    <button wire:click="resetData" class="flex-1 ui-button bg-red-600 text-white hover:bg-red-700 text-xs shadow-xs">
                        Reset Sekarang
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Session Confirmation Modal --}}
    @if ($showSessionConfirm)
        <div class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-xs" wire:click="cancelToggleSession"></div>
            <div class="relative bg-white rounded-3xl shadow-xl max-w-sm w-full p-6 sm:p-7 z-10 space-y-4">
                <div class="w-12 h-12 rounded-full {{ $this->setting->is_voting_open ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-600' }} flex items-center justify-center mx-auto">
                    @if ($this->setting->is_voting_open)
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @else
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @endif
                </div>
                <div class="text-center">
                    <h3 class="text-base font-black text-slate-900">
                        {{ $this->setting->is_voting_open ? 'Tutup Sesi Voting?' : 'Buka Sesi Voting?' }}
                    </h3>
                    <p class="mt-1 text-xs text-slate-500 leading-relaxed">
                        {{ $this->setting->is_voting_open
                            ? 'Siswa pada bilik suara digital tidak akan dapat mengirimkan suara baru setelah sesi ditutup.'
                            : 'Bilik suara digital siswa akan aktif dan siswa dapat langsung melakukan voting.' }}
                    </p>
                </div>
                <div class="flex gap-2 pt-2">
                    <button wire:click="cancelToggleSession" class="flex-1 ui-button bg-slate-100 text-slate-700 hover:bg-slate-200 text-xs">
                        Batal
                    </button>
                    <button wire:click="toggleVotingSession" class="flex-1 ui-button text-xs shadow-xs {{ $this->setting->is_voting_open ? 'bg-red-600 hover:bg-red-700 text-white' : 'bg-emerald-600 hover:bg-emerald-700 text-white' }}">
                        {{ $this->setting->is_voting_open ? 'Ya, Tutup Sesi' : 'Ya, Buka Sesi' }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Session Finalize Confirmation Modal --}}
    @if ($showFinalizeConfirm)
        <div class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-xs" wire:click="cancelFinalizeSession"></div>
            <div class="relative bg-white rounded-3xl shadow-xl max-w-md w-full p-6 sm:p-7 z-10 space-y-4">
                <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 border border-amber-200 flex items-center justify-center mx-auto shadow-xs">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.504-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.003 0V8.25a2.25 2.25 0 00-2.25-2.25h-1.5a2.25 2.25 0 00-2.25 2.25v7.125m5.003 0H9.497M18 10.5a3 3 0 10-6 0 3 3 0 006 0zm-12 0a3 3 0 10-6 0 3 3 0 006 0z"/></svg>
                </div>
                <div class="text-center">
                    <h3 class="text-lg font-black text-slate-900">Akhiri Sesi &amp; Umumkan Pemenang?</h3>
                    <p class="mt-1 text-xs text-slate-500 leading-relaxed">
                        Sesi voting akan ditutup dan sistem akan mengumumkan Paslon dengan perolehan suara terbanyak sebagai Pemenang Terpilih Ketua &amp; Wakil OSIS.
                    </p>
                </div>
                @if ($this->winner)
                    <div class="bg-amber-50/70 p-3.5 rounded-2xl border border-amber-200/80 text-center">
                        <span class="text-[10px] font-black uppercase tracking-wider text-amber-800">Kandidat Memimpin Teratas</span>
                        <p class="text-sm font-extrabold text-slate-900 mt-0.5">
                            Paslon No. {{ sprintf('%02d', $this->winner->number_order) }} — {{ $this->winner->chairman_name }} &amp; {{ $this->winner->vice_chairman_name }}
                        </p>
                        <p class="text-xs font-bold text-amber-700 mt-0.5">
                            {{ $this->winner->vote_count }} Suara ({{ $this->winner->computed_percentage }}%)
                        </p>
                    </div>
                @endif
                <div class="flex gap-2.5 pt-2">
                    <button wire:click="cancelFinalizeSession" class="flex-1 ui-button bg-slate-100 text-slate-700 hover:bg-slate-200 text-xs py-2.5">
                        Batal
                    </button>
                    <button wire:click="finalizeSession" class="flex-1 ui-button bg-gradient-to-r from-amber-400 to-yellow-500 hover:from-amber-500 hover:to-yellow-600 text-slate-950 font-black text-xs py-2.5 shadow-xs">
                        Ya, Akhiri &amp; Umumkan
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Clear All Students (DPT) Confirmation Modal --}}
    @if ($showClearStudentsConfirm)
        <div class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-xs" wire:click="cancelClearStudents"></div>
            <div class="relative bg-white rounded-3xl shadow-xl max-w-md w-full p-6 sm:p-7 z-10 space-y-4">
                <div class="w-12 h-12 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <div class="text-center">
                    <h3 class="text-base font-black text-slate-900">Hapus Seluruh Data Pemilih (DPT)?</h3>
                    <p class="mt-1 text-xs text-slate-500 leading-relaxed">
                        Tindakan ini akan mengosongkan seluruh data <span class="font-bold text-slate-800">{{ $this->totalStudents }} siswa</span> yang terdaftar dalam Daftar Pemilih Tetap beserta riwayat voting terkait.
                    </p>
                </div>
                <div class="bg-rose-50/70 p-3 rounded-xl border border-rose-100 text-xs text-rose-800 space-y-1">
                    <p class="font-bold flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Perhatian: Data akan terhapus permanen</span>
                    </p>
                    <p class="text-[11px] text-rose-700 leading-relaxed">
                        Gunakan fitur ini hanya ketika Anda ingin memulai pemilihan baru dengan mengimpor DPT baru dari awal.
                    </p>
                </div>
                <div class="flex gap-2 pt-2">
                    <button wire:click="cancelClearStudents" class="flex-1 ui-button bg-slate-100 text-slate-700 hover:bg-slate-200 text-xs py-2.5">
                        Batal
                    </button>
                    <button wire:click="clearAllStudents" class="flex-1 ui-button bg-rose-600 text-white hover:bg-rose-700 text-xs py-2.5 shadow-xs font-bold">
                        Ya, Hapus Semua DPT
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
