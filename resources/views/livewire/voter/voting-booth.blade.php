<div class="min-h-screen flex flex-col font-sans bg-[#f8fafc]">

    {{-- Top Navbar matching Design (Image 2) --}}
    <header class="bg-[#0c1427] border-b border-slate-800/80 shadow-md">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between gap-4">
            {{-- Left: Brand & School Info --}}
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-white p-1 flex items-center justify-center shrink-0 shadow-xs border border-white/20">
                    <img src="{{ asset('images/logo-smk.jpg') }}" alt="Logo SMK An-Nurmaniyah" class="w-full h-full object-contain">
                </div>
                <div>
                    <h1 class="text-base font-extrabold text-white tracking-tight leading-tight">
                        E-Voting OSIS
                    </h1>
                    <p class="text-xs text-slate-300 font-medium">
                        SMK An-Nurmaniyah
                    </p>
                </div>
            </div>

            {{-- Right: Sesi Status Badge --}}
            <div>
                @if ($this->votingOpen)
                    <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-[#062e22] text-[#34d399] border border-[#065f46] shadow-xs">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Sesi Voting Dibuka
                    </span>
                @else
                    <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-slate-800 text-slate-300 border border-slate-700 shadow-xs">
                        <span class="w-2 h-2 rounded-full bg-slate-500"></span>
                        Sesi Voting Ditutup
                    </span>
                @endif
            </div>
        </div>

        {{-- Sub-bar with Period & Date --}}
        <div class="border-t border-slate-800/80 bg-[#0a1122]">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 py-2.5 flex items-center justify-between text-xs text-slate-300">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>Pemilihan Ketua &amp; Wakil OSIS — {{ $this->electionPeriod }}</span>
                </div>
                <div>
                    <span>{{ now()->translatedFormat('d M Y') }}</span>
                </div>
            </div>
        </div>
    </header>

    {{-- Main Content Container --}}
    <main class="flex-1 max-w-6xl w-full mx-auto px-4 sm:px-6 py-8 sm:py-10 space-y-6">

        {{-- ================= LAYAR SUKSES ================= --}}
        @if ($voteSuccess)
            <div
                x-data
                x-init="setTimeout(() => $wire.resetBooth(), 4000)"
                class="max-w-md mx-auto text-center bg-white rounded-3xl shadow-sm border border-slate-200/80 p-8 sm:p-10 my-8 space-y-4"
            >
                <div class="w-16 h-16 mx-auto rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center ring-8 ring-emerald-50/50">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-black text-slate-900">Suara Anda Berhasil Tercatat!</h2>
                    <p class="mt-2 text-xs sm:text-sm leading-relaxed text-slate-500">
                        Terima kasih telah menggunakan hak suara Anda secara bijak dan bertanggung jawab.
                    </p>
                </div>

                <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100 text-xs text-slate-600 space-y-1 text-left">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Status Transaksi:</span>
                        <span class="font-bold text-emerald-600">✓ SUKSES &amp; TERVERIFIKASI</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Waktu:</span>
                        <span class="font-medium text-slate-700">{{ now()->format('d M Y, H:i:s') }}</span>
                    </div>
                </div>

                <div class="pt-2">
                    <p class="text-[11px] text-slate-400">
                        Layar bilik suara akan kembali otomatis dalam <span class="font-bold text-blue-600">4 detik</span> untuk pemilih berikutnya...
                    </p>
                </div>
            </div>

        {{-- ================= SESI DITUTUP ================= --}}
        @elseif (! $this->votingOpen && ! $verified)
            <div class="max-w-md mx-auto text-center bg-white rounded-3xl shadow-sm border border-slate-200/80 p-8 sm:p-10 my-8 space-y-4">
                <div class="w-16 h-16 mx-auto rounded-full bg-slate-100 text-slate-400 flex items-center justify-center">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-black text-slate-900">Sesi Voting Ditutup</h2>
                    <p class="mt-2 text-xs sm:text-sm leading-relaxed text-slate-500">
                        Bilik suara digital saat ini sedang tidak menerima suara. Silakan tunggu instruksi dan pengumuman dari Panitia Pemilihan OSIS.
                    </p>
                </div>
                <div class="pt-2 text-xs text-slate-400">
                    Sistem Bilik Suara Digital
                </div>
            </div>

        {{-- ================= LANGKAH 1: VERIFIKASI NIS ================= --}}
        @elseif (! $verified)
            <div
                x-data="{
                    focusNis() {
                        this.$nextTick(() => {
                            if (this.$refs.nisInput) {
                                this.$refs.nisInput.focus();
                                this.$refs.nisInput.select();
                            }
                        });
                    },
                    handleNisEnter(e) {
                        e.preventDefault();
                        if (this.$refs.tokenInput) {
                            this.$refs.tokenInput.focus();
                            this.$refs.tokenInput.select();
                        }
                    },
                    handleTokenEnter(e) {
                        e.preventDefault();
                        if (this.$refs.submitBtn) {
                            this.$refs.submitBtn.focus();
                            this.$refs.submitBtn.click();
                        }
                    }
                }"
                x-init="focusNis()"
                class="max-w-lg mx-auto bg-white rounded-3xl shadow-sm border border-slate-200/80 p-6 sm:p-9 my-4 space-y-5"
            >
                <div class="space-y-1">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-extrabold uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-100">
                        LANGKAH 1 DARI 2
                    </span>
                    <h2 class="text-2xl font-black text-slate-900 tracking-tight mt-2">Verifikasi Identitas &amp; Token</h2>
                    <p class="text-xs sm:text-sm text-slate-500 leading-relaxed">
                        Masukkan Nomor Induk Siswa (NIS) dan Token/Kode Unik 4 karakter yang diberikan panitia untuk masuk ke bilik suara digital.
                    </p>
                </div>

                <form
                    wire:submit="verifyNis"
                    autocomplete="off"
                    class="space-y-4 pt-2"
                >
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="booth_nis" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                NIS / NIK Siswa
                            </label>
                            <span class="text-[11px] text-slate-400 font-medium">
                                Tekan <kbd class="px-1.5 py-0.5 rounded bg-slate-100 border border-slate-200 text-slate-600 font-mono text-[10px] font-bold">Enter ↵</kbd> ke Token
                            </span>
                        </div>
                        <div class="relative">
                            <input
                                type="text"
                                id="booth_nis"
                                name="voter_nis_session"
                                x-ref="nisInput"
                                wire:model="nis"
                                @keydown.enter="handleNisEnter($event)"
                                @keydown.down="handleNisEnter($event)"
                                autofocus
                                inputmode="numeric"
                                autocomplete="off"
                                autocorrect="off"
                                autocapitalize="off"
                                spellcheck="false"
                                data-lpignore="true"
                                data-form-type="other"
                                aria-autocomplete="none"
                                placeholder="Masukkan NIS Anda (contoh: 2024001)"
                                class="ui-input w-full text-base sm:text-lg tracking-wide py-3 px-4 font-semibold text-slate-900 focus:ring-2 focus:ring-blue-600 focus:border-blue-600 focus:outline-hidden"
                            >
                        </div>
                        @error('nis')
                            <p class="mt-1.5 text-xs text-red-600 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="booth_token" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Token / Kode Unik (4 Karakter)
                            </label>
                            <span class="text-[11px] text-slate-400 font-medium">
                                Tekan <kbd class="px-1.5 py-0.5 rounded bg-slate-100 border border-slate-200 text-slate-600 font-mono text-[10px] font-bold">Enter ↵</kbd> Validasi
                            </span>
                        </div>
                        <div class="relative">
                            <input
                                type="text"
                                id="booth_token"
                                name="voter_token_session"
                                x-ref="tokenInput"
                                wire:model="unique_code"
                                @keydown.enter="handleTokenEnter($event)"
                                @keydown.up="$refs.nisInput?.focus(); $refs.nisInput?.select();"
                                @input="$el.value = $el.value.toUpperCase()"
                                maxlength="4"
                                autocomplete="off"
                                autocorrect="off"
                                autocapitalize="characters"
                                spellcheck="false"
                                data-lpignore="true"
                                data-form-type="other"
                                aria-autocomplete="none"
                                placeholder="Contoh: A7B2"
                                class="ui-input w-full text-base sm:text-lg font-mono font-bold tracking-widest uppercase py-3 px-4 text-slate-900 focus:ring-2 focus:ring-blue-600 focus:border-blue-600 focus:outline-hidden"
                            >
                        </div>
                        @error('unique_code')
                            <p class="mt-1.5 text-xs text-red-600 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    @if ($errorMessage)
                        <div class="rounded-2xl bg-red-50 border border-red-200 p-3.5 text-xs text-red-700 flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-red-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>{{ $errorMessage }}</span>
                        </div>
                    @endif

                    <button
                        type="submit"
                        x-ref="submitBtn"
                        wire:loading.attr="disabled"
                        wire:target="verifyNis"
                        class="ui-button w-full bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:ring-blue-200 text-white font-bold text-sm py-3.5 rounded-xl shadow-xs transition flex items-center justify-center gap-2 cursor-pointer"
                    >
                        <span wire:loading.remove wire:target="verifyNis" class="flex items-center gap-1.5">
                            <span>Verifikasi &amp; Lanjutkan</span>
                            <kbd class="hidden sm:inline-block px-1.5 py-0.5 rounded bg-blue-700/80 text-white font-mono text-[11px] font-semibold">↵</kbd>
                        </span>
                        <span wire:loading wire:target="verifyNis" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Memeriksa DPT...
                        </span>
                    </button>
                </form>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                    <span>Satu siswa hanya berhak memilih satu kali</span>
                    <span>Terenkripsi SHA-256</span>
                </div>
            </div>

        {{-- ================= LANGKAH 2: PILIH PASANGAN CALON ================= --}}
        @else
            <div
                x-data="{
                    handleKeySelection(e) {
                        if (['INPUT', 'TEXTAREA'].includes(e.target.tagName)) return;

                        if (@js($showConfirmModal)) {
                            if (e.key === 'Escape') {
                                e.preventDefault();
                                $wire.cancelConfirmation();
                            } else if (e.key === 'Enter') {
                                e.preventDefault();
                                $wire.confirmVote();
                            }
                            return;
                        }

                        let digit = null;
                        if (e.key >= '1' && e.key <= '9') {
                            digit = parseInt(e.key);
                        } else if (e.code && e.code.startsWith('Numpad') && e.code.length === 7) {
                            digit = parseInt(e.code.replace('Numpad', ''));
                        }

                        if (digit) {
                            const btn = document.querySelector(`[data-candidate-number='${digit}']`);
                            if (btn) {
                                e.preventDefault();
                                btn.click();
                            }
                        }
                    }
                }"
                @keydown.window="handleKeySelection($event)"
                class="space-y-6"
            >

                {{-- Student Identity Bar (Top Card in Design) --}}
                <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-2xs border border-slate-200/90 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-11 h-11 rounded-full bg-blue-600 text-white font-black text-lg flex items-center justify-center shrink-0 shadow-xs">
                            {{ strtoupper(substr($studentName, 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-1.5 text-blue-600 font-extrabold text-[11px] uppercase tracking-wider">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>IDENTITAS TERVERIFIKASI</span>
                            </div>
                            <h2 class="text-base sm:text-lg font-black text-slate-900 leading-tight mt-0.5 truncate">
                                {{ $studentName }}
                                @if ($studentClass)
                                    <span class="text-xs font-semibold text-slate-400 font-normal">({{ $studentClass }})</span>
                                @endif
                            </h2>
                        </div>
                    </div>
                    <div class="shrink-0">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80 shadow-2xs">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            Hak Suara Aktif
                        </span>
                    </div>
                </div>

                {{-- Step 2 Title & Details --}}
                <div class="space-y-1.5">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-extrabold uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-100">
                        LANGKAH 2 DARI 2
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                        Pilih Pasangan Calon
                    </h1>
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <p class="text-xs sm:text-sm text-slate-500">
                            Pilih <strong class="text-slate-800">satu</strong> pasangan calon. <span class="hidden sm:inline">Dapat menekan tombol angka keyboard <kbd class="px-1.5 py-0.5 rounded bg-slate-200 text-slate-700 font-mono text-[10px] font-bold">[1]</kbd> atau <kbd class="px-1.5 py-0.5 rounded bg-slate-200 text-slate-700 font-mono text-[10px] font-bold">[2]</kbd>.</span>
                        </p>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200/80 self-start sm:self-auto shrink-0">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                            {{ $this->candidates->count() }} Paslon Terdaftar
                        </span>
                    </div>
                </div>

                @if ($errorMessage)
                    <div class="rounded-2xl bg-red-50 border border-red-200 p-3.5 text-xs text-red-700">
                        {{ $errorMessage }}
                    </div>
                @endif

                {{-- Candidate Cards Grid (2 Columns matching Image 2) --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-stretch">
                    @foreach ($this->candidates as $candidate)
                        <div class="bg-white rounded-3xl border border-slate-200/90 shadow-2xs hover:shadow-md transition-all duration-200 p-4 sm:p-5 flex flex-col justify-between overflow-hidden">
                            <div>
                                {{-- Photos Section with Badge and Gradient Overlays --}}
                                <div class="grid grid-cols-2 gap-3 mb-4">
                                    {{-- Photo 1: Ketua --}}
                                    <div class="relative rounded-2xl overflow-hidden aspect-[4/5] bg-slate-100 border border-slate-100 group">
                                        {{-- No. Order Badge --}}
                                        <div class="absolute top-2.5 left-2.5 bg-white/95 backdrop-blur-xs px-2.5 py-1 rounded-lg text-xs font-black text-slate-800 shadow-xs z-10">
                                            No. {{ $candidate->number_order }}
                                        </div>

                                        @if ($candidate->chairman_photo)
                                            <img
                                                src="{{ Storage::disk('public')->url($candidate->chairman_photo) }}"
                                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                                alt="Foto Calon Ketua"
                                            >
                                        @else
                                            <div class="w-full h-full flex flex-col items-center justify-center bg-slate-100 text-slate-400 text-xs">
                                                <svg class="w-10 h-10 mb-1 opacity-50" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                Foto Ketua
                                            </div>
                                        @endif

                                        {{-- Gradient Bottom Overlay with KETUA label --}}
                                        <div class="absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-black/85 via-black/40 to-transparent flex items-end justify-center pb-2.5 z-10 pointer-events-none">
                                            <span class="text-[11px] font-extrabold tracking-widest text-white uppercase text-center">
                                                KETUA
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Photo 2: Wakil --}}
                                    <div class="relative rounded-2xl overflow-hidden aspect-[4/5] bg-slate-100 border border-slate-100 group">
                                        @if ($candidate->vice_chairman_photo)
                                            <img
                                                src="{{ Storage::disk('public')->url($candidate->vice_chairman_photo) }}"
                                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                                alt="Foto Calon Wakil"
                                            >
                                        @else
                                            <div class="w-full h-full flex flex-col items-center justify-center bg-slate-100 text-slate-400 text-xs">
                                                <svg class="w-10 h-10 mb-1 opacity-50" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                Foto Wakil
                                            </div>
                                        @endif

                                        {{-- Gradient Bottom Overlay with WAKIL label --}}
                                        <div class="absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-black/85 via-black/40 to-transparent flex items-end justify-center pb-2.5 z-10 pointer-events-none">
                                            <span class="text-[11px] font-extrabold tracking-widest text-white uppercase text-center">
                                                WAKIL
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Names & Slogan --}}
                                <div class="space-y-0.5">
                                    <h3 class="text-lg font-black text-slate-900 leading-tight">
                                        {{ $candidate->chairman_name }} <span class="text-slate-400 font-normal">&amp;</span> {{ $candidate->vice_chairman_name }}
                                    </h3>
                                    @if ($candidate->slogan)
                                        <p class="text-xs text-slate-500 italic line-clamp-1">
                                            “{{ $candidate->slogan }}”
                                        </p>
                                    @endif
                                </div>

                                {{-- Visi Box --}}
                                <div class="mt-3.5 bg-blue-50/50 border border-blue-100/70 rounded-2xl p-3.5 text-left">
                                    <span class="text-[10px] font-black text-blue-600 tracking-wider uppercase block">VISI</span>
                                    <p class="text-xs text-slate-600 mt-1 leading-relaxed line-clamp-3">
                                        {{ $candidate->vision }}
                                    </p>
                                </div>

                                {{-- Program Misi Accordion / Dropdown --}}
                                <details class="mt-3 group">
                                    <summary class="cursor-pointer text-xs font-bold text-blue-600 hover:text-blue-700 flex items-center gap-1 select-none">
                                        <svg class="w-3.5 h-3.5 transition-transform duration-200 group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                        <span>Program Misi</span>
                                    </summary>
                                    <ul class="mt-2 space-y-1.5 pl-1">
                                        @foreach ($candidate->mission ?? [] as $point)
                                            <li class="text-xs text-slate-600 flex items-start gap-2">
                                                <span class="text-blue-500 font-bold">•</span>
                                                <span>{{ $point }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </details>
                            </div>

                            {{-- Action Button --}}
                            <div class="mt-5">
                                <button
                                    wire:click="selectCandidate({{ $candidate->id }})"
                                    data-candidate-number="{{ $candidate->number_order }}"
                                    wire:loading.attr="disabled"
                                    class="ui-button w-full py-3.5 px-4 rounded-xl font-bold text-xs sm:text-sm text-white bg-blue-600 hover:bg-blue-700 active:bg-blue-800 shadow-xs hover:shadow transition-all flex items-center justify-center gap-2 cursor-pointer focus:ring-4 focus:ring-blue-200"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span>Pilih Paslon Ini</span>
                                    <span class="px-2 py-0.5 rounded-md bg-blue-800/60 text-[10px] font-mono font-bold text-blue-100 ml-1">
                                        Tekan [{{ $candidate->number_order }}]
                                    </span>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        @endif

    </main>

    {{-- ================= MODAL KONFIRMASI ================= --}}
    @if ($showConfirmModal)
        @php $chosen = $this->candidates->firstWhere('id', $selectedCandidateId); @endphp
        <div
            x-data
            x-init="$nextTick(() => { $refs.confirmVoteBtn?.focus(); })"
            class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6"
        >
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-xs" wire:click="cancelConfirmation"></div>
            <div class="relative bg-white rounded-3xl shadow-xl max-w-sm w-full p-6 sm:p-7 z-10 space-y-4">
                <div class="text-center">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-100">
                        KONFIRMASI AKHIR
                    </span>
                    <h3 class="text-lg font-black text-slate-900 mt-2">Pastikan Pilihan Anda</h3>
                </div>

                @if ($chosen)
                    <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-4 text-center space-y-2">
                        <div class="flex items-center justify-center -space-x-2">
                            @if ($chosen->chairman_photo)
                                <img src="{{ Storage::disk('public')->url($chosen->chairman_photo) }}" class="w-12 h-12 rounded-xl object-cover ring-2 ring-white shadow-xs">
                            @endif
                            @if ($chosen->vice_chairman_photo)
                                <img src="{{ Storage::disk('public')->url($chosen->vice_chairman_photo) }}" class="w-12 h-12 rounded-xl object-cover ring-2 ring-white shadow-xs">
                            @endif
                        </div>
                        <div>
                            <span class="w-6 h-6 rounded-md bg-blue-600 text-white font-black text-xs inline-flex items-center justify-center">
                                {{ $chosen->number_order }}
                            </span>
                            <p class="font-bold text-slate-900 text-sm mt-1">
                                {{ $chosen->chairman_name }} &amp; {{ $chosen->vice_chairman_name }}
                            </p>
                            @if ($chosen->slogan)
                                <p class="text-[11px] text-slate-400 italic">“{{ $chosen->slogan }}”</p>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="bg-amber-50 rounded-2xl p-3 border border-amber-200/80 text-[11px] text-amber-800 flex items-start gap-2">
                    <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Pilihan suara bersifat <strong>rahasia, langsung, dan tidak dapat diubah</strong> setelah dikirimkan.</span>
                </div>

                <div class="flex gap-2.5 pt-1">
                    <button
                        wire:click="cancelConfirmation"
                        wire:loading.attr="disabled"
                        class="flex-1 ui-button bg-slate-100 text-slate-700 hover:bg-slate-200 text-xs font-semibold py-3 cursor-pointer"
                    >
                        <span>Kembali</span>
                        <kbd class="text-[10px] text-slate-400 font-mono ml-1">[Esc]</kbd>
                    </button>
                    <button
                        x-ref="confirmVoteBtn"
                        wire:click="confirmVote"
                        wire:loading.attr="disabled"
                        wire:target="confirmVote"
                        class="flex-1 ui-button bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:ring-blue-200 text-white text-xs font-bold py-3 shadow-xs flex items-center justify-center gap-1.5 cursor-pointer"
                    >
                        <span wire:loading.remove wire:target="confirmVote" class="flex items-center gap-1">
                            <span>✓ Ya, Kirim Suara</span>
                            <kbd class="px-1.5 py-0.5 rounded bg-blue-800/60 text-white font-mono text-[10px]">Enter ↵</kbd>
                        </span>
                        <span wire:loading wire:target="confirmVote">Menyimpan...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Footer --}}
    <footer class="mt-auto py-6 border-t border-slate-200/60 bg-white/60 text-center text-xs text-slate-400">
        <p>© 2026 E-Voting OSIS • Aplikasi Pemilihan Ketua &amp; Wakil OSIS.</p>
        <p class="mt-1 text-[11px] text-slate-400/80">Sistem Bilik Suara Digital Terdistribusi — Aman, Langsung, Bebas, dan Rahasia.</p>
    </footer>

</div>
