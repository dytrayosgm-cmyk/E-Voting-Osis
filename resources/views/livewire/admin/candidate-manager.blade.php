<div class="space-y-4">

    {{-- Section Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <h2 class="text-lg font-extrabold text-slate-900">Manajemen Paslon</h2>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                    {{ $candidates->count() }} Pasangan Kandidat
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100" title="Periode aktif pada bilik suara siswa">
                    <svg class="w-3 h-3 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>{{ $this->election_period ?: 'Periode 2025/2026' }}</span>
                </span>
            </div>
            <p class="mt-0.5 text-xs text-slate-500">Tambah, edit, dan kelola identitas paslon langsung dari Dashboard admin.</p>
        </div>
        <button
            wire:click="openCreate"
            @disabled($this->votingOpen && !$overrideLock)
            class="ui-button bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs px-4 py-2 rounded-xl flex items-center gap-1.5 shadow-xs disabled:opacity-50 disabled:cursor-not-allowed"
        >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah Paslon
        </button>
    </div>

    {{-- Integrity Protection Alert --}}
    @if ($this->votingOpen && !$overrideLock)
        <div class="rounded-xl border border-amber-200 bg-amber-50/90 p-3.5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-xs text-amber-800">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span><strong>Proteksi Integritas:</strong> Sesi voting sedang dibuka. Modifikasi data kandidat dikunci demi integritas perolehan suara bilik siswa.</span>
            </div>
            <button
                wire:click="toggleOverride"
                type="button"
                class="font-semibold text-amber-900 underline hover:text-amber-950 shrink-0 self-start sm:self-auto"
            >
                Buka Kunci Sementara (Override)
            </button>
        </div>
    @elseif ($overrideLock)
        <div class="rounded-xl border border-emerald-200 bg-emerald-50/90 p-3.5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-xs text-emerald-800">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                </svg>
                <span><strong>Kunci Dibuka Sementara:</strong> Anda sekarang dapat menambah atau mengubah data kandidat saat sesi terbuka. Berhati-hatilah agar data perolehan suara tetap valid.</span>
            </div>
            <button
                wire:click="toggleOverride"
                type="button"
                class="font-semibold text-emerald-900 underline hover:text-emerald-950 shrink-0 self-start sm:self-auto"
            >
                Kunci Kembali
            </button>
        </div>
    @endif

    @error('session')
        <div class="rounded-xl bg-red-50 border border-red-200 px-3.5 py-2.5 text-xs text-red-700">{{ $message }}</div>
    @enderror

    @error('delete')
        <div class="rounded-xl bg-red-50 border border-red-200 px-3.5 py-2.5 text-xs text-red-700">{{ $message }}</div>
    @enderror

    {{-- Candidates Table --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left text-slate-600">
                <thead class="bg-slate-50/80 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-3.5 font-bold">NO.</th>
                        <th class="px-4 py-3.5 font-bold">PASLON</th>
                        <th class="px-4 py-3.5 font-bold">SLOGAN / VISI SINGKAT</th>
                        <th class="px-4 py-3.5 font-bold text-center">SUARA MASUK</th>
                        <th class="px-4 py-3.5 font-bold text-center">STATUS</th>
                        <th class="px-4 py-3.5 font-bold text-right">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($candidates as $c)
                        @php
                            $percentage = $totalVotesIn > 0 ? round(($c->vote_count / $totalVotesIn) * 100, 1) : 0;
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition">
                            {{-- Number Order --}}
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-700 font-extrabold flex items-center justify-center text-xs border border-blue-100/60">
                                    {{ sprintf('%02d', $c->number_order) }}
                                </span>
                            </td>

                            {{-- Paslon Identity with 2 mini photos --}}
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center -space-x-1.5 shrink-0">
                                        @if ($c->chairman_photo)
                                            <img src="{{ Storage::disk('public')->url($c->chairman_photo) }}" class="w-8 h-8 rounded-lg object-cover ring-2 ring-white border border-slate-200" alt="Ketua">
                                        @else
                                            <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 font-bold flex items-center justify-center text-[10px] ring-2 ring-white">K</div>
                                        @endif

                                        @if ($c->vice_chairman_photo)
                                            <img src="{{ Storage::disk('public')->url($c->vice_chairman_photo) }}" class="w-8 h-8 rounded-lg object-cover ring-2 ring-white border border-slate-200" alt="Wakil">
                                        @else
                                            <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-700 font-bold flex items-center justify-center text-[10px] ring-2 ring-white">W</div>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-900 text-xs sm:text-sm">{{ $c->chairman_name }} &amp; {{ $c->vice_chairman_name }}</p>
                                        <p class="text-[10px] text-slate-400 font-medium">Kandidat Ketua &amp; Wakil OSIS</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Slogan / Visi --}}
                            <td class="px-4 py-3.5 max-w-xs">
                                <p class="italic text-slate-600 line-clamp-1">
                                    {{ $c->slogan ? '“' . $c->slogan . '”' : '“' . \Illuminate\Support\Str::limit($c->vision, 45) . '”' }}
                                </p>
                            </td>

                            {{-- Suara Masuk --}}
                            <td class="px-4 py-3.5 whitespace-nowrap text-center">
                                <span class="font-black text-slate-900 text-sm block">{{ $c->vote_count }}</span>
                                <span class="text-[10px] text-blue-600 font-bold block">({{ $percentage }}%)</span>
                            </td>

                            {{-- Status --}}
                            <td class="px-4 py-3.5 whitespace-nowrap text-center">
                                @if ($c->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        Nonaktif
                                    </span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-4 py-3.5 whitespace-nowrap text-right space-x-2 font-semibold">
                                <button
                                    wire:click="viewVisionMission({{ $c->id }})"
                                    type="button"
                                    class="text-blue-600 hover:text-blue-700 hover:underline"
                                >
                                    Visi &amp; Misi
                                </button>
                                <button
                                    wire:click="openEdit({{ $c->id }})"
                                    @disabled($this->votingOpen && !$overrideLock)
                                    type="button"
                                    class="text-slate-600 hover:text-slate-800 disabled:opacity-40 disabled:cursor-not-allowed"
                                >
                                    Edit
                                </button>
                                <button
                                    wire:click="delete({{ $c->id }})"
                                    wire:confirm="Yakin ingin menghapus paslon ini?"
                                    @disabled($this->votingOpen && !$overrideLock)
                                    type="button"
                                    class="text-red-600 hover:text-red-700 disabled:opacity-40 disabled:cursor-not-allowed"
                                >
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-400">
                                Belum ada kandidat paslon terdaftar. Klik "+ Tambah Paslon" untuk menambahkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Detail Visi & Misi Modal --}}
    @if ($showVisionModal && $viewingCandidate)
        <div class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-xs" wire:click="closeVisionMission"></div>
            <div class="relative bg-white rounded-3xl shadow-xl max-w-lg w-full p-6 sm:p-7 z-10 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-700 font-extrabold flex items-center justify-center text-xs">
                            {{ sprintf('%02d', $viewingCandidate->number_order) }}
                        </span>
                        <h3 class="text-base font-bold text-slate-900">
                            {{ $viewingCandidate->chairman_name }} &amp; {{ $viewingCandidate->vice_chairman_name }}
                        </h3>
                    </div>
                    <button wire:click="closeVisionMission" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                @if ($viewingCandidate->slogan)
                    <p class="text-xs italic text-slate-500 bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                        “{{ $viewingCandidate->slogan }}”
                    </p>
                @endif

                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-blue-600">Visi</h4>
                    <p class="mt-1 text-xs text-slate-700 leading-relaxed bg-blue-50/50 p-3 rounded-xl border border-blue-100/60">
                        {{ $viewingCandidate->vision }}
                    </p>
                </div>

                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-blue-600">Program Misi</h4>
                    <ul class="mt-1.5 space-y-1.5 pl-1">
                        @foreach ($viewingCandidate->mission ?? [] as $m)
                            <li class="text-xs text-slate-600 flex items-start gap-2">
                                <span class="text-blue-500 font-bold">•</span>
                                <span>{{ $m }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="pt-3 border-t border-slate-100 text-right">
                    <button wire:click="closeVisionMission" class="ui-button bg-slate-100 text-slate-700 hover:bg-slate-200 text-xs px-4 py-2">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Create / Edit Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 overflow-y-auto">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-xs" wire:click="$set('showModal', false)"></div>
            <div class="relative bg-white rounded-3xl shadow-xl max-w-lg w-full p-6 sm:p-7 my-auto z-10 max-h-[90vh] overflow-y-auto space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-lg font-bold text-slate-900">
                        {{ $editingId ? 'Edit Pasangan Calon' : 'Tambah Paslon Baru' }}
                    </h3>
                    <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form wire:submit="save" class="space-y-4 text-xs">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Nomor Urut</label>
                            <input type="number" wire:model="number_order" class="ui-input w-full" placeholder="1, 2, ...">
                            @error('number_order') <p class="mt-1 text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex items-center pt-6">
                            <label class="inline-flex items-center gap-2 font-semibold text-slate-700 cursor-pointer">
                                <input type="checkbox" wire:model="is_active" class="rounded text-blue-600 focus:ring-blue-500">
                                Tampilkan di Bilik Suara
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Nama Calon Ketua</label>
                            <input type="text" wire:model="chairman_name" class="ui-input w-full" placeholder="Nama Lengkap Ketua">
                            @error('chairman_name') <p class="mt-1 text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Nama Calon Wakil</label>
                            <input type="text" wire:model="vice_chairman_name" class="ui-input w-full" placeholder="Nama Lengkap Wakil">
                            @error('vice_chairman_name') <p class="mt-1 text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Foto Ketua</label>
                            <input type="file" wire:model="chairman_photo" accept="image/*" class="w-full text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            @error('chairman_photo') <p class="mt-1 text-red-600">{{ $message }}</p> @enderror
                            @if ($chairman_photo)
                                <img src="{{ $chairman_photo->temporaryUrl() }}" class="mt-2 w-16 h-16 object-cover rounded-xl border border-slate-200">
                            @elseif ($existingChairmanPhoto)
                                <img src="{{ Storage::disk('public')->url($existingChairmanPhoto) }}" class="mt-2 w-16 h-16 object-cover rounded-xl border border-slate-200">
                            @endif
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Foto Wakil</label>
                            <input type="file" wire:model="vice_chairman_photo" accept="image/*" class="w-full text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            @error('vice_chairman_photo') <p class="mt-1 text-red-600">{{ $message }}</p> @enderror
                            @if ($vice_chairman_photo)
                                <img src="{{ $vice_chairman_photo->temporaryUrl() }}" class="mt-2 w-16 h-16 object-cover rounded-xl border border-slate-200">
                            @elseif ($existingViceChairmanPhoto)
                                <img src="{{ Storage::disk('public')->url($existingViceChairmanPhoto) }}" class="mt-2 w-16 h-16 object-cover rounded-xl border border-slate-200">
                            @endif
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Slogan / Tagline</label>
                        <input type="text" wire:model="slogan" class="ui-input w-full" placeholder="Contoh: Bersama Kita Berkarya, Mandiri dan Berprestasi">
                        @error('slogan') <p class="mt-1 text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="p-3 bg-blue-50/60 rounded-xl border border-blue-100 space-y-1">
                        <label class="block font-bold text-slate-800">
                            Periode / Hari Pemilihan (Tampil di Bilik Suara)
                        </label>
                        <input
                            type="text"
                            wire:model="election_period"
                            class="ui-input w-full bg-white text-xs font-semibold"
                            placeholder="Contoh: Periode 2025/2026 atau Hari Pemilihan: 25 Okt 2026"
                        >
                        <p class="text-[10px] text-slate-500">
                            Dapat disesuaikan dengan hari pemilihan. Otomatis tampil di sub-bar bilik suara siswa.
                        </p>
                        @error('election_period') <p class="text-red-600 text-[11px]">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Visi</label>
                        <textarea wire:model="vision" rows="3" class="ui-input w-full" placeholder="Tuliskan rumusan visi paslon..."></textarea>
                        @error('vision') <p class="mt-1 text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Program Misi (1 baris = 1 poin)</label>
                        <textarea wire:model="missionText" rows="4" class="ui-input w-full" placeholder="Poin 1&#10;Poin 2&#10;Poin 3"></textarea>
                        @error('missionText') <p class="mt-1 text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                        <button type="button" wire:click="$set('showModal', false)" class="ui-button bg-slate-100 text-slate-700 hover:bg-slate-200 text-xs px-4 py-2">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled" class="ui-button bg-blue-600 text-white hover:bg-blue-700 text-xs px-5 py-2">
                            <span wire:loading.remove wire:target="save">Simpan Paslon</span>
                            <span wire:loading wire:target="save">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
