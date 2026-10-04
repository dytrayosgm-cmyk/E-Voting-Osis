<?php

namespace App\Livewire\Admin;

use App\Models\Candidate;
use App\Models\Setting;
use App\Models\SystemLog;
use App\Models\Vote;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class CandidateManager extends Component
{
    use WithFileUploads;

    public bool $showModal = false;

    public ?int $editingId = null;

    #[Validate('required|integer|min:1|max:99')]
    public $number_order = '';

    #[Validate('required|string|max:100')]
    public $chairman_name = '';

    #[Validate('required|string|max:100')]
    public $vice_chairman_name = '';

    #[Validate('nullable|string|max:150')]
    public $slogan = '';

    #[Validate('required|string|max:2000')]
    public $vision = '';

    /** Textarea 1 baris = 1 poin misi, dipecah jadi array saat disimpan */
    #[Validate('required|string|max:3000')]
    public $missionText = '';

    #[Validate('nullable|image|max:2048')] // 2MB
    public $chairman_photo;

    #[Validate('nullable|image|max:2048')]
    public $vice_chairman_photo;

    public ?string $existingChairmanPhoto = null;

    public ?string $existingViceChairmanPhoto = null;

    public bool $is_active = true;

    #[Computed]
    public function votingOpen(): bool
    {
        return (bool) Setting::current()->is_voting_open;
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
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        if (! $this->ensureVotingClosed()) {
            return;
        }

        $c = Candidate::findOrFail($id);
        $this->editingId = $c->id;
        $this->number_order = $c->number_order;
        $this->chairman_name = $c->chairman_name;
        $this->vice_chairman_name = $c->vice_chairman_name;
        $this->slogan = $c->slogan;
        $this->vision = $c->vision;
        $this->missionText = implode("\n", $c->mission ?? []);
        $this->is_active = $c->is_active;
        $this->chairman_photo = null;
        $this->vice_chairman_photo = null;
        $this->existingChairmanPhoto = $c->chairman_photo;
        $this->existingViceChairmanPhoto = $c->vice_chairman_photo;
        $this->showModal = true;
    }

    public function save(): void
    {
        if (! $this->ensureVotingClosed()) {
            return;
        }

        $rules = [
            'number_order' => 'required|integer|min:1|max:99|unique:candidates,number_order,'.($this->editingId ?? 'NULL').',id',
            'chairman_name' => 'required|string|max:100',
            'vice_chairman_name' => 'required|string|max:100',
            'slogan' => 'nullable|string|max:150',
            'vision' => 'required|string|max:2000',
            'missionText' => 'required|string|max:3000',
            'chairman_photo' => 'nullable|image|max:2048',
            'vice_chairman_photo' => 'nullable|image|max:2048',
        ];

        $validated = $this->validate($rules);

        $mission = array_values(array_filter(array_map('trim', explode("\n", $this->missionText))));

        $data = [
            'number_order' => $validated['number_order'],
            'chairman_name' => $validated['chairman_name'],
            'vice_chairman_name' => $validated['vice_chairman_name'],
            'slogan' => $validated['slogan'],
            'vision' => $validated['vision'],
            'mission' => $mission,
            'is_active' => $this->is_active,
        ];

        if ($this->chairman_photo) {
            $data['chairman_photo'] = $this->chairman_photo->store('candidates', 'public');
        }
        if ($this->vice_chairman_photo) {
            $data['vice_chairman_photo'] = $this->vice_chairman_photo->store('candidates', 'public');
        }

        if ($this->editingId) {
            $candidate = Candidate::findOrFail($this->editingId);
            $oldChairmanPhoto = $candidate->chairman_photo;
            $oldViceChairmanPhoto = $candidate->vice_chairman_photo;
            $candidate->update($data);

            if (isset($data['chairman_photo']) && $oldChairmanPhoto) {
                Storage::disk('public')->delete($oldChairmanPhoto);
            }
            if (isset($data['vice_chairman_photo']) && $oldViceChairmanPhoto) {
                Storage::disk('public')->delete($oldViceChairmanPhoto);
            }

            SystemLog::record(SystemLog::TYPE_ADMIN_ACTION, "Admin memperbarui data Paslon No. {$data['number_order']}.");
        } else {
            $candidate = Candidate::create($data);
            SystemLog::record(SystemLog::TYPE_ADMIN_ACTION, "Admin menambahkan Paslon baru No. {$data['number_order']}.");
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

        // Jangan izinkan hapus kandidat yang sudah punya suara masuk —
        // lindungi integritas data hasil pemilihan.
        if ($c->vote_count > 0) {
            $this->addError('delete', 'Kandidat ini sudah memiliki suara masuk dan tidak dapat dihapus. Nonaktifkan saja.');

            return;
        }

        $c->delete();
        $photosToDelete = array_filter([$c->chairman_photo, $c->vice_chairman_photo]);
        if ($photosToDelete !== []) {
            Storage::disk('public')->delete($photosToDelete);
        }
        SystemLog::record(SystemLog::TYPE_ADMIN_ACTION, "Admin menghapus Paslon No. {$c->number_order}.");
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
        $this->is_active = true;
        $this->resetErrorBag();
    }

    private function ensureVotingClosed(): bool
    {
        if (Setting::current()->is_voting_open && ! $this->overrideLock) {
            $this->addError('session', 'Data paslon hanya dapat dikelola saat sesi voting ditutup atau dengan Buka Kunci Sementara.');
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
