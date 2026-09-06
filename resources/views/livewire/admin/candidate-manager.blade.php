<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Manajemen Kandidat</h1>
            <p class="mt-1 text-base text-gray-500">Perubahan di sini otomatis tersinkron ke tampilan Bilik Suara siswa.</p>
        </div>
        <button wire:click="openCreate" @disabled($this->votingOpen) class="ui-button bg-indigo-600 text-white hover:bg-indigo-500">
            + Tambah Paslon
        </button>
    </div>

    @if ($this->votingOpen)
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-base text-amber-800">
            Sesi voting sedang dibuka. Pengelolaan paslon dikunci sampai sesi ditutup kembali.
        </div>
    @else
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-base text-green-800">
            Sesi voting ditutup. Data paslon dapat ditambahkan atau diperbarui.
        </div>
    @endif

    @error('session')
        <div class="rounded-lg bg-red-50 px-4 py-3 text-base text-red-700">{{ $message }}</div>
    @enderror

    @error('delete')
        <div class="rounded-lg bg-red-50 px-4 py-3 text-base text-red-700">{{ $message }}</div>
    @enderror

    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-180 text-base">
            <thead class="bg-gray-50 text-sm text-gray-500 uppercase tracking-wide">
                <tr>
                    <th class="text-left px-5 py-4 font-semibold">No.</th>
                    <th class="text-left px-5 py-4 font-semibold">Paslon</th>
                    <th class="text-left px-5 py-4 font-semibold">Slogan</th>
                    <th class="text-left px-5 py-4 font-semibold">Suara</th>
                    <th class="text-left px-5 py-4 font-semibold">Status</th>
                    <th class="text-right px-5 py-4 font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($candidates as $c)
                    <tr>
                        <td class="px-5 py-4">
                            <span class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-700 font-bold text-sm flex items-center justify-center">{{ $c->number_order }}</span>
                        </td>
                        <td class="px-5 py-4 font-semibold text-gray-900">{{ $c->chairman_name }} &amp; {{ $c->vice_chairman_name }}</td>
                        <td class="px-5 py-4 text-gray-500">{{ $c->slogan }}</td>
                        <td class="px-5 py-4 text-gray-700">{{ $c->vote_count }}</td>
                        <td class="px-5 py-4">
                            <span class="text-sm px-2.5 py-1 rounded-full {{ $c->is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $c->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right space-x-3">
                            <button wire:click="openEdit({{ $c->id }})" @disabled($this->votingOpen) class="text-indigo-600 hover:text-indigo-500 text-sm font-semibold disabled:cursor-not-allowed disabled:opacity-40">Edit</button>
                            <button wire:click="delete({{ $c->id }})" wire:confirm="Hapus paslon ini?" @disabled($this->votingOpen) class="text-red-600 hover:text-red-500 text-sm font-semibold disabled:cursor-not-allowed disabled:opacity-40">Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-8 text-center text-gray-400 text-base">Belum ada kandidat. Klik "+ Tambah Paslon" untuk mulai.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    {{-- Modal Form --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center px-4 py-8 overflow-y-auto">
            <div class="absolute inset-0 bg-gray-900/50" wire:click="$set('showModal', false)"></div>
            <div class="relative bg-white rounded-2xl shadow-lg max-w-lg w-full p-6 my-auto max-h-[90vh] overflow-y-auto">
                <h3 class="text-xl font-bold text-gray-900">{{ $editingId ? 'Edit Paslon' : 'Tambah Paslon Baru' }}</h3>

                <form wire:submit="save" class="mt-5 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Nomor Urut</label>
                            <input type="number" wire:model="number_order" class="ui-input mt-1.5 w-full">
                            @error('number_order') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex items-end pb-2.5">
                            <label class="inline-flex items-center gap-2 text-sm font-semibold text-gray-700">
                                <input type="checkbox" wire:model="is_active" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                Tampilkan di Bilik Suara
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Nama Ketua</label>
                            <input type="text" wire:model="chairman_name" class="ui-input mt-1.5 w-full">
                            @error('chairman_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Nama Wakil</label>
                            <input type="text" wire:model="vice_chairman_name" class="ui-input mt-1.5 w-full">
                            @error('vice_chairman_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Foto Ketua</label>
                            <input type="file" wire:model="chairman_photo" accept="image/*" class="mt-1.5 w-full text-sm">
                            <div wire:loading wire:target="chairman_photo" class="text-sm text-gray-400 mt-1">Mengunggah...</div>
                            <div class="mt-2 aspect-4/5 overflow-hidden rounded-lg bg-gray-100">
                                @if ($chairman_photo)
                                    <img src="{{ $chairman_photo->temporaryUrl() }}" class="h-full w-full object-cover" alt="Pratinjau foto ketua">
                                @elseif ($existingChairmanPhoto)
                                    <img src="{{ Storage::disk('public')->url($existingChairmanPhoto) }}" class="h-full w-full object-cover" alt="Foto ketua saat ini">
                                @else
                                    <div class="flex h-full items-center justify-center text-sm text-gray-400">Belum ada foto</div>
                                @endif
                            </div>
                            @error('chairman_photo') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Foto Wakil</label>
                            <input type="file" wire:model="vice_chairman_photo" accept="image/*" class="mt-1.5 w-full text-sm">
                            <div wire:loading wire:target="vice_chairman_photo" class="text-sm text-gray-400 mt-1">Mengunggah...</div>
                            <div class="mt-2 aspect-4/5 overflow-hidden rounded-lg bg-gray-100">
                                @if ($vice_chairman_photo)
                                    <img src="{{ $vice_chairman_photo->temporaryUrl() }}" class="h-full w-full object-cover" alt="Pratinjau foto wakil">
                                @elseif ($existingViceChairmanPhoto)
                                    <img src="{{ Storage::disk('public')->url($existingViceChairmanPhoto) }}" class="h-full w-full object-cover" alt="Foto wakil saat ini">
                                @else
                                    <div class="flex h-full items-center justify-center text-sm text-gray-400">Belum ada foto</div>
                                @endif
                            </div>
                            @error('vice_chairman_photo') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Slogan</label>
                        <input type="text" wire:model="slogan" class="ui-input mt-1.5 w-full">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Visi Utama</label>
                        <textarea wire:model="vision" rows="2" class="ui-input mt-1.5 w-full"></textarea>
                        @error('vision') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Program Misi (1 baris = 1 poin)</label>
                        <textarea wire:model="missionText" rows="4" placeholder="Contoh:&#10;Mengaktifkan mentoring antar angkatan&#10;Membuka ruang aspirasi digital" class="ui-input mt-1.5 w-full"></textarea>
                        @error('missionText') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" wire:click="$set('showModal', false)" class="flex-1 rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">Batal</button>
                        <button type="submit" wire:loading.attr="disabled" class="flex-1 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-indigo-500 transition disabled:opacity-60">
                            {{ $editingId ? 'Simpan Perubahan' : 'Tambah Paslon' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
