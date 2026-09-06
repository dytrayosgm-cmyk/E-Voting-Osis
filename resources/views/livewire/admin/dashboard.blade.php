<div wire:poll.5s="$refresh" class="space-y-7">

    {{-- Flash message --}}
    @if ($flashMessage)
        <div
            x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show"
            class="rounded-lg px-4 py-3 text-base {{ $flashType === 'success' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}"
        >
            {{ $flashMessage }}
        </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
            <p class="mt-1 text-base text-gray-500">{{ $this->setting->session_name }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.reports.export') }}"
               class="ui-button border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">
                Export Laporan (CSV)
            </a>
            <button
                wire:click="confirmToggleSession"
                class="ui-button
                       {{ $this->setting->is_voting_open ? 'bg-red-600 hover:bg-red-500' : 'bg-indigo-600 hover:bg-indigo-500' }}"
            >
                {{ $this->setting->is_voting_open ? 'Tutup Sesi Voting' : 'Buka Sesi Voting' }}
            </button>
        </div>
    </div>

    {{-- Stat cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl shadow-sm p-5">
            <p class="text-sm font-semibold text-gray-500">Total Suara Masuk</p>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($this->totalVotesIn) }}</p>
            <p class="mt-1 text-sm text-gray-400">dari {{ number_format($this->totalStudents) }} pemilih terdaftar</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm p-5">
            <p class="text-sm font-semibold text-gray-500">Partisipasi</p>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $this->participationRate }}%</p>
            <div class="mt-2 h-1.5 rounded-full bg-gray-100 overflow-hidden">
                <div class="h-full bg-indigo-600" style="width: {{ min($this->participationRate, 100) }}%"></div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm p-5">
            <p class="text-sm font-semibold text-gray-500">Status Sesi Voting</p>
            <p class="mt-2 text-3xl font-bold {{ $this->setting->is_voting_open ? 'text-green-600' : 'text-gray-400' }}">
                {{ $this->setting->is_voting_open ? 'OPEN' : 'CLOSED' }}
            </p>
            <p class="mt-1 text-sm text-gray-400">
                @if ($this->setting->is_voting_open && $this->setting->opened_at)
                    Dibuka {{ $this->setting->opened_at->diffForHumans() }}
                @elseif ($this->setting->closed_at)
                    Ditutup {{ $this->setting->closed_at->diffForHumans() }}
                @else
                    Belum pernah dibuka
                @endif
            </p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm p-5">
            <p class="text-sm font-semibold text-gray-500">DB Transaction Health</p>
            <p class="mt-2 text-3xl font-bold {{ $this->dbHealth['ok'] ? 'text-green-600' : 'text-red-600' }}">
                {{ $this->dbHealth['label'] }}
            </p>
            <p class="mt-1 text-sm text-gray-400">
                {{ $this->dbHealth['ok'] ? $this->dbHealth['latency_ms'] . ' ms latency' : 'Cek koneksi MySQL' }}
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        {{-- Leaderboard --}}
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm p-5">
            <h2 class="text-base font-bold text-gray-900">Klasemen Suara</h2>
            <div class="mt-4 space-y-4">
                @forelse ($this->leaderboard as $i => $c)
                    <div>
                        <div class="flex items-center justify-between text-base">
                            <span class="font-medium text-gray-800 truncate pr-2">
                                {{ $i === 0 && $c->vote_count > 0 ? '🏆 ' : '' }}No. {{ $c->number_order }} — {{ $c->pair_name }}
                            </span>
                            <span class="text-gray-500 shrink-0 font-medium">{{ $c->vote_count }} suara</span>
                        </div>
                        <div class="mt-1.5 h-2 rounded-full bg-gray-100 overflow-hidden">
                            <div class="h-full bg-indigo-600 transition-all" style="width: {{ $c->computed_percentage }}%"></div>
                        </div>
                        <p class="mt-1 text-sm text-gray-400">{{ $c->computed_percentage }}%</p>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">Belum ada kandidat.</p>
                @endforelse
            </div>
        </div>

        {{-- Security Logs --}}
        <div class="lg:col-span-3 bg-white rounded-2xl shadow-sm p-5">
            <h2 class="text-base font-bold text-gray-900">System Security Logs</h2>
            <div class="mt-4 max-h-96 overflow-y-auto space-y-2">
                @forelse ($this->recentLogs as $log)
                    <div class="flex items-start gap-3 py-2 border-b border-gray-50 last:border-0">
                        @php
                            $badge = match($log->type) {
                                'vote_success' => 'bg-green-50 text-green-700',
                                'race_condition_blocked', 'duplicate_attempt' => 'bg-red-50 text-red-700',
                                'nis_not_found' => 'bg-amber-50 text-amber-700',
                                default => 'bg-gray-100 text-gray-600',
                            };
                        @endphp
                        <span class="mt-0.5 shrink-0 text-[10px] font-medium px-2 py-0.5 rounded-full {{ $badge }}">
                            {{ str_replace('_', ' ', $log->type) }}
                        </span>
                        <div class="min-w-0">
                            <p class="text-sm text-gray-700">{{ $log->message }}</p>
                            <p class="text-xs text-gray-400">{{ $log->created_at->format('d M Y, H:i:s') }} · {{ $log->ip_address }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">Belum ada aktivitas.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Candidate management --}}
    <section class="bg-white rounded-2xl shadow-sm p-5">
        <div class="mb-4">
            <h2 class="text-lg font-bold text-gray-900">Manajemen Paslon</h2>
            <p class="mt-1 text-sm text-gray-500">Tambah, edit, dan kelola identitas paslon langsung dari Dashboard.</p>
        </div>
        @livewire('admin.candidate-manager')
    </section>

    {{-- Student import --}}
    <section class="bg-white rounded-2xl shadow-sm p-5">
        <div class="mb-4">
            <h2 class="text-lg font-bold text-gray-900">Import Daftar Pemilih</h2>
            <p class="mt-1 text-sm text-gray-500">Unggah CSV untuk menambahkan banyak siswa sekaligus. Gunakan kolom: <span class="font-mono">nis,name,class</span>. NIS yang sudah ada akan diperbarui tanpa mengubah status suaranya.</p>
        </div>

        <form wire:submit="importStudents" class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="flex-1">
                <label for="studentFile" class="block text-sm font-semibold text-gray-700">File CSV</label>
                <input id="studentFile" type="file" wire:model="studentFile" accept=".csv,.txt" class="mt-1.5 block w-full rounded-lg border border-gray-300 bg-white text-sm text-gray-700 file:mr-3 file:border-0 file:bg-gray-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-gray-700">
                @error('studentFile')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" wire:loading.attr="disabled" class="ui-button bg-indigo-600 text-white hover:bg-indigo-500">
                <span wire:loading.remove wire:target="importStudents">Import Siswa</span>
                <span wire:loading wire:target="importStudents">Memproses...</span>
            </button>
        </form>

        @if ($importSummary)
            <p class="mt-3 text-sm text-green-700">Terakhir: {{ $importSummary['created'] }} ditambahkan, {{ $importSummary['updated'] }} diperbarui.</p>
        @endif
    </section>

    {{-- Danger zone --}}
    <div class="bg-white rounded-2xl shadow-sm p-5 border border-red-100">
        <h2 class="text-lg font-bold text-red-700">Reset Voting</h2>
        <p class="mt-1 text-sm text-gray-500">Menghapus seluruh suara, mengembalikan status pemilih, dan mereset perolehan kandidat. Gunakan hanya untuk simulasi.</p>
        <button wire:click="confirmReset" @disabled($this->setting->is_voting_open) class="ui-button mt-3 border border-red-200 text-red-700 hover:bg-red-50">
            {{ $this->setting->is_voting_open ? 'Tutup Sesi untuk Reset' : 'Reset Voting' }}
        </button>
    </div>

    {{-- Reset confirmation modal --}}
    @if ($showResetConfirm)
        <div class="fixed inset-0 z-50 flex items-center justify-center px-4">
            <div class="absolute inset-0 bg-gray-900/50" wire:click="cancelReset"></div>
            <div class="relative bg-white rounded-2xl shadow-lg max-w-sm w-full p-6">
                <h3 class="text-base font-semibold text-gray-900">Reset Seluruh Data Voting?</h3>
                <p class="mt-2 text-sm text-gray-600">Tindakan ini menghapus semua suara masuk, mengembalikan status siswa menjadi "belum memilih", dan mereset perolehan kandidat. Tidak dapat dibatalkan.</p>
                <label class="block mt-4 text-xs font-medium text-gray-700">Ketik <span class="font-mono font-semibold">RESET</span> untuk konfirmasi</label>
                <input type="text" wire:model="resetConfirmText" class="mt-1.5 w-full rounded-lg border-gray-300 shadow-sm text-sm px-3.5 py-2.5 focus:border-red-500 focus:ring-red-500">
                <div class="mt-5 flex gap-3">
                    <button wire:click="cancelReset" class="flex-1 rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">Batal</button>
                    <button wire:click="resetData" class="flex-1 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-red-500 transition">Reset Sekarang</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Session confirmation modal --}}
    @if ($showSessionConfirm)
        <div class="fixed inset-0 z-50 flex items-center justify-center px-4">
            <div class="absolute inset-0 bg-gray-900/50" wire:click="cancelToggleSession"></div>
            <div class="relative w-full max-w-sm rounded-2xl bg-white p-6 shadow-lg">
                <h3 class="text-base font-semibold text-gray-900">
                    {{ $this->setting->is_voting_open ? 'Tutup Sesi Voting?' : 'Buka Sesi Voting?' }}
                </h3>
                <p class="mt-2 text-sm text-gray-600">
                    {{ $this->setting->is_voting_open
                        ? 'Siswa tidak dapat mengirim suara baru setelah sesi ditutup.'
                        : 'Siswa dapat mulai melakukan voting setelah sesi dibuka.' }}
                </p>
                <div class="mt-5 flex gap-3">
                    <button wire:click="cancelToggleSession" class="flex-1 rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Batal</button>
                    <button wire:click="toggleVotingSession" class="flex-1 rounded-lg {{ $this->setting->is_voting_open ? 'bg-red-600 hover:bg-red-500' : 'bg-indigo-600 hover:bg-indigo-500' }} px-4 py-2.5 text-sm font-medium text-white shadow-sm transition">
                        {{ $this->setting->is_voting_open ? 'Tutup Sesi' : 'Buka Sesi' }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
