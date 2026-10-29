<?php

namespace App\Livewire\Admin;

use App\Models\Candidate;
use App\Models\Setting;
use App\Models\SystemLog;
use App\Models\Vote;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class CandidateManager extends Component
{
    use WithFileUploads;

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $type = 'paslon'; // paslon | kandidat

    public $number_order = '';

    public $chairman_name = '';

    public $vice_chairman_name = '';

    public $slogan = '';

    public $vision = '';

    /** Textarea 1 baris = 1 poin misi, dipecah jadi array saat disimpan */
    public $missionText = '';

    public $chairman_photo;

    public $vice_chairman_photo;

    public ?string $existingChairmanPhoto = null;

    public ?string $existingViceChairmanPhoto = null;

    public bool $is_active = true;

    public string $election_period = '';

    public function mount(): void
    {
        $setting = Setting::current();
        $this->type = $setting->voting_mode ?: 'paslon';
        $this->election_period = $setting->election_period ?: 'Periode 2025/2026';
    }

    #[Computed]
    public function setting(): Setting
    {
        return Setting::current();
    }

    #[Computed]
    public function votingOpen(): bool
    {
        return (bool) $this->setting->is_voting_open;
    }

    public function setVotingMode(string $mode): void
    {
        if (! in_array($mode, ['paslon', 'kandidat'], true)) {
            return;
        }

        Setting::current()->update(['voting_mode' => $mode]);
        $this->type = $mode;

        $label = $mode === 'kandidat' ? 'Kandidat Individu' : 'Paslon (Ketua & Wakil)';
        SystemLog::record(SystemLog::TYPE_ADMIN_ACTION, "Admin mengubah mode sistem pemilihan menjadi {$label}.");
        unset($this->setting);
    }

    protected function rules(): array
    {
        $rules = [
            'number_order' => 'required|integer|min:1|max:99|unique:candidates,number_order,'.($this->editingId ?? 'NULL').',id',
            'type' => 'required|in:paslon,kandidat',
            'chairman_name' => 'required|string|max:100',
            'slogan' => 'nullable|string|max:150',
            'vision' => 'required|string|max:2000',
            'missionText' => 'required|string|max:3000',
            'election_period' => 'nullable|string|max:100',
            'chairman_photo' => 'nullable|image|max:2048',
            'vice_chairman_photo' => 'nullable|image|max:2048',
        ];

        if ($this->type === 'paslon') {
            $rules['vice_chairman_name'] = 'required|string|max:100';
        } else {
            $rules['vice_chairman_name'] = 'nullable|string|max:100';
        }

        return $rules;
    }

    public function candidates()
    {
        return Candidate::orderBy('number_order')->get();
    }

    public function openCreate(): void
    {
        if (! $this->ensureVotingClosed()) {
            return;
        }

        $this->resetForm();
        $this->type = Setting::current()->voting_mode ?: 'paslon';
        $this->election_period = Setting::current()->election_period ?: 'Periode 2025/2026';
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        if (! $this->ensureVotingClosed()) {
            return;
        }

        $c = Candidate::findOrFail($id);
        $this->editingId = $c->id;
        $this->type = $c->type ?: 'paslon';
        $this->number_order = $c->number_order;
        $this->chairman_name = $c->chairman_name;
        $this->vice_chairman_name = (string) ($c->vice_chairman_name ?? '');
        $this->slogan = (string) ($c->slogan ?? '');
        $this->vision = $c->vision;
        $this->missionText = implode("\n", $c->mission ?? []);
        $this->is_active = $c->is_active;
        $this->chairman_photo = null;
        $this->vice_chairman_photo = null;
        $this->existingChairmanPhoto = $c->chairman_photo;
        $this->existingViceChairmanPhoto = $c->vice_chairman_photo;
        $this->election_period = Setting::current()->election_period ?: 'Periode 2025/2026';
        $this->showModal = true;
    }

    public function save(): void
    {
        if (! $this->ensureVotingClosed()) {
            return;
        }

        $validated = $this->validate();

        $mission = array_values(array_filter(array_map('trim', explode("\n", $this->missionText))));

        $data = [
            'number_order' => $validated['number_order'],
            'type' => $this->type,
            'chairman_name' => $validated['chairman_name'],
            'vice_chairman_name' => $this->type === 'paslon' ? $validated['vice_chairman_name'] : null,
            'slogan' => $validated['slogan'],
            'vision' => $validated['vision'],
            'mission' => $mission,
            'is_active' => $this->is_active,
        ];

        if ($this->chairman_photo) {
            $data['chairman_photo'] = $this->chairman_photo->store('candidates', 'public');
        }
        if ($this->vice_chairman_photo && $this->type === 'paslon') {
            $data['vice_chairman_photo'] = $this->vice_chairman_photo->store('candidates', 'public');
        } elseif ($this->type === 'kandidat') {
            $data['vice_chairman_photo'] = null;
        }

        $labelType = $this->type === 'kandidat' ? 'Kandidat' : 'Paslon';

        if ($this->editingId) {
            $candidate = Candidate::findOrFail($this->editingId);
            $oldChairmanPhoto = $candidate->chairman_photo;
            $oldViceChairmanPhoto = $candidate->vice_chairman_photo;
            $candidate->update($data);

            if (isset($data['chairman_photo']) && $oldChairmanPhoto) {
                Storage::disk('public')->delete($oldChairmanPhoto);
            }
            if ($oldViceChairmanPhoto && ($this->type === 'kandidat' || isset($data['vice_chairman_photo']))) {
                Storage::disk('public')->delete($oldViceChairmanPhoto);
            }

            SystemLog::record(SystemLog::TYPE_ADMIN_ACTION, "Admin memperbarui data {$labelType} No. {$data['number_order']}.");
        } else {
            $candidate = Candidate::create($data);
            SystemLog::record(SystemLog::TYPE_ADMIN_ACTION, "Admin menambahkan {$labelType} baru No. {$data['number_order']}.");
        }

        if (! empty($validated['election_period'])) {
            Setting::current()->update([
                'election_period' => trim($validated['election_period']),
            ]);
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        if (! $this->ensureVotingClosed()) {
            return;
        }

        $c = Candidate::findOrFail($id);
        $labelType = $c->isKandidat() ? 'Kandidat' : 'Paslon';

        // Jangan izinkan hapus kandidat yang sudah punya suara masuk —
        // lindungi integritas data hasil pemilihan.
        if ($c->vote_count > 0) {
            $this->addError('delete', "{$labelType} ini sudah memiliki suara masuk dan tidak dapat dihapus. Nonaktifkan saja.");

            return;
        }

        $c->delete();
        $photosToDelete = array_filter([$c->chairman_photo, $c->vice_chairman_photo]);
        if ($photosToDelete !== []) {
            Storage::disk('public')->delete($photosToDelete);
        }
        SystemLog::record(SystemLog::TYPE_ADMIN_ACTION, "Admin menghapus {$labelType} No. {$c->number_order}.");
    }

    public bool $showVisionModal = false;

    public ?Candidate $viewingCandidate = null;

    public bool $overrideLock = false;

    public function viewVisionMission(int $id): void
    {
        $this->viewingCandidate = Candidate::findOrFail($id);
        $this->showVisionModal = true;
    }

    public function closeVisionMission(): void
    {
        $this->showVisionModal = false;
        $this->viewingCandidate = null;
    }

    public function toggleOverride(): void
    {
        $this->overrideLock = ! $this->overrideLock;
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId', 'number_order', 'chairman_name', 'vice_chairman_name',
            'slogan', 'vision', 'missionText', 'chairman_photo', 'vice_chairman_photo',
            'existingChairmanPhoto', 'existingViceChairmanPhoto',
        ]);
        $this->type = Setting::current()->voting_mode ?: 'paslon';
        $this->election_period = Setting::current()->election_period ?: 'Periode 2025/2026';
        $this->is_active = true;
        $this->resetErrorBag();
    }

    private function ensureVotingClosed(): bool
    {
        if (Setting::current()->is_voting_open && ! $this->overrideLock) {
            $this->addError('session', 'Data paslon/kandidat hanya dapat dikelola saat sesi voting ditutup atau dengan Buka Kunci Sementara.');
            $this->showModal = false;

            return false;
        }

        return true;
    }

    public function render()
    {
        return view('livewire.admin.candidate-manager', [
            'candidates' => $this->candidates(),
            'totalVotesIn' => Vote::count(),
        ]);
    }
}
