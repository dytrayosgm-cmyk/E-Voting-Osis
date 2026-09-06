<div class="min-h-screen flex flex-col">

    {{-- Header --}}
    <header class="border-b border-gray-200 bg-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-5 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="w-11 h-11 rounded-xl bg-indigo-600 flex items-center justify-center text-white font-bold text-base">EV</span>
                <div>
                    <p class="text-base font-bold text-gray-900">Pemilihan Ketua &amp; Wakil OSIS</p>
                    <p class="text-sm text-gray-500">Bilik Suara Digital</p>
                </div>
            </div>

            @if ($this->votingOpen)
                <span class="inline-flex items-center gap-2 px-3 py-2 rounded-full text-sm font-semibold bg-green-50 text-green-700">
                    <span class="w-2 h-2 rounded-full bg-green-500"></span> Sesi Dibuka
                </span>
            @else
                <span class="inline-flex items-center gap-2 px-3 py-2 rounded-full text-sm font-semibold bg-gray-100 text-gray-600">
                    <span class="w-2 h-2 rounded-full bg-gray-400"></span> Sesi Ditutup
                </span>
            @endif
        </div>
    </header>

    <main class="flex-1 max-w-6xl w-full mx-auto px-4 sm:px-6 py-8 lg:py-10">

        {{-- ================= LAYAR SUKSES ================= --}}
        @if ($voteSuccess)
            <div
                x-data
                x-init="setTimeout(() => $wire.resetBooth(), 4000)"
                class="max-w-lg mx-auto text-center bg-white rounded-2xl shadow-md p-8 sm:p-10 mt-8"
            >
                <div class="w-16 h-16 mx-auto rounded-full bg-green-50 flex items-center justify-center">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h2 class="mt-5 text-2xl font-bold text-gray-900">Suara Anda berhasil tercatat</h2>
                <p class="mt-2 text-base leading-relaxed text-gray-500">Terima kasih sudah menggunakan hak suara Anda. Layar akan kembali otomatis untuk pemilih berikutnya.</p>
            </div>

        {{-- ================= SESI DITUTUP ================= --}}
        @elseif (! $this->votingOpen && ! $verified)
            <div class="max-w-lg mx-auto text-center bg-white rounded-2xl shadow-md p-8 sm:p-10 mt-8">
                <div class="w-16 h-16 mx-auto rounded-full bg-gray-100 flex items-center justify-center">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <h2 class="mt-5 text-2xl font-bold text-gray-900">Sesi voting belum dibuka</h2>
                <p class="mt-2 text-base leading-relaxed text-gray-500">Silakan tunggu pengumuman dari panitia untuk mulai memilih.</p>
            </div>

        {{-- ================= FASE VERIFIKASI NIS ================= --}}
        @elseif (! $verified)
            <div class="max-w-lg mx-auto bg-white rounded-2xl shadow-md p-7 sm:p-9 mt-8">
                <h2 class="text-2xl font-bold text-gray-900">Verifikasi Identitas</h2>
                <p class="mt-2 text-base text-gray-500">Masukkan NIS/NIK Anda untuk memulai proses pemilihan.</p>

                <form wire:submit="verifyNis" class="mt-6">
                    <label for="nis" class="block text-base font-semibold text-gray-700">NIS / NIK</label>
                    <input
                        type="text"
                        id="nis"
                        wire:model="nis"
                        autofocus
                        inputmode="numeric"
                        placeholder="Contoh: 2024001"
                           class="ui-input mt-1.5 w-full text-lg tracking-wide"
                    >
                    @error('nis')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    @if ($errorMessage)
                        <div class="mt-4 rounded-lg bg-red-50 px-4 py-3 text-base text-red-700">
                            {{ $errorMessage }}
                        </div>
                    @endif

                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="verifyNis"
                           class="ui-button mt-6 w-full bg-indigo-600 text-white hover:bg-indigo-500"
                    >
                        <span wire:loading.remove wire:target="verifyNis">Verifikasi &amp; Lanjutkan</span>
                        <span wire:loading wire:target="verifyNis">Memeriksa...</span>
                    </button>
                </form>
            </div>

        {{-- ================= FASE PEMILIHAN KANDIDAT ================= --}}
        @else
            <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="inline-flex items-center gap-3 rounded-lg bg-white shadow-sm px-4 py-3 w-fit">
                    <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>
                    <p class="text-base text-gray-700">
                        NIS Terverifikasi: <span class="font-semibold text-gray-900">{{ $studentName }}</span> — Hak suara sah tersedia
                    </p>
                </div>
            </div>

            @if ($errorMessage)
                <div class="mb-6 rounded-lg bg-red-50 px-4 py-3 text-base text-red-700">
                    {{ $errorMessage }}
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach ($this->candidates as $candidate)
                    <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition overflow-hidden flex flex-col">
                        <div class="p-5 flex items-center gap-3 border-b border-gray-100">
                            <span class="w-11 h-11 rounded-full bg-indigo-50 text-indigo-700 font-bold text-base flex items-center justify-center shrink-0">
                                {{ $candidate->number_order }}
                            </span>
                            <div class="min-w-0">
                                <p class="font-bold text-gray-900 text-base leading-tight">{{ $candidate->chairman_name }} &amp; {{ $candidate->vice_chairman_name }}</p>
                                <p class="mt-1 text-sm text-gray-500">{{ $candidate->slogan }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-px bg-gray-100">
                            <div class="bg-gray-50 aspect-square flex items-center justify-center overflow-hidden">
                                @if ($candidate->chairman_photo)
                                    <img src="{{ Storage::url($candidate->chairman_photo) }}" class="w-full h-full object-cover" alt="Foto Ketua">
                                @else
                                    <span class="text-xs text-gray-400">Foto Ketua</span>
                                @endif
                            </div>
                            <div class="bg-gray-50 aspect-square flex items-center justify-center overflow-hidden">
                                @if ($candidate->vice_chairman_photo)
                                    <img src="{{ Storage::url($candidate->vice_chairman_photo) }}" class="w-full h-full object-cover" alt="Foto Wakil">
                                @else
                                    <span class="text-xs text-gray-400">Foto Wakil</span>
                                @endif
                            </div>
                        </div>

                        <div class="p-5 flex-1 flex flex-col">
                            <p class="text-sm font-bold text-gray-500 uppercase tracking-wide">Visi</p>
                            <p class="mt-1 text-base text-gray-700 leading-relaxed">{{ $candidate->vision }}</p>

                            <details class="mt-4 group">
                                <summary class="cursor-pointer text-sm font-semibold text-indigo-600 flex items-center gap-1 select-none">
                                    Lihat Program Misi
                                    <svg class="w-3.5 h-3.5 transition group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </summary>
                                <ul class="mt-2.5 space-y-1.5">
                                    @foreach ($candidate->mission as $point)
                                        <li class="text-sm text-gray-600 flex gap-2">
                                            <span class="text-indigo-400 mt-0.5">•</span>
                                            <span>{{ $point }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </details>

                            <button
                                wire:click="selectCandidate({{ $candidate->id }})"
                                wire:loading.attr="disabled"
                                    class="ui-button mt-6 w-full bg-indigo-600 text-white hover:bg-indigo-500"
                            >
                                Pilih Paslon Ini
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </main>

    {{-- ================= MODAL KONFIRMASI ================= --}}
    @if ($showConfirmModal)
        @php $chosen = $this->candidates->firstWhere('id', $selectedCandidateId); @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center px-4">
            <div class="absolute inset-0 bg-gray-900/50" wire:click="cancelConfirmation"></div>
            <div class="relative bg-white rounded-2xl shadow-lg max-w-sm w-full p-6">
                <h3 class="text-base font-semibold text-gray-900">Konfirmasi Pilihan Anda</h3>
                @if ($chosen)
                    <p class="mt-2 text-sm text-gray-600">
                        Anda akan memilih Paslon No. <span class="font-semibold text-gray-900">{{ $chosen->number_order }}</span> —
                        <span class="font-semibold text-gray-900">{{ $chosen->chairman_name }} &amp; {{ $chosen->vice_chairman_name }}</span>.
                    </p>
                @endif
                <p class="mt-2 text-xs text-amber-600 bg-amber-50 rounded-lg px-3 py-2">
                    Pilihan tidak dapat diubah setelah dikonfirmasi. Pastikan pilihan Anda sudah benar.
                </p>

                <div class="mt-5 flex gap-3">
                    <button
                        wire:click="cancelConfirmation"
                        wire:loading.attr="disabled"
                        class="flex-1 rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition"
                    >
                        Batal
                    </button>
                    <button
                        wire:click="confirmVote"
                        wire:loading.attr="disabled"
                        wire:target="confirmVote"
                        class="flex-1 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm
                               hover:bg-indigo-500 transition disabled:opacity-60 disabled:cursor-not-allowed"
                    >
                        <span wire:loading.remove wire:target="confirmVote">Ya, Kirim Suara</span>
                        <span wire:loading wire:target="confirmVote">Menyimpan...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
